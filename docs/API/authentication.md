# Authentication API

**Source:** `backend/routes/api.php`, `backend/app/Http/Controllers/Auth/*`,
`backend/app/Http/Requests/Auth/*`, `backend/app/Http/Resources/UserResource.php`,
`backend/config/auth.php`, `backend/config/sanctum.php`.

Four endpoints. Tokens are Sanctum personal access tokens and do not expire
(`config/sanctum.php` → `'expiration' => null`).

---

## Register

### Request

`POST /api/auth/register`

**Authentication:** not required. **Role:** public.

### Body

```json
{
  "name": "Nour Khalil",
  "email": "nour@example.com",
  "phone": "01012345678",
  "password": "secret-password",
  "password_confirmation": "secret-password",
  "role": "customer"
}
```

### Validation (`RegisterRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `name` | string, max 255 | Yes |
| `email` | string, email, max 255, unique in `users` — lowercased and trimmed before validation | Yes |
| `phone` | string, Egyptian mobile `/^(?:\+?20\|0)?1[0125][0-9]{8}$/`, unique in `users` | No (nullable) |
| `password` | confirmed, min 8 characters (`Password::min(8)`) | Yes |
| `password_confirmation` | must match `password` | Yes (implied by `confirmed`) |
| `role` | must be one of the allowed roles | No |

**Allowed roles depend on configuration.** `RegisterRequest::allowedRoles()`
returns `['customer']` unless `ALLOW_OWNER_REGISTRATION=true`, in which case it
returns `['customer', 'owner']`. Anything else is rejected with
`"You cannot register with that account type."`, and a missing or disallowed
role falls back to `customer` via `resolvedRole()` — the role is never taken
straight from the request body.

### Success — `201 Created`

```json
{
  "message": "Registration successful.",
  "user": {
    "id": 8,
    "name": "Nour Khalil",
    "email": "nour@example.com",
    "phone": "01012345678",
    "role": "customer",
    "status": "active",
    "created_at": "2026-08-28T15:29:07+00:00"
  },
  "token": "8|VZ0nW2Zcb1a2b3c4d5e6f..."
}
```

The password is hashed by the `hashed` cast on `User::casts()` and never
appears in the response.

### Errors

| Code | Cause |
|------|-------|
| `422` | any validation failure — duplicate email or phone, weak password, mismatched confirmation, disallowed role |

---

## Login

### Request

`POST /api/auth/login`

**Authentication:** not required. **Role:** public.

### Body

```json
{ "email": "owner@propspace.com", "password": "password" }
```

### Validation (`LoginRequest`)

| Field | Rules |
|-------|-------|
| `email` | required, string, email (lowercased and trimmed first) |
| `password` | required, string |

### Success — `200 OK`

```json
{
  "message": "Login successful.",
  "user": {
    "id": 1,
    "name": "Hassan Farouk",
    "email": "owner@propspace.com",
    "phone": "01012000001",
    "role": "owner",
    "status": "active",
    "created_at": "2026-08-28T15:29:07+00:00"
  },
  "token": "1|GZ2OnW2ZcbLi5zCOlgKIDj7fQFIznysRon9yyNeX9c4f1c0f"
}
```

Read `user.role` to decide where to send the user: the SPA sends `owner` to
`/owner/dashboard` and `customer` to `/`.

### Errors

| Code | Body | Cause |
|------|------|-------|
| `401` | `{"message": "The provided credentials do not match our records."}` | wrong password **or** unknown email — deliberately identical, so the endpoint cannot be used to enumerate registered addresses |
| `403` | `{"message": "This account has been deactivated."}` | `users.status = 'inactive'` |
| `422` | validation errors | missing or malformed email/password |

---

## Logout

### Request

`POST /api/auth/logout`

**Authentication:** required. **Role:** any.

No body.

### Success — `200 OK`

```json
{ "message": "Logged out successfully." }
```

Only the token that made the request is deleted, so signing out on one device
leaves the user's other sessions working
(`AuthenticationTest::test_logout_only_revokes_the_token_that_made_the_request`).

### Errors

| Code | Cause |
|------|-------|
| `401` | no token, or a token already revoked |

---

## Current user

### Request

`GET /api/auth/me`

**Authentication:** required. **Role:** any.

### Success — `200 OK`

```json
{
  "user": {
    "id": 2,
    "name": "Omar Sabry",
    "email": "customer@propspace.com",
    "phone": "01098000001",
    "role": "customer",
    "status": "active",
    "created_at": "2026-08-28T15:29:07+00:00"
  }
}
```

Identity comes from the token only — a `user_id` supplied by the caller is
ignored entirely. The SPA calls this once at startup to rehydrate the session
behind a stored token.

### Errors

| Code | Cause |
|------|-------|
| `401` | missing, expired or revoked token |

---

## `UserResource` shape

`id`, `name`, `email`, `phone`, `role`, `status` are always present.
`created_at` appears when it is not null. Three further keys appear **only**
when the controller loaded them — currently just the owner customer directory:

| Key | Appears when |
|-----|--------------|
| `contracts_count`, `purchase_requests_count` | `GET /api/owner/customers` |
| `contracts`, `purchase_requests` | `GET /api/owner/customers/{customer}` |

The password and remember-token are on the model's `$hidden` list and can
never be serialised.
