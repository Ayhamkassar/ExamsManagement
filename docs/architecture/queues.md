# ExamFlow — Queue & Background Work Architecture

Heavy work must never block the request/response cycle. This page defines how ExamFlow
runs background work today (the foundation) and how future jobs must behave.

## 1. Queue Driver

- Default connection: **Redis** (`QUEUE_CONNECTION=redis`). Redis provides the shared,
  fast queue broker and is already part of the stack.
- Development/test convenience: `QUEUE_CONNECTION=sync` (tests) / array.
- Docker Compose includes a dedicated **queue worker** service.

## 2. Future Workloads (Phase 1+)

Planned queued operations, to be implemented in their own phases:
- Large file processing (scanned paper ingest, OCR).
- Notifications (email/push).
- Report generation.
- Result calculations.
- Analytics.
- Imports / exports.
- Long-running administrative tasks.

**None of these are implemented in Phase 0** — only the queue foundation exists.

## 3. Job Fundamentals

- Jobs are plain classes under `app/Jobs/`, implementing `ShouldQueue`.
- Retries and backoff via `$tries`, `$backoff`, `$timeout`, and `$maxExceptions`
  (tune per job). Current worker uses `--tries=3`.
- Use queue **batches** (`Bus::batch`) and **chain** (`Bus::chain`) for multi-step work
  when it adds clarity.
- Keep jobs idempotent and able to run more than once (retries). Guard against double
  side effects.

## 4. Tenant Context in Jobs (CRITICAL)

Workers have **no HTTP request**, so there is no `TenantContext` by default. Any job that
touches tenant-scoped models must restore it.

Use the `App\Jobs\Concerns\TenantAware` trait:

```php
use App\Jobs\Concerns\TenantAware;

class GenerateReport implements ShouldQueue
{
    use TenantAware;

    public function handle(): void
    {
        $this->handleTenantAware();   // restores TenantContext from $this->tenantId
        // ... scoped work (global TenantScope + audit now work correctly)
    }
}

// Dispatch from a controller:
GenerateReport::dispatch()->withTenant($currentTenantId);
```

Rules:
- Capture `tenantId` (and any needed ids) on the job at dispatch time — never resolve
  from `auth()`/`request()` inside `handle()`.
- Call the tenant-init at the start of `handle()`.
- Do not run tenant-agnostic system jobs with a stray tenant context (clear it if needed).

## 5. Failure Handling & Dead Letters

- Failed jobs are written to `failed_jobs` (table migration exists) for inspection and
  redelivery (`php artisan queue:retry`, `queue:failed`).
- Set `$tries` sensibly; do not retry forever.
- Notify operators on repeated failures (future: alerting channel).

## 6. Scheduler

- Scheduled tasks are declared in `routes/console.php` using
  `Schedule::command(...)->cron(...)`/`->everyFiveMinutes()`.
- The Docker `scheduler` service runs `php artisan schedule:work`.
- In local dev run `php artisan schedule:work` manually.
- Scheduled jobs that are tenant-aware follow the same `TenantAware` rules (resolve the
  tenant set explicitly within the command).

## 7. Observability

- Queue jobs already carry the standard Laravel lifecycle; prefix logs with the request /
  correlation id where relevant.
- Use `php artisan queue:monitor` or Redis-based dashboards later for queue depth/latency.
- Alert on stuck/failed jobs (future).

## 8. Cache as Temporary State

- Redis cache is available for distributed locks, rate-limit state, and temporary
  processing state.
- Use **tenant-aware cache keys** (`TenantCacheKey`) for anything tenant-related.
- Prefer `Cache::lock()` for distributed mutual exclusion (e.g., one report job per
  tenant+resource).
- Do not implement speculative caching now (see `overview.md` — avoid premature
  optimization).

## 9. Current Configuration Files

- `config/queue.php` — Redis connection, retry-after, failed table.
- `config/cache.php` — Redis store, `examflow` prefix (from `CACHE_PREFIX`).
- `.env.example` — `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`.
