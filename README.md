# ExamFlow Backend — Phase 0 Foundation

> **Status: Phase 0 (Backend Foundation & Architecture)**
> Business modules (students, exams, results, etc.) are **not** implemented yet and
> must not be until Phase 1.

ExamFlow is a production-grade, API-first **SaaS examination platform**. It is architected
to ultimately serve private schools, universities, and national / government-level
examination organizations — with strict multi-tenant data isolation, auditability,
security, and horizontal scalability.

This repository contains the **backend only**, independent of the React frontend (kept in
the sibling `FrontEnd/` directory). All client types (React web, React Native, third-party
integrations) communicate through versioned REST APIs under `/api/v1`.

---

## Tech Stack

| Concern            | Choice                                             |
| ------------------ | ---------------------------------------------------|
| Framework          | Laravel 12 (PHP 8.3+ recommended; 8.2 dev OK)       |
| Database           | PostgreSQL 16 (via pgsql driver)                    |
| Cache / Queue / Session | Redis 7                                        |
| Auth tokens        | Laravel Sanctum (stateful API tokens)               |
| API                | REST, versioned under `/api/v1`                     |
| Queue              | Laravel Queue (Redis driver) + Scheduler            |
| Object storage     | S3-compatible (future private files)                |
| Tests              | Pest (PHPUnit under the hood)                       |
| Static analysis    | PHPStan (Larastan) + Laravel Pint                   |
| Docker             | Docker Compose (app, queue, scheduler, postgres, redis) |

---

## What Phase 0 Delivered

- **API versioning** — all endpoints under `/api/v1`.
- **Consistent response envelope** — `success` / `message` / `data` / `errors`
  (`app/Support/Api/ApiResponse.php`).
- **Centralized exception handling** — `app/Exceptions/ApiExceptionRenderer.php`
  returns structured, secret-safe JSON and never leaks stack traces in production.
- **Request correlation** — `X-Request-ID` assigned on every request and echoed back,
  threaded through exceptions and audit logs.
- **Multi-tenancy foundation** — `TenantContext`, tenant resolution via `X-Tenant-ID`
  header, a global `TenantScope`, `BelongsToTenant` trait, and tenant-isolation tests.
- **RBAC foundation** — roles, permissions, `AuthorizationService`, Laravel Policies/Gates.
- **Audit foundation** — append-only, tamper-resistant `audit_logs` table + `AuditLogger`.
- **Infra tables** — `tenants`, enhanced `users`, `roles`, `permissions`,
  `audit_logs`, and a non-business `tenant_isolation_probes` table used to prove
  isolation mechanics.
- **Health endpoints** — `/api/v1/health/live` (liveness) and
  `/api/v1/health/ready` (readiness with DB / Redis / cache checks).
- **Security foundation** — security headers, CORS, rate limiting, validated requests,
  secure defaults, `.env.example` without secrets.
- **Queue / cache / storage** — Redis configured, tenant-aware cache keys, tenant-scoped
  object-storage path conventions, `TenantAware` job trait foundation.
- **Docker** — `docker-compose.yml` + `Dockerfile` with Postgres, Redis, queue worker,
  and scheduler services.
- **Testing & quality** — Pest feature/unit/architecture suites, PHPStan, Pint.
- **Documentation** — see [`docs/`](docs/).

---

## Quick Start (Local, without Docker)

Requirements: PHP 8.2+, Composer, PostgreSQL, Redis (locally reachable).

```bash
cp .env.example .env
# edit .env: DB_* , REDIS_*; APP_KEY is generated below
php artisan key:generate
composer install
php artisan migrate --seed
php artisan serve --port=8000
```

Health checks:

```bash
curl http://localhost:8000/api/v1/health/live
curl http://localhost:8000/api/v1/health/ready
```

---

## Quick Start (Docker)

See [`docs/development/setup.md`](docs/development/setup.md). In short:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

> Note: Docker was not installed in the original development environment. The
> configuration is provided and kept standard, but has not been exercised with a live
> container there.

---

## Code Quality Commands

```bash
composer test        # runs the full Pest suite
composer lint        # Pint check (style); composer lint:fix auto-fixes
composer analyse     # PHPStan static analysis
composer check       # lint + analyse + test
```

---

## Documentation Map

| Topic                              | Location                                    |
| ---------------------------------- | ------------------------------------------- |
| Architecture overview & decisions  | `docs/architecture/overview.md`             |
| Multi-tenancy                      | `docs/architecture/multi-tenancy.md`        |
| Security                           | `docs/architecture/security.md`             |
| Storage                            | `docs/architecture/storage.md`              |
| Queues & background work           | `docs/architecture/queues.md`               |
| Scaling to national scale          | `docs/architecture/scaling.md`              |
| API conventions (incl. OpenAPI)    | `docs/api/README.md`, `docs/api/openapi.yaml` |
| Local & Docker setup               | `docs/development/setup.md`                 |
| Testing                            | `docs/development/testing.md`               |
| Deployment                         | `docs/deployment/README.md`                |

---

## Security Note

All variables are documented in [`.env.example`](.env.example). **Never commit `.env`.**
Secrets (DB passwords, APP_KEY, storage credentials, Redis credentials) must come from
environment / secret managers only. See `docs/architecture/security.md`.

---

## What is Explicitly **Out of Scope** Until Phase 1+

Do **not** implement (no code, no tables, no fake data): students, teachers, exams,
questions, exam papers, corrections, results, appeals, billing, notifications, or any
mock business data. Violating this breaks the Phase 0 boundary. See the architecture
docs for how future tenant-scoped models should be added.

