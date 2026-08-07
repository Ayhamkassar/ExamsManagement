# ExamFlow — Storage Architecture

ExamFlow will store large, sensitive files: scanned exam papers, attachments, reports,
supporting documents. The architecture for file storage is established now so Phase 1+
can plug in cleanly.

## 1. Principles

- **Object storage first** — use an S3-compatible service (AWS S3, MinIO, Ceph RGW,
  DigitalOcean Spaces, etc.).
- **Do not store large files in PostgreSQL.** The DB stores references/metadata and file
  keys, never binary blobs.
- **Tenant isolation** by path prefix: `tenants/{tenant_id}/...`
- **Private by default** — files are not served through public URLs. Downloads use
  server-generated, signed, time-limited URLs after validating tenant + permission.
- **Large files bypass the web request cycle** — uploads/downloads stream to/from object
  storage; heavy post-processing is queued (see `queues.md`).

## 2. Storage Disks (`config/filesystems.php`)

- `local` — dev scratch, small internal files.
- `public` — only for truly public assets (never exam data).
- `s3` — production object storage. Configure via `AWS_*` env vars; supports
  `AWS_USE_PATH_STYLE_ENDPOINT` / `AWS_ENDPOINT` for MinIO/Ceph compatibility.

Configure the default production disk via `FILESYSTEM_DISK=s3` (see `.env.example`).

## 3. Path Convention

Use `App\Support\Storage\TenantStoragePath` to build tenant-scoped paths:

```php
use App\Support\Storage\TenantStoragePath;

$path = TenantStoragePath::for($tenantId, 'scans', 'exam-2026-xx.pdf');
// => tenants/{tenant_id}/scans/exam-2026-xx.pdf
```

Conventions:
- First segment: static `tenants` (configurable via `examflow.storage.tenant_prefix`).
- Second segment: the tenant id.
- Remaining segments: a logical **resource area** (`scans`, `attachments`, `reports`,
  `results`, `imports`, `exports`) then the file name / sub-path.
- Use UUIDs/ULIDs for file names when they should be unguessable.

## 4. Private-by-Default Access

- Store files on a **private disk**; never copy to `public/` or `storage/public`.
- Future download endpoints:
  1. Authorize (tenant scope + permission) the requester.
  2. Generate a **signed, time-limited URL** (`Storage::disk('s3')->temporaryUrl(...)`).
  3. Return the URL to the client; the object storage enforces the expiry.
- Never embed private file URLs in HTML/JSON that other tenants could reach.
- S3 URLs must not be guessable/predictable; object keys include the random/id segment.

## 5. Tenant Isolation Guarantees

- Path isolation prevents cross-tenant *listing* even if a bucket were misconfigured.
- For defense-in-depth, object storage policies should scope access by path pattern per
  tenant context in production.
- Bucket access keys are stored in secrets manager, not in code.

## 6. Lifecycle & Retention

- Define per-file-category retention windows (config) — soft-delete references, then
  purge objects through an audited, queued process.
- Enable object-storage **versioning** for high-value exam scans to protect against
  accidental overwrite/deletion.
- For sensitive national exams, consider **server-side encryption** (SSE-S3/KMS) and
  restricted bucket policies.

## 7. Exports / Large Downloads

- Generate large exports (reports, result bundles) as **queued jobs** that write to
  object storage, then notify the requester with a temporary URL (see `queues.md`).
- Stream (chunk) large downloads from object storage rather than buffering whole files
  in PHP memory.

## 8. Not Yet Implemented (Phase 1+)

- Upload controllers / signed multipart upload orchestration.
- File metadata table (object key, size, checksum, mime, tenant_id) with audit hooks.
- Anti-virus / document scanning hooks for uploads.
- Search/OCRI pipelines for scanned papers (queued).
