# ExamFlow — Development Setup

This guide covers local (non-Docker) setup and the provided Docker environment.

## Prerequisites

- PHP **8.3+** recommended (local XAMPP here is 8.2, still works for development).
- Composer 2.
- PostgreSQL 16 (a server reachable from your machine).
- Redis 7 (for cache/queue/session when configured).
- Git.

---

## Local Setup (no Docker)

```bash
cd BackEnd

# 1. Install dependencies
composer install

# 2. Create environment and generate app key
cp .env.example .env
php artisan key:generate

# 3. Configure .env
#    DB_CONNECTION=pgsql, DB_* -> your PostgreSQL
#    REDIS_* -> your Redis
#    CACHE_STORE=redis, QUEUE_CONNECTION=redis, SESSION_DRIVER=redis

# 4. Migrate + seed (RoleSeeder/PermissionSeeder are infra-only)
php artisan migrate --seed

# 5. Run
php artisan serve --port=8000
# in another terminal, process the queue:
php artisan queue:work
# scheduled tasks (dev):
php artisan schedule:work
```

Verify:

```bash
php artisan about
curl http://localhost:8000/api/v1/health/live
curl http://localhost:8000/api/v1/health/ready
```

---

## Docker Environment

`docker-compose.yml` provides five services:

| Service    | Purpose                                          |
| ---------- | ------------------------------------------------ |
| `app`      | Laravel (served on `:8000`)                      |
| `queue`    | `php artisan queue:work` worker                  |
| `scheduler`| `php artisan schedule:work`                      |
| `postgres` | PostgreSQL 16 (health-checked)                   |
| `redis`    | Redis 7 (health-checked)                         |

### Starting

```bash
docker compose up -d --build
```

### Stopping

```bash
docker compose down          # stop and remove containers/network
docker compose down -v       # ALSO delete the named volumes (postgres_data, redis_data)
```

### Commands inside a service

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
docker compose exec app php artisan config:cache
docker compose exec app php artisan queue:work --tries=3
docker compose logs -f app
```

### Notes / Limitations

- The `Dockerfile` builds a production-ish PHP 8.3 CLI image; the Compose `app` runs
  `php artisan serve`, suitable for development, not production load.
- **Docker was not installed in the original development environment**, so these files
  are committed but have not been exercised against a live daemon there. If you have
  Docker available, validate with the commands above before relying on them.
- `.env` controls DB credentials used by Compose (`DB_DATABASE`, `DB_USERNAME`,
  `DB_PASSWORD`, `DB_PORT`, `REDIS_PORT`, `APP_PORT`); defaults mirror `.env.example`.

---

## Environment Variables

Documented in [`.env.example`](../../.env.example). Summary of ExamFlow-specific ones:

| Variable | Purpose | Default |
| -------- | ------- | ------- |
| `EXAMFLOW_TENANT_HEADER` | Header carrying the tenant id | `X-Tenant-ID` |
| `EXAMFLOW_REQUEST_ID_HEADER` | Correlation header | `X-Request-ID` |
| `EXAMFLOW_DEFAULT_PER_PAGE` | Default page size | `25` |
| `EXAMFLOW_MAX_PER_PAGE` | Hard cap on `per_page` | `100` |
| `EXAMFLOW_API_RATE_LIMIT` | Global API limit (per minute) | `60` |
| `CORS_ALLOWED_ORIGINS` | Comma-separated allowed origins | — |
| `FILESYSTEM_DISK` | Default storage disk | `local` |
| `AWS_*` | S3-compatible object storage | — |

---

## Common Commands

```bash
php artisan migrate             # run migrations
php artisan migrate:fresh --seed # reset + re-seed (dev only)
php artisan route:list           # see registered routes
php artisan test                 # run tests
composer lint                    # Pint check
composer analyse                 # PHPStan
```
