# ExamFlow — Testing

Tests are executable and meaningful — no "assert true" placeholders. The suite proves the
foundation (response contract, error safety, health, tenant isolation, authorization,
audit immutability) actually works.

## Stack

- **Pest** (on PHPUnit) with `tests/Pest.php` wiring.
- Feature/Architecture tests run with `RefreshDatabase` (SQLite in-memory per
  `phpunit.xml`).
- Unit tests boot the app so `config()`/`app()` helpers work, but don't touch the DB.
- `TestCase.php` extends Laravel's base test case.

## Running

```bash
composer test          # recommended: clears config, then runs the suite
php artisan test       # run the suite
php artisan test --filter=TenantIsolation
```

`phpunit.xml` forces a clean, deterministic env: `APP_ENV=testing`, PostgreSQL dev is
bypassed in favour of SQLite `:memory:`, cache/queue/session use array/sync, and an
`APP_KEY` is pinned so tests never depend on a developer's `.env`.

## Suite Layout

```
tests/
├── Unit/                      # pure logic, app booted, no DB
│   └── TenantCacheKeyTest.php # tenant-aware cache key construction
├── Feature/
│   ├── Api/
│   │   ├── ApiResponseStructureTest.php  # envelope + validation + no stack traces in prod
│   │   └── HealthEndpointTest.php        # liveness/readiness, request id, tenant header
│   ├── Auth/AuthorizationTest.php        # RBAC + tenant policy (incl. cross-tenant deny)
│   ├── Audit/AuditLogTest.php            # audit records are immutable
│   └── Tenant/TenantIsolationTest.php    # global scope + auto-fill + header resolution
└── Architecture/            # (ready for architecture-level tests in later phases)
```

## What the Key Tests Validate

**Tenant isolation** (`TenantIsolationTest.php`) — the most important:
- Queries scoped to the active `TenantContext` (rows of other tenants invisible).
- `tenant_id` auto-assigned on create when context is set.
- Documented behavior when context is cleared.
- The `X-Tenant-ID` header flows through middleware into `TenantContext`.

**Error safety** (`ApiResponseStructureTest.php`):
- Consistent success/validation envelopes.
- With `APP_DEBUG=false`, an unexpected exception returns the generic message — the test
  would fail if a stack trace or internal detail leaked.

**Authorization** (`AuthorizationTest.php`):
- Super-admin bypass.
- Org-admin permission checks are tenant-scoped and reject cross-tenant access.
- `TenantPolicy` denies access to another tenant's resource.

**Health** (`HealthEndpointTest.php`): liveness/readiness structure, request-id header,
and valid/invalid tenant headers.

**Audit** (`AuditLogTest.php`): records are append-only (updates/deletes throw).

## Conventions for New Tests

- Place integration tests under `tests/Feature/<Domain>/`; pure helpers under `tests/Unit/`.
- Use factories (see `database/factories`) rather than hand-written rows; they create only
  infrastructure records, never mock business data.
- Tenant-scoped feature tests should set `TenantContext` (via header or directly) exactly
  as production would, to prove isolation end to end.
- Every implementation added in later phases must ship with tests covering: success path,
  validation failure, authorization denial, and cross-tenant denial.

## Related

- `docs/development/setup.md` — running migrations/queue/scheduler.
- `composer check` — lint + analyse + test in one go.
