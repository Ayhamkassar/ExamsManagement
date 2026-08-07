# ExamFlow — Architecture Overview

This document is the starting point for understanding ExamFlow's backend architecture.
It records the key decisions made in **Phase 0** (back-end foundation). Business modules
arrive in later phases and must follow the patterns defined here.

## 1. Architectural Goals

1. **Hard multi-tenant data isolation** enforced server-side, never by frontend filtering.
2. **Secure by default** — no secrets in code, centralized error handling, no leakage.
3. **Auditable** — every sensitive change is recorded in tamper-resistant audit logs.
4. **Scalable** — stateless app, Redis-backed cache/queue, S3-compatible object storage,
   designed so it can grow to national scale without a rewrite.
5. **Maintainable** — thin controllers, validated requests, API resources, small focused
   classes, explicit boundaries.

## 2. High-Level Layering

```
HTTP request
   └─ Middleware (Request-ID, Security headers, Tenant resolution, CORS, Rate limit)
        └─ Route (/api/v1/...)
             └─ Controller  (thin - delegates)
                  └─ Request (validation/DTO)
                  └─ Service / Action (business logic)
                  └─ Resource / ApiResponse (consistent output)
                        └─ Model + Repository / query builders
                             └─ PostgreSQL / Redis / Storage / Queue
```

- **Controllers** stay thin: validate input, call a service/action, return a Resource.
- **Services** hold orchestration; **Actions** hold single-purpose operations.
- **Repositories/DTOs** are used only where they add real separation — we avoid
  speculative abstraction.
- **Models** stay lean; complex queries are scoped or moved to repositories/services.

## 3. Identifier Strategy — **ULID**

We use **ULID (Universally Unique Lexicographically Sortable Identifier)** as the
identifier for all primary keys (`HasUlids`).

**Why ULID over UUID v4 / auto-increment:**

| Concern        | UUID v4 | Auto-increment | ULID |
| -------------- | ------- | -------------- | ---- |
| Unguessable / not enumerable | Yes     | No             | Yes  |
| Sortable (time-ordered)      | No      | Yes (but leaky)| Yes  |
| Index locality (scans)       | Poor    | Excellent      | Good |
| Multi-node / offline create  | Yes     | No             | Yes  |

- Auto-increment IDs **leak volume** and enable enumeration — unacceptable for a
  public examination platform.
- ULID keeps good index locality while being unguessable and merge-safe across
  horizontally scaled writers.
- **Never mix identifier strategies.** Every new table should use the same ULID convention.
- Storage columns are `snake_case` (`tenant_id`, `is_super_admin`); tables are plural;
  models singular.

## 4. Time & Locale Conventions

- All timestamps are stored/transmitted as **UTC**. The application timezone is UTC.
- Timestamps use Laravel's standard `created_at` / `updated_at` (+ `deleted_at` for
  soft deletes).

## 5. API Surface (Phase 0)

Only health endpoints exist (`routes/api.php`):

| Method | URI                            | Purpose                          |
| ------ | ------------------------------ | -------------------------------- |
| GET    | `/api/v1/health/live`          | Liveness probe (app is alive)    |
| GET    | `/api/v1/health/ready`         | Readiness probe (DB/Redis/cache) |
| GET    | `/api/v1/health`               | Alias for readiness              |

Future resources are added under `/api/v1/...` by following `docs/api/README.md`.

## 6. Request Lifecycle & Correlation

1. `AssignRequestId` reads/creates an `X-Request-ID` (ULID) and sets it on the request.
2. `SecurityHeaders` applies secure response headers.
3. `ResolveTenant` reads the `X-Tenant-ID` header, validates the tenant is active, and
   populates `TenantContext` (a per-request singleton).
4. Rate limiting (`api` limiter, keyed by user id / IP).
5. Controller → service → response.
6. Any uncaught exception goes to `ApiExceptionRenderer` (structured, secret-safe) and is
   logged with the request ID.

## 7. Directory Layout

```
app/
├── Console/            # (scheduler wiring is in routes/console.php)
├── Enums/              # SystemRole, SystemPermission, TenantStatus
├── Exceptions/         # ApiExceptionRenderer
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Middleware/
│   ├── Requests/  Resources/   (later phases)
├── Jobs/Concerns/      # TenantAware (foundation)
├── Models/  Policies/  Providers/
├── Services/  Support/  Actions/  Repositories/  DTOs/  (Actions/Repos/DTOs added when needed)
database/  routes/  tests/  docs/
```

We intentionally **do not** pre-create empty `Actions/`, `Repositories/`, `DTOs/`,
`Events/`, `Listeners/`, `Notifications/` directories — they are added only when real
responsibilities exist, keeping the codebase clean (YAGNI / KISS).

## 8. Key Architectural Decisions (ADR summary)

| # | Decision | Rationale |
| - | -------- | --------- |
| 1 | API-first, `/api/v1` | Serves web, mobile, and third parties uniformly. |
| 2 | ULID primary keys | Unguessable, sortable, horizontal-writer-safe. |
| 3 | Per-request `TenantContext` + global `TenantScope` | Enforces isolation at the query layer. |
| 4 | Sanctum tokens (not raw JWT) | Simpler secure tokens, instant revocation; see security.md. |
| 5 | PostgreSQL + Redis | Relational integrity at scale + high-throughput cache/queue. |
| 6 | Envelope + centralized exceptions | One consistent API contract everywhere. |
| 7 | `audit_logs` append-only | Tamper-resistant audit trail for sensitive data. |
| 8 | S3-compatible object storage | Durable, horizontally-scalable file handling later. |

See `docs/architecture/security.md`, `multi-tenancy.md`, `storage.md`, `queues.md`,
and `scaling.md` for deeper rationale.
