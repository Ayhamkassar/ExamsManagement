# ExamFlow — API Documentation

All endpoints are versioned under `/api/v1`. This page defines the **conventions** every
endpoint follows. Machine-readable spec: [`openapi.yaml`](openapi.yaml).

## 1. Versioning

- Base path: `/api/v1` (configured in `routes/api.php`; global prefix `api` in
  `bootstrap/app.php`).
- Backward-incompatible changes ship under a new major version (`/api/v2`) while old
  versions remain supported during transition. Never break a released version silently.
- `Accept: application/json` should be sent by clients.

## 2. Response Envelope

Success:

```json
{ "success": true, "message": "Operation completed successfully.", "data": {} }
```

Validation error (`422`):

```json
{ "success": false, "message": "Validation failed.", "errors": { "email": ["..."] } }
```

Other errors:

```json
{ "success": false, "message": "Forbidden." }
```

- `data` is omitted when null; `errors` present only on validation/conflict errors.
- Handled by `App\Support\Api\ApiResponse` and `ApiExceptionRenderer`.

## 3. HTTP Status Codes

| Code | Meaning                                                  |
| ---- | -------------------------------------------------------- |
| 200  | OK                                                        |
| 201  | Created                                                   |
| 204  | No Content (deliberate empty body)                        |
| 400  | Bad request (e.g. missing tenant context)                 |
| 401  | Unauthenticated                                           |
| 403  | Forbidden (authorization failed)                          |
| 404  | Not found (resource or invalid/inactive tenant)           |
| 405  | Method not allowed                                        |
| 409  | Conflict (state conflict; used later)                     |
| 422  | Validation failed                                         |
| 429  | Too many requests (rate limited)                          |
| 500  | Unexpected server error (generic message in prod)         |
| 503  | Degraded / not ready                                      |

Responses never expose stack traces, SQL, or internal paths in production.

## 4. Request Correlation

- Every response includes `X-Request-ID` (echoed from the request if provided, otherwise
  a generated ULID).
- Clients should send the same `X-Request-ID` when retrying to correlate logs.
- The id is logged with exceptions and stored on `audit_logs.correlation_id`.

## 5. Authentication

- **Web (React)**: stateful Sanctum sessions — obtain a CSRF token, then authenticate;
  cookies carry the session. CSRF-protected.
- **Mobile / third-party**: `Authorization: Bearer <personal-access-token>`.
- Full auth endpoints arrive in **Phase 1**; the token infrastructure is already present.

## 6. Tenant Identification

- Tenant-scoped requests send `X-Tenant-ID: <tenant-ulid>`.
- The middleware validates it (must be active) and sets tenant context.
- Routes requiring a tenant context add the `tenant` middleware and return `400` without
  it. See `docs/architecture/multi-tenancy.md`.

## 7. Pagination / Filtering / Sorting (Convention)

List endpoints follow this convention:

| Query      | Default | Max         | Notes                        |
| ---------- | ------- | ----------- | ---------------------------- |
| `page`     | 1       | —           | 1-based                      |
| `per_page` | 25      | 100         | clamped; prevents unlimited |
| `search`   | —       | —           | free-text (server-defined)   |
| `sort`     | —       | allow-list   | only whitelisted fields      |
| `direction`| asc     | asc/desc     |                             |

- `per_page` is clamped to `examflow.pagination.max_per_page` (default 100).
- Sortable fields must be **allow-listed server-side**; unknown fields are ignored or
  rejected — never used directly in SQL ordering by user input.
- Response includes pagination metadata (e.g. `current_page`, `total`, `per_page`,
  `last_page`, `first_page_url`, `next_page_url`, `prev_page_url`) via the standard
  Laravel paginator / `PaginationParameters` helper.

## 8. Rate Limiting

- Global API limiter: `EXAMFLOW_API_RATE_LIMIT` (default 60/min) keyed by user id or IP.
- Exceeded → `429`. Production adds stricter per-endpoint limits.

## 9. Health

| Method | URI                   | Purpose                          |
| ------ | --------------------- | -------------------------------- |
| GET    | `/api/v1/health/live` | Liveness: app responds           |
| GET    | `/api/v1/health/ready`| Readiness: DB/Redis/cache checks |

`ready` returns `200` when healthy, `503` when degraded. Node names never include
connection strings/secrets.

## 10. OpenAPI

`openapi.yaml` defines the envelope, error model, and the health endpoints. It follows
OpenAPI 3.0. Add new endpoints there as they are implemented — never document endpoints
that don't exist.
