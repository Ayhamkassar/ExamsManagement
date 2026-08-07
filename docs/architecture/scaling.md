# ExamFlow — Scaling to National Scale

ExamFlow must eventually support thousands of organizations, millions of students, large
exam campaigns, and very high result-announcement traffic. This page records how the
architecture **keeps these options open** without pretending national infrastructure is
built today.

## 1. Guiding Principle

Make no irreversible decision that blocks horizontal scaling. Keep services stateless so
compute can scale out; keep state in PostgreSQL/Redis/object storage.

## 2. Stateless Application Tier

- The Laravel app is **stateless** for requests (no in-memory session affinity needed).
- Sessions/cache/queue live in Redis; the DB is the system of record.
- This allows N identical app replicas behind a load balancer (or `php artisan serve` /
  FPM / Octane) — scale by adding instances.
- `.env` differences across replicas are limited to env config; no code changes needed.

## 3. Database Scaling

- Start with a well-indexed primary PostgreSQL.
- **Indexing strategy**: composite indexes on `(tenant_id, ...)` for tenant-scoped lookups
  (see migrations), unique constraints for integrity.
- Future levers (in order of effort):
  1. **Read replicas** for reporting/heavy reads.
  2. **Connection pooling** (PgBouncer) for high concurrency.
  3. **Partitioning** large high-volume tables (e.g., `audit_logs`, future `results`) by
     date or region.
  4. **Sharding** by tenant group only if a single cluster can no longer serve load — a
     significant operational change to defer until there is evidence.
- Avoid N+1 queries; use eager loading, pagination, and covered indexes (see
  `overview.md` performance section).

## 4. Cache & Queue Scaling

- Redis is the single shared cache/queue broker. Add Redis **cluster mode** to scale
  capacity horizontally.
- Use tenant-aware cache keys (`TenantCacheKey`) and sensible TTLs; invalidate on writes.
- Queue workers scale by adding worker replicas/processes. Keep jobs idempotent.
- Add dedicated worker pools per queue in production (e.g., `reports`, `notifications`,
  `ingestion`) to isolate heavy workloads.

## 5. Storage Scaling

- S3-compatible object storage scales horizontally with zero application change.
- Use lifecycle rules and versioning; offload large exports to queued jobs.
- Never store files in the DB or on local disks in production.

## 6. Multi-Tenancy at Scale

- The shared-schema model scales with proper `tenant_id` composite indexes.
- If a customer demands hard physical isolation, the documented future option is
  per-tenant schema/database via a connection resolver — kept possible because all
  queries go through Eloquent and the `TenantScope`. **Not implemented now.**
- Batch/campaign operations (e.g., nationwide result publishing) should be designed as
  **queued fan-out** that updates the cache layer (hot reads) while the DB remains the
  source of truth.

## 7. High-Traffic Result Announcement (design guidance)

- Publish computed results to a cache/CDN-backed read model; serve reads from cache.
- Queue result calculations offline; do not compute during the announcement request.
- Rate limit aggressively; use multiple app replicas + Redis-backed limiter.
- Use object storage + temporary URLs for downloadable result artifacts.

## 8. Observability for Scaling

- `GET /api/v1/health/live` and `/health/ready` feed orchestrators/load balancers.
- Request-IDs make distributed tracing possible.
- Log queue failures, DB query times, and Redis latency; add APM later.

## 9. Explicitly NOT Done Now

- Kubernetes / service mesh (overkill for current stage).
- Sharding, partitioning, or per-tenant clusters (no evidence yet).
- Complex caching layers or CDN wiring (premature).
- Microservices (monolith-first; extract bounded services only when warranted).

## 10. Capacity Levers Summary (future)

| Threshold hint                        | Lever                                        |
| ------------------------------------- | -------------------------------------------- |
| App CPU/connection saturation         | Add stateless app replicas / Octane workers  |
| Read-heavy reporting                  | PostgreSQL read replicas                     |
| Redis memory/SLA                      | Redis cluster + dedicated queue nodes        |
| Large tables growth                   | Range partitioning on date / tenant group    |
| Massive file volume                   | S3 lifecycle + versioning + CDN for public   |
| A single customer outgrows shared DB  | Per-tenant schema/database (documented)      |
