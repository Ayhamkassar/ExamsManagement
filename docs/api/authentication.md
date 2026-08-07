# ExamFlow — Authentication API

All authentication endpoints live under `/api/v1/auth`. Responses follow the standard
ExamFlow envelope (`success` / `message` / `data`) and error conventions
(`docs/api/README.md`).

## Authentication Flow

1. **Register** (`POST /auth/register`) — creates the user (status `active`), sends an
   email-verification notification. Returns the created user (no token).
2. **Verify email** — the user opens the signed link; the frontend calls
   `POST /auth/email/verify` with the signed URL params.
3. **Login** (`POST /auth/login`) — returns `{ user, token, roles, permissions, tenant }`.
4. **Use the token** — subsequent requests send `Authorization: Bearer <token>`.
5. **Logout** (`POST /auth/logout`) revokes the current token.

## Endpoints

| Method | URI                          | Auth            | Notes                                      |
| ------ | ---------------------------- | --------------- | ------------------------------------------ |
| POST   | `/auth/register`             | public          | creates account, sends verification email  |
| POST   | `/auth/login`                | public          | returns user + token + context             |
| POST   | `/auth/logout`               | `auth:sanctum`  | revokes current token                      |
| POST   | `/auth/logout-all`           | `auth:sanctum`  | revokes all tokens for the user            |
| GET    | `/auth/me`                   | `auth:sanctum`  | profile + roles + permissions + tenant     |
| PATCH  | `/auth/profile`              | `auth:sanctum`  | update `name` / `phone` only               |
| GET    | `/auth/sessions`             | `auth:sanctum`  | list active devices/sessions               |
| DELETE | `/auth/sessions/{session}`   | `auth:sanctum`  | revoke a specific session                  |
| POST   | `/auth/change-password`      | `auth:sanctum`  | change own password                        |
| POST   | `/auth/forgot-password`      | public          | sends reset link (generic response)        |
| POST   | `/auth/reset-password`       | public          | consumes token, sets new password          |
| POST   | `/auth/email/verify`         | public (signed) | verifies email from signed URL             |
| POST   | `/auth/email/resend`         | `auth:sanctum`  | re-sends verification (rate-limited)       |

## RBAC (admin) Endpoints

| Method | URI                          | Permission     | Notes                                      |
| ------ | ---------------------------- | -------------- | ------------------------------------------ |
| GET    | `/permissions`               | `roles.manage` | list all permissions                       |
| GET    | `/roles`                     | `roles.manage` | list roles (with permissions)              |
| POST   | `/roles`                     | `roles.manage` | create role                                |
| GET    | `/roles/{role}`              | `roles.manage` | show role                                  |
| PATCH  | `/roles/{role}`              | `roles.manage` | update role (name/description)             |
| DELETE | `/roles/{role}`              | `roles.manage` | delete role (blocked for system roles)     |
| POST   | `/roles/{role}/permissions`  | `roles.manage` | sync role permissions                      |
| POST   | `/users/{user}/roles`        | `users.manage` | assign role to user                        |
| DELETE | `/users/{user}/roles/{role}` | `users.manage` | revoke role from user                      |

## Token Usage

```http
Authorization: Bearer <personal-access-token>
```
- Tokens are plain-text Sanctum tokens, hashed at rest; the raw value is returned once.
- For web SPA use Sanctum stateful sessions (CSRF + cookie) instead of bearer tokens.
- Token name/abilities/expiry are configurable (see `.env.example`: `EXAMFLOW_TOKEN_*`).

## Response Example — Login

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": { "id": "...", "name": "Jane", "email": "jane@example.com", "status": "active" },
    "token": "<plain-text-token>",
    "roles": ["organization_admin"],
    "permissions": ["users.manage", "roles.manage", "organization.manage", "audit.view"],
    "tenant": { "id": "...", "name": "Example University" }
  }
}
```

## Error Responses

| Scenario                          | Status |
| --------------------------------- | ------ |
| Validation failure                | 422    |
| Invalid credentials                | 401    |
| Suspended / inactive account       | 403    |
| Unauthenticated (missing token)    | 401    |
| Missing permission                 | 403    |
| Invalid/expired reset token        | 422    |
| Invalid/expired verification link  | 400    |
| Revoking another user's session    | 404    |
| Rate limited                       | 429    |

Error bodies never expose passwords, password hashes, tokens, or stack traces.

## Rate Limits

| Limiter     | Applied to                                             | Default |
| ----------- | ------------------------------------------------------ | ------- |
| `login`     | `POST /auth/login`                                     | 5/min   |
| `password`  | `POST /auth/forgot-password`, `/auth/reset-password`   | 3/min   |
| `verification` | `POST /auth/email/resend`                           | 3/15min |
| `register`  | `POST /auth/register`                                  | 10/hour |
| `api`       | all `/api/v1/*` routes                                 | 60/min  |

See `docs/api/openapi.yaml` for the machine-readable contract covering the auth endpoints.
