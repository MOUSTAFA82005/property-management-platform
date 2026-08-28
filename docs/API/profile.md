# Profile API

**Source:** `backend/app/Http/Controllers/Customer/ProfileController.php`,
`backend/app/Http/Requests/Customer/UpdateProfileRequest.php`,
`frontend/src/services/profile.js`.

Shared by both roles despite the `Customer` namespace — the owner portal's
profile page calls the same two endpoints.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/profile` | Yes | Any |
| PUT | `/api/profile` | Yes | Any |

`PATCH` is **not** routed here — only `PUT`.

---

## Get profile

`GET /api/profile`

Always returns the token holder. No id is accepted from the caller, so one
user can never read another's profile.

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

Note the `user` wrapper — this endpoint does **not** use a `data` envelope.

### Errors

`401` unauthenticated.

---

## Update profile

`PUT /api/profile`

### Body

```json
{
  "name": "Omar Sabry",
  "email": "omar@example.com",
  "phone": "01098000001",
  "current_password": "old-password",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

Send only what changes; a password change requires `current_password`.

### Validation (`UpdateProfileRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `name` | `sometimes`, required, string, max 255 | No |
| `email` | `sometimes`, required, email, max 255, unique in `users` ignoring self; lowercased and trimmed | No |
| `phone` | nullable, Egyptian mobile pattern, unique ignoring self | No |
| `current_password` | `required_with:password`, string | Only with `password` |
| `password` | `sometimes`, required, confirmed, min 8 | No |

### Role escalation is impossible here

`role` and `status` are **not validated and not copied** — the controller
persists only `name`, `email`, `phone` and (after verifying the current
password) `password`. Adding `"role": "owner"` to the body has no effect
(`CustomerIsolationTest::test_a_profile_update_cannot_escalate_the_users_role`).

### Success — `200 OK`

```json
{
  "message": "Profile updated.",
  "user": { "id": 2, "name": "Omar Sabry", "email": "omar@example.com", "phone": "01098000001", "role": "customer", "status": "active" }
}
```

### Errors

| Code | Body | Cause |
|------|------|-------|
| `401` | `Unauthenticated.` | no token |
| `422` | `errors: { current_password: ["That is not your current password."] }` | wrong current password |
| `422` | `errors: { email: [...] }` | the address belongs to another account |
| `422` | `errors: { phone: [...] }` | not an Egyptian mobile number, or already registered |
