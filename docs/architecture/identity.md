# ExamFlow — Identity & Authentication Architecture

This document describes the identity and authentication design implemented in **Phase 1**.
It builds on the Phase 0 foundation (Sanctum, RBAC, multi-tenancy). See
`docs/security/authentication.md` for security considerations and `docs/api/authentication.md`
for the API contract.

## 1. Authentication Strategy: Laravel Sanctum

We authenticate with **Laravel Sanctum** (no custom JWT). Rationale (from
`docs/architecture/security.md`):

- **Instantly revocable** — tokens are rows in `personal_access_tokens`; revocation is a
  delete and takes effect immediately (no refresh-token rotation, no JWT denylist).
- **Multiple devices** — each login issues an independent token with device metadata,
  listing/revocation is per token.
- **Three caller classes** supported:
  - React web (SPA) — stateful session + CSRF (Sanctum stateful domains).
  - React Native / mobile — bearer personal access tokens.
  - Future third-party integrations — scoped bearer tokens.

The Phase 0 `hasApiTokens` / Sanctum wiring is retained. Token model, middleware, and
config are reused and extended.

## 2. Identity Model (`users`)

`users` now carries identity + account lifecycle fields (see
`2026_08_07_000004_add_identity_fields_to_users_table.php`):

| Column             | Type      | Notes                                    |
| ------------------ | --------- | ---------------------------------------- |
| `id`               | ULID      | primary key                              |
| `tenant_id`        | ULID,null | belongs-to tenant (nullable for super)   |
| `name`             | string    |                                          |
| `email`            | string    | unique                                   |
| `phone`            | string?   |                                          |
| `password`         | hashed    | Laravel `hashed` cast / bcrypt           |
| `email_verified_at`| datetime? | linked to email verification             |
| `status`           | enum      | active / inactive / suspended / pending_verification |
| `last_login_at`    | datetime? | updated on successful login              |
| `last_login_ip`    | string?   | updated on successful login              |
| `is_super_admin`   | bool      | platform-level flag                      |

**Account status** is modelled with `App\Enums\UserStatus` (`Active`, `Inactive`,
`Suspended`, `PendingVerification`) — never hard-coded strings. `UserStatus::canLogin()`
drives whether an account may authenticate.

**Sensitive-field protection:** `password` and `remember_token` are in `$hidden`;
`UserResource` returns only whitelisted fields. Mass-assignment is limited via `$fillable`.

## 3. Token Architecture & Devices

- `App\Models\PersonalAccessToken` extends Sanctum's model and is bound via
  `Sanctum::usePersonalAccessTokenModel(...)`. It stores per-device metadata
  (`ip_address`, `user_agent`, `device`) for session management.
- `App\Services\Auth\TokenService` centralises issuance/revocation:
  - `issue(user, device?, abilities?)` — creates a token, returns plain text.
  - `revokeCurrent(request, user)` — used by `POST /logout`.
  - `revokeAll(user, exceptTokenId?)` — used by `POST /logout-all` and password resets.
  - `list(user)` — returns active sessions for `GET /auth/sessions`.
- Each login is a new session; users can see and revoke individual sessions
  (`DELETE /auth/sessions/{session}`).

## 4. Auth Services

- `App\Services\Auth\AuthenticationService::attempt()` — validates credentials, checks
  `status->canLogin()`, records events, updates login metadata, issues the token.
- `App\Services\Auth\PasswordService` — forgot / reset / change with token revocation.
- `App\Services\Auth\SecurityEventLogger` — writes to the separate `security_events`
  table (never mixed with business `audit_logs`).
- `App\Services\Auth\AuthContextService` — composes the roles / permissions / tenant
  context returned with auth responses.

## 5. RBAC Integration

- Roles & permissions remain DB-backed (`roles`, `permissions`, `permission_role`,
  `role_user`). `AuthorizationService` resolves a user's roles/permissions **per tenant**.
- `App\Http\Middleware\EnsurePermission` (`permission` alias) and Policies (`UserPolicy`,
  `RolePolicy`, `TenantPolicy`) enforce access server-side; client-supplied claims are
  never trusted.
- Login/`/me` responses include `roles` and `permissions` for the current tenant context.
- **Global vs. organization membership**: system roles (super_admin, organization_admin,
  examiner, reviewer, student) are global definitions. Assigning a role to a user with an
  optional `tenant_id` pivot lays groundwork for organization membership; full
  organization-membership workflows are **Phase 2**.

## 6. Email Verification

- `User` implements `Illuminate\Contracts\Auth\MustVerifyEmail`.
- `App\Notifications\VerifyEmail` sends a **signed, time-limited URL**
  (`URL::temporarySignedRoute` → `POST /auth/email/verify`).
- Endpoint validates the signature + email hash before marking verified.
- `POST /auth/email/resend` re-sends (rate-limited). Future SSO is not blocked — the
  signed-URL flow is independent.

## 7. Future Considerations (not implemented now)

- **2FA** — can be added by requiring a second factor before token issuance in
  `AuthenticationService`.
- **Device trust** — `personal_access_tokens.device` / `ip_address` support trusted-device
  flags later.
- **SSO (OIDC/SAML)** — auth is isolated in `AuthenticationService`; an SSO provider can
  be layered without disturbing token/session mechanics.
- **Throttled/suspicious login heuristics** — `security_events` already captures
  `login_failed`/`login_suspicious`/`login_blocked` for later risk scoring.
