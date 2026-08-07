# ExamFlow — Security Architecture

Security is designed in from the start. This page covers the Phase 0 security foundation
and the conventions every future module must follow.

## 1. Secrets Management

- **Never commit `.env`.** It is git-ignored (see `.gitignore`).
- No secrets are hard-coded anywhere: DB passwords, `APP_KEY`, Redis credentials,
  object-storage keys, payment credentials, and JWT/encryption keys all come from
  environment / secret managers (e.g. AWS Secrets Manager, Vault, SSM) at runtime.
- `.env.example` documents every variable with **empty** values for secrets.
- `APP_KEY` is generated with `php artisan key:generate` (never reused across envs).

## 2. Authentication Strategy — **Laravel Sanctum**

We use **Laravel Sanctum** stateful tokens, not a custom raw-JWT implementation.

Why:
- **Instant revocation** — tokens are stored server-side; revoking is immediate and
  offline (no waiting for JWT expiry or maintaining a revocation list/denylist).
- **Simpler and more secure than rolling our own JWT.** JWT adds complexity (signing,
  refresh, revocation, clock skew) without a meaningful benefit for first-party clients.
- Supports three classes of caller we need:
  - **React web**: session cookie + CSRF protection (stateful).
  - **React Native / mobile**: personal access tokens over `Authorization: Bearer`.
  - **Third-party / API**: scoped personal access tokens.
- If a future requirement genuinely calls for short-lived stateless JWT (e.g. service
  accounts at national scale), it can be layered behind an abstraction without
  disrupting first-party auth.

Sanctum is already installed (`laravel/sanctum`) and the `personal_access_tokens` table
migration exists. **Full auth endpoints are intentionally deferred to Phase 1.** The
foundation (guard config, token table, `HasApiTokens` on `User`) is in place.

## 3. Authorization (RBAC foundation)

- **Permissions** and **roles** live in the DB (`permissions`, `roles`, `permission_role`,
  `role_user`). Roles are seeded as **system roles** (super_admin, organization_admin,
  examiner, reviewer, student).
- `AuthorizationService` resolves a user's permissions/roles **per tenant**
  (`rolesForTenant`, `userHasPermission($user, $perm, $tenantId)`).
- **The server computes permissions — never trust the client.** Role/permission claims
  from the frontend are ignored; checks always run against the DB.
- Enforcement uses **Laravel Policies / Gates**:
  - `Gate::policy(Tenant::class, TenantPolicy::class)`.
  - `Gate::define('permission', ...)` for ad-hoc permission checks.
- A `User.is_super_admin` bypass is limited to super-admins; all other checks go through
  the service.

Example mappings (architecture examples only; modules come later):
- **Super Admin** — platform-level resources (`platform.manage`).
- **Organization Admin** — manage their tenant's resources (`organization.manage`).
- **Examiner** — access only assigned examination work.
- **Reviewer** — review workflow scoped to assignments.
- **Student** — access only their own data.

## 4. Request Security

### Middleware pipeline (applied to `api` routes)
1. `AssignRequestId` — `X-Request-ID` correlation (see `docs/api/README.md`).
2. `SecurityHeaders` — sets safe response headers.
3. `ResolveTenant` — resolves and validates `X-Tenant-ID`.
4. `throttle:api` — per-user/IP rate limiting.

### `SecurityHeaders` sets
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: no-referrer`
- `X-XSS-Protection: 0` (modern CSP replaces it)
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Strict-Transport-Security` (HSTS) when the request is secure.

### CORS
- Only configured origins are allowed (`CORS_ALLOWED_ORIGINS`).
- `supports_credentials: true` for cookie-based web auth.
- `X-Request-ID` exposed to clients.

### CSRF
- Stateful (cookie) web auth uses Sanctum's CSRF token flow.
- Token-based (mobile/third-party) auth is stateless and CSRF-safe by design.

### Rate limiting
- `RateLimiter::for('api', ...)` — defaults to `EXAMFLOW_API_RATE_LIMIT` (60/min),
  keyed by user id (or IP when unauthenticated).
- Production should add stricter per-endpoint limits and a Redis-backed limiter.

## 5. Input Handling

- All input validated in **Form Requests** (later phases) or inline `validate` now.
- Laravel automatically protects against SQL injection via the query builder / Eloquent
  prepared statements. Never concatenate user input into raw SQL.
- **Mass-assignment protection** is on by default; models expose only `$fillable` fields.
- Output is JSON-escaped, mitigating XSS toward web clients.

## 6. Centralized Exception Handling (`ApiExceptionRenderer`)

- Returns a **consistent envelope** and appropriate HTTP status code for:
  validation (`422`), unauthenticated (`401`), forbidden (`403`), not found (`404`),
  method not allowed (`405`), too many requests (`429`), HTTP errors, and unexpected
  (`500`).
- In production (`APP_DEBUG=false`), unexpected errors return a generic message —
  **never** stack traces, SQL details, filesystem paths, or env values.
- Full technical detail is written **server-side** to logs with the request ID.

## 7. Auditability

- `AuditLog` records are **immutable**: updates and deletes are prevented at the model
  level (see `app/Models/AuditLog.php`).
- Each record captures actor, tenant, action, before/after values, IP, user agent, and
  correlation ID.
- Future tamper-resistance hardening (hash-chaining of rows) is planned; logs are
  rotated via the dedicated `audit` channel.

## 8. File Security

- Private files are **not** on the public disk and are **not** served via public URLs.
- Storage is namespaced per tenant: `tenants/{tenant_id}/...`
  (`TenantStoragePath`, see `storage.md`).
- Future downloads must use signed, time-limited temporary URLs validated server-side
  against tenant + permission.

## 9. Data Privacy

- **Data minimization**: only collect/store what is required; never store data "just in
  case".
- **Access control**: tenant scope + RBAC + ABAC (later) gate every dataset.
- **Retention / deletion**: define retention windows per data class; support soft delete
  now, hard purge via a safe, audited process later.
- **Backup / export**: keep encrypted backups; expose audited export endpoints later.
- **No automatic legal compliance claim.** The architecture is designed so legal /
  compliance requirements (GDPR, data-protection, national exam rules) can be layered on
  without rework.

## 10. Anti-Patterns

- ❌ Logging passwords, tokens, or PII into technical logs.
- ❌ Trusting client-supplied roles/permissions.
- ❌ Bypassing the exception renderer or exposing `debug` truth in prod.
- ❌ Storing secrets in code/config committed to git.
- ❌ Serving private files publicly.

