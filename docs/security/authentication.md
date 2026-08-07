# ExamFlow — Authentication Security

Operational security guidance for the authentication system. Architecture-level rationale
is in `docs/architecture/identity.md`; the API contract is in `docs/api/authentication.md`.

## 1. Secrets & Token Handling

- Tokens are **never logged**, never echoed in error responses, and never stored in
  plain text (Sanctum stores only a SHA-256 hash of the token).
- The `Authorization: Bearer` header is used by mobile / third-party clients; web SPA uses
  stateful sessions + CSRF.
- `SANCTUM_TOKEN_PREFIX`/token names help secret-scanning tools; the raw token is shown
  **once** at issuance and cannot be recovered later.

## 2. Password Security

- Hashing uses Laravel's default `bcrypt` (`hashed` cast). Never store plaintext.
- Minimum password length is enforced (default **12**) via `Password::min(...)`.
- `login`, `register`, `forgot-password`, `reset-password`, `change-password` rules use
  the configured minimum; no weak passwords.
- On **password reset/change**, existing tokens are revoked (all on reset; all-but-current
  on self-service change unless `EXAMFLOW_REVOKE_ALL_ON_CHANGE=true`).
- Every password event is recorded in `security_events`
  (`password_reset_requested`, `password_reset`, `password_changed`).

## 3. Brute-Force & Rate Limiting

- `login` limiter: default **5/min** keyed by `email|ip` (`throttle:login`).
- `password` (forgot/reset): default **3/min**.
- `verification` (resend): default **3/15min**.
- `register`: default **10/hour** per IP.
- A global `api` limiter (default 60/min) applies to all API routes.
- Repeated failures produce `login_failed` / `login_blocked` security events for later
  risk/suspicion analysis; accounts are suspended by operators, not automatically locked
  in this phase.

## 4. No User Enumeration

- `POST /auth/login` returns a generic **401 "Invalid credentials."** for both unknown
  email and wrong password.
- `POST /auth/forgot-password` always returns the same generic success message regardless
  of whether the email exists.
- `POST /auth/reset-password` returns a generic failure on invalid/expired tokens.
- Distinguishing a **suspended/inactive** account returns `403` (the account is known to
  exist but disabled — a deliberate, explicit signal) while still avoiding password hints.

## 5. Email Verification

- Verification links are **signed and time-limited** (`temporarySignedRoute`, 60 min) and
  bound to the email hash — tampering is detected server-side.
- `POST /auth/email/verify` validates the signature & hash before marking verified;
  `/email/resend` is rate-limited.

## 6. Session / Device Management

- Each token is a distinct session with `device`, `ip_address`, `user_agent`.
- Users can list sessions and revoke individual ones; `logout` revokes the current token;
  `logout-all` revokes all.
- Revoking another user's session is rejected (`404`).

## 7. Server-Side Authorization

- Permissions are computed server-side from DB roles; the client never supplies authority
  claims.
- Admin routes are guarded by `permission:roles.manage` / `permission:users.manage`
  middleware plus Policies (`UserPolicy`, `RolePolicy`).
- System roles cannot be deleted; a user lacking the required permission receives `403`.
- `is_super_admin` is a narrow platform flag and is never supplied by clients.

## 8. Audit Separation

- **Security events** (`security_events`, append-only/immutable) record authentication
  lifecycle: login success/failure, logout, password events, email verification, token
  revocation, blocked logins.
- Record-keeping is intentionally **separate** from business `audit_logs` (grades,
  permissions changes, etc.), so security review and business review never mix.

## 9. Future Hardening (not implemented now)

- 2FA, device trust/registration.
- Account lockout with exponential backoff on repeated failures.
- Risk-based `login_suspicious` scoring.
- SSO (OIDC/SAML) layered on `AuthenticationService`.

## 10. Do Not

- Do not log tokens, passwords, or reset tokens.
- Do not reveal account existence through timing, messages, or status codes beyond the
  deliberate suspended/inactive `403`.
- Do not trust client-supplied roles/permissions/tenant ids.
