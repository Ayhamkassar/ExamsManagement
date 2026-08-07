# ExamFlow — Multi-Tenancy Architecture

ExamFlow is a SaaS platform that will serve many independent organizations (schools,
universities, government bodies). **Strict data isolation is a non-negotiable invariant.**
Isolation is enforced **server-side** at the query layer — never by frontend filtering.

## 1. Tenancy Model

- **Shared database, shared schema, row-level scoping.** Every tenant-owned row carries a
  `tenant_id` foreign key to `tenants`.
- This model balances cost/operational simplicity with strong isolation and is suitable
  for thousands of organizations on a relatively small number of DB clusters.
- **Not** per-tenant databases or schemas: those make migrations, connections, and
  horizontal scaling far more complex without commensurate benefit at this stage.
  (Documented as a future option in `scaling.md` if a customer demands hard isolation.)

## 2. Core Concepts

### `tenants` table
- ULID `id`, `name`, `slug` (unique), `status` (`active|suspended|pending`), `settings`,
  soft deletes.
- Suspended/inactive tenants are rejected at resolution time.

### `TenantContext` (per-request singleton)
- Holds the currently resolved `Tenant` for the duration of the request.
- Bound as a singleton in `AppServiceProvider`; populated by `ResolveTenant` middleware.
- API: `set(Tenant)`, `get()`, `id()`, `has()`, `clear()`.
- Used by the global scope, cache-key helper, storage-path helper, and audit logger, so
  that all of them are automatically tenant-aware.

### Tenant resolution (`ResolveTenant` middleware)
- Reads `X-Tenant-ID` request header (configurable via `EXAMFLOW_TENANT_HEADER`).
- Looks up the tenant, requiring `status = active`.
- On success: `TenantContext->set(tenant)` and `request->attributes->set('tenant', ...)`.
- On invalid/inactive tenant: returns `404` with a generic message.
- If the header is absent, no tenant context is set (public/health routes still work).

### `EnsureTenantContext` middleware (`tenant` alias)
- Guards routes that must be tenant-scoped: returns `400` if no tenant context exists.
- Use on tenant-scoped resource routes.

## 3. Enforcing Isolation on Models

Any future model owning tenant data must:

1. **Use the `App\Models\Concerns\BelongsToTenant` trait** which:
   - Registers the global `TenantScope`.
   - Auto-fills `tenant_id` on `creating` from the current `TenantContext` when not set.
2. Have a `tenant_id` column with a foreign key to `tenants`.
3. Define `tenant(): BelongsTo`.

```php
use App\Models\Concerns\BelongsToTenant;

class Student extends Model {
    use BelongsToTenant, HasUlids;
}
```

### `TenantScope` (global scope)
```php
$builder->where($model->getTable().'.tenant_id', $currentTenantId);
```
- Applies whenever `TenantContext` is set — **every** `Model::query()` is therefore
  restricted to the current tenant.
- When no tenant context is set, the scope is a no-op. **This is deliberate**: platform
  (super-admin) / system operations run outside a tenant and must explicitly scope their
  queries. Future super-admin endpoints must not rely on the absence of context to leak
  data — they must define their own explicit, filtered queries.
- To intentionally query across tenants (system jobs, exports), bypass explicitly:
  `Model::withoutGlobalScope(TenantScope::class)`, and only in trusted contexts.

## 4. Tenant Isolation Test Proving

A non-business table `tenant_isolation_probes` exists solely to prove the mechanics.
`tests/Feature/Tenant/TenantIsolationTest.php` proves that:

- Queries are scoped to the active `TenantContext`.
- `tenant_id` is auto-assigned on create when context is set.
- Clearing the context disables the scope (documented behavior).
- The `X-Tenant-ID` request header flows through middleware into `TenantContext`.

## 5. Authorization Boundaries

Tenant isolation (data) is **complemented** by authorization (who may act on data):

- `User` belongs to one `tenant` (nullable for super-admins).
- Roles/permissions are attached per-tenant via the `role_user.tenant_id` pivot.
- `AuthorizationService->userHasPermission($user, $permission, $tenantId)` scopes role
  checks to a tenant. A user must be in the target tenant to act on its resources.
- Policies (e.g. `TenantPolicy`) combine membership check + permission check:
  - Org admin can manage **their own** tenant resources.
  - Examiner can access only assigned work (enforced in future phases).
  - Student can access only their own data.
  - Super Admin can manage platform-level resources.

## 6. Cache Tenant Isolation

Cache keys must be tenant-aware to avoid cross-tenant leaks or collisions.

Convention: `tenant:{tenant_id}:resource:{id}`

```php
TenantCacheKey::for($tenantId, 'users', $userId);
// => tenant:<tenantId>:users:<userId>
```
Always build cache keys from `TenantContext->id()` (or the entity's own `tenant_id`),
never a bare resource id. See `storage.md` for file storage.

## 7. Background Jobs Tenant Context

Queued jobs must restore tenant context, because a worker has no HTTP request.

- Use the `App\Jobs\Concerns\TenantAware` trait:
  - `withTenant(Tenant|string $id)` captures the tenant on the job instance.
  - `handleTenantAware()` / `initializeTenantContext()` re-hydrates `TenantContext` before
    work runs, so global scopes and audit logging behave correctly.
- Future job authoring must call the tenant-context initialization at the top of `handle()`.

## 8. File Storage Tenant Isolation

Object-storage paths are namespaced per tenant:

```
tenants/{tenant_id}/...
```
`TenantStoragePath::for($tenantId, 'scans', 'exam-123.pdf')` builds these paths. Private
files must never be served through public URLs; future downloads use signed/time-limited
temporary URLs (see `security.md` and `storage.md`).

## 9. Common Pitfalls

- ❌ Relying on the client to send filters — always scope server-side.
- ❌ Queries that bypass the global scope without an explicit, controlled override.
- ❌ Storing cross-tenant references without a `tenant_id`.
- ❌ Using a non-tenant-aware cache key.
- ❌ Letting a job run with stale/missing tenant context.
- ❌ Serving private files via public disk.

## 10. Future: Super-Admin & Cross-Tenant Operations

Super-admin tooling may need to read across tenants. These paths must:
- Be explicitly authorized by `is_super_admin` / `platform.manage`.
- Use explicit scoping (e.g. `.where('tenant_id', $id)`) rather than relying on the
  global scope.
- Write audit records with a `tenant_id` when the action targets a specific tenant.

