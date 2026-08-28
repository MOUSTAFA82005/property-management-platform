# Authentication & Authorization

**Derived from:** `backend/bootstrap/app.php`, `backend/routes/api.php`,
`backend/app/Http/Controllers/Auth/*`, `backend/app/Http/Middleware/EnsureUserHasRole.php`,
`backend/app/Policies/*`, `backend/config/sanctum.php`,
`frontend/src/services/api.js`, `frontend/src/stores/auth.js`,
`frontend/src/router/index.js`.

## The actual chain

```mermaid
flowchart TD
    A["HTTP request from the SPA"] --> B{"Authorization: Bearer &lt;token&gt;?"}
    B -- no --> B1["401 Unauthenticated<br/>(protected routes)"]
    B -- yes --> C["auth:sanctum<br/>resolves the token to a User"]
    C -- invalid/revoked --> B1
    C -- valid --> D{"route inside /api/owner/*?"}
    D -- no --> F
    D -- yes --> E["role:owner middleware<br/>EnsureUserHasRole"]
    E -- "role != owner" --> E1["403 This action is unauthorized."]
    E -- "role == owner" --> F["Controller action"]
    F --> G["Gate::authorize(ability, model)"]
    G --> H["Policy method<br/>e.g. ContractPolicy::update()"]
    H --> I["ChecksPropertyOwnership::owns()<br/>record.ownerId() == user.id"]
    I -- false --> I1["403 This action is unauthorized."]
    I -- true --> J["Form Request / inline validation"]
    J -- fails --> J1["422 with errors{}"]
    J -- passes --> K["Query scoped with scopeOwnedBy(user)"]
    K --> L["Resource response"]
```

Written as the layers the request passes through:

```text
Client (Axios, bearer token)
    ↓
auth:sanctum                    → 401 if no/expired/revoked token
    ↓
role:owner middleware           → 403 if the role is wrong (owner routes only)
    ↓
Policy via Gate::authorize()    → 403 if the record is not theirs
    ↓
Ownership verification          → record.ownerId() walked through the property chain
    ↓
Validation (FormRequest)        → 422 on bad input
    ↓
Query scoping (scopeOwnedBy)    → lists can only ever contain own records
    ↓
Controller → API Resource → JSON
```

Two independent mechanisms protect owner data, and both are always on:

1. **Policies** decide whether one named record may be touched.
2. **Query scoping** (`scopeOwnedBy`) decides what a *list* may contain, so an
   index endpoint cannot leak another owner's rows even though no single
   record was named.

Customer-facing endpoints are scoped structurally instead: they start from
`$request->user()->contracts()` / `->payments()` / `->notifications()`, so
there is no code path that could reach another user's rows.

## Login sequence (class level)

```mermaid
sequenceDiagram
    autonumber
    actor U as User
    participant V as LoginView.vue
    participant S as stores/auth.js
    participant Ax as services/api.js (Axios)
    participant LC as Auth\LoginController
    participant LR as LoginRequest
    participant M as User model
    participant DB as Database

    U->>V: submits email + password
    V->>S: login({ email, password })
    S->>Ax: POST /api/auth/login
    Ax->>LC: request (no token yet)
    LC->>LR: validate email, password
    LR-->>LC: validated data (email lowercased/trimmed)
    LC->>DB: User::where('email', ...)->first()
    DB-->>LC: user or null
    alt no user or Hash::check fails
        LC-->>Ax: 401 "The provided credentials do not match our records."
    else user.status != 'active'
        LC-->>Ax: 403 "This account has been deactivated."
    else credentials valid
        LC->>M: createToken('auth-token')
        M->>DB: insert personal_access_tokens
        LC-->>Ax: 200 { message, user, token }
        Ax-->>S: response
        S->>S: setAuth(user, token) → localStorage
        S-->>V: user
        V->>V: router.push(auth.homeRoute())
    end
```

`homeRoute()` sends an owner to `/owner/dashboard` and a customer to `/`.

## Where each guard lives

| Layer | Class / file | Effect |
|-------|--------------|--------|
| Token issue | `Auth\LoginController`, `Auth\RegisterController` | `createToken('auth-token')` → plain-text Sanctum token returned once |
| Token transport | `frontend/src/services/api.js` | request interceptor sets `Authorization: Bearer <token>` from `localStorage` |
| Token check | `auth:sanctum` middleware (`routes/api.php`) | resolves `personal_access_tokens` → `User` |
| Role gate | `App\Http\Middleware\EnsureUserHasRole`, aliased `role` in `bootstrap/app.php` | `role:owner` on the `/api/owner` group |
| Record authorization | `App\Policies\*` via `Gate::authorize()` in each controller | 403 on someone else's record |
| Ownership predicate | `App\Policies\Concerns\ChecksPropertyOwnership::owns()` | owner **and** `ownerId()` match |
| Ownership chain | `scopeOwnedBy()` / `ownerId()` on each model | walks unit → building → property → owner_id |
| Token revocation | `Auth\LogoutController` | deletes **only** the current access token |
| JSON errors | `bootstrap/app.php` → `shouldRenderJsonWhen(api/*)` | every API failure is JSON, never an HTML error page |

## Frontend guards are convenience, not security

`frontend/src/router/index.js` has `requiresAuth`, `requiresOwner` and
`requiresCustomer` meta flags, and `services/api.js` clears the session and
redirects to `/login` on any unexpected `401`. Both exist so the SPA does not
render a page that would only 403 — **the API enforces the same rules
independently**, and `tests/Feature/OwnerIsolationTest.php`,
`CustomerIsolationTest.php` and `frontend/e2e/isolation.spec.js` assert that
hiding a button is never what protects a record.

## Registration and role escalation

`RegisterRequest::allowedRoles()` returns `['customer']` unless
`ALLOW_OWNER_REGISTRATION=true` is set in `.env`, and the persisted role comes
from `resolvedRole()` — never straight from the request body. `UpdateProfileRequest`
does not validate `role` or `status` at all, and `ProfileController::update()`
persists only `name`, `email`, `phone` and (with the current password)
`password`, so a customer cannot promote themselves to owner.
