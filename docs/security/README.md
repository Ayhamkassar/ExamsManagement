# ExamFlow — Security & Operations Reference

This folder gathers security guidance for operating ExamFlow. Deep architecture detail is
in [`docs/architecture/security.md`](../architecture/security.md); here we focus on
**operational** conventions.

## Response / Incident Handling

- All API errors are surfaced through `ApiExceptionRenderer`; production responses never
  contain stack traces, SQL, filesystem paths, or secrets.
- **Every request is traceable** via `X-Request-ID` (echoed and logged). Include it in
  every bug report / incident ticket.
- Unexpected exceptions are logged server-side with: request id, exception class/message,
  file, and line. Review these logs rather than the client-facing message.

## Secrets

- Secrets live only in environment / secret managers. `.env` is git-ignored.
- Rotate `APP_KEY` and storage/DB credentials through a documented, audited process.
- Never paste tokens, keys, or `.env` contents into issues, logs, or this repo.

## Access & Authorization Ops

- Roles/permissions are DB-backed and assessed server-side; the client never supplies
  authority claims.
- Super-admin is a narrow, explicit flag; all real-world admin actions should also be
  audited.
- Tenant isolation is enforced at the query layer — do not "fix" it with client filters.

## Audit & Retention

- Business audit events are recorded in the append-only `audit_logs` table (updates and
  deletes are blocked). Operator/SRE access to that table should also be audited.
- Define and enforce data-retention windows per category and purge through a queued,
  audited process.

## File Handling

- Private files are stored on a private/object disk and handed out only via signed,
  time-limited URLs after authorization. Never move private files to `public/`.

## Responsible Reporting

- Report suspected vulnerabilities privately to the platform security owner; do not
  disclose exploit details publicly.
- Patch critical dependencies promptly; keep `composer audit` in CI.

## Compliance

- This project is engineered to be *compatible with* privacy/legal requirements but does
  **not** claim automatic compliance. Engage legal counsel to map requirements (data
  protection, national exam regulations) before claiming compliance.
