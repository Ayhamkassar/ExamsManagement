# ExamFlow — Deployment

This page describes a minimal, production-oriented deployment posture for the backend.
It is guidance, not an over-engineered Kubernetes setup at this stage.

## Recommended Topology (start)

- One or more **stateless app replicas** behind a load balancer / reverse proxy (Nginx /
  Caddy) terminating TLS.
- **PostgreSQL** (managed or self-hosted) as the system of record. Start single primary;
  add read replicas when reporting load demands.
- **Redis** (managed or self-hosted) for cache/queue/session; enable persistence and,
  later, cluster mode.
- **S3-compatible object storage** for files (private bucket).
- **Queue workers** (one or more processes) draining the Redis queue.
- **Scheduler** running `php artisan schedule:work`/cron (single instance to avoid
  duplicate runs; or use a leader-elected scheduler).

## Environment (per stage)

- Copy `.env.example`; set real values via the deployment platform's secret store.
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`.
- `DB_CONNECTION=pgsql`, strong credentials, `DB_SSLMODE=require` over TLS.
- `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`.
- `FILESYSTEM_DISK=s3` with `AWS_*` (or MinIO/Ceph) credentials.
- `CORS_ALLOWED_ORIGINS` restricted to the real web origin(s).
- Tune `EXAMFLOW_API_RATE_LIMIT` per plan tier.

## Build & Run

Build a PHP-FPM (or CLI/Octane) image from `Dockerfile`, run migrations as a one-off step
before scaling out replicas:

```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan event:cache
```

Run app via FPM + Nginx or Laravel Octane behind the proxy. Keep the scheduler on a single
instance.

## Health & Load Balancing

- Liveness: `GET /api/v1/health/live` (app responds).
- Readiness: `GET /api/v1/health/ready` (DB/Redis/cache OK; `503` when degraded).
- Point the load balancer / orchestrator at these probes; do not route traffic to an
  unready instance.

## Observability

- Ship application logs (with request ids) to a central log store.
- Monitor: request rate/latency/errors, queue depth and failures, Redis memory, DB
  connections and slow queries, DRD availability.
- Add error tracking (e.g., Sentry) and APM when operating at scale.

## Backups & DR

- PostgreSQL: consistent nightly backups (pg_dump / managed snapshots) + point-in-time
  recovery; test restores regularly.
- Redis: persistence (RDB/AOF) to survive restarts; treat queue as best-effort and keep
  burst/notification-grade data recoverable.
- Object storage: enable versioning + lifecycle rules; cross-region replication for
  critical exam data if required by policy.

## Secrets & Access Control

- IAM/service-account least-privilege for app → DB, Redis, S3.
- Limit shell access to the environment; require MFA for admin consoles.
- Rotate credentials; store secrets in a secrets manager, never in the image.

## Not Implemented Now (future)

- Kubernetes / service mesh / auto-scaling policies (documented in
  [`docs/architecture/scaling.md`](../architecture/scaling.md)).
- Multi-region active-active, sharding, or per-tenant clusters.
