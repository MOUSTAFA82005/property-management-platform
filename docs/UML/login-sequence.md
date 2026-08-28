# Sequence — Customer login

**Derived from:** `frontend/src/views/auth/LoginView.vue`,
`frontend/src/stores/auth.js`, `frontend/src/services/auth.js`,
`frontend/src/services/api.js`, `frontend/src/router/index.js`,
`frontend/src/main.js`, `backend/app/Http/Controllers/Auth/LoginController.php`,
`backend/app/Http/Requests/Auth/LoginRequest.php`.

The flow is identical for an owner; only the destination route differs
(`homeRoute()` → `/owner/dashboard` for an owner, `/` for a customer).

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant V as LoginView.vue
    participant AS as useAuthStore (Pinia)
    participant SV as services/auth.js
    participant AX as Axios client
    participant API as POST /api/auth/login
    participant DB as MySQL
    participant R as vue-router

    C->>V: enters email + password, submits
    V->>AS: login({ email, password })
    AS->>AS: loading = true
    AS->>SV: login(credentials)
    SV->>AX: api.post('/auth/login', credentials)
    Note over AX: request interceptor adds no token<br/>(none stored yet)
    AX->>API: POST with Accept + Content-Type json
    API->>API: LoginRequest validates, lowercases email
    API->>DB: SELECT users WHERE email = ?
    DB-->>API: user row
    API->>API: Hash::check(password, user.password)
    alt credentials wrong or unknown email
        API-->>AX: 401 { message }
        Note over AX: /auth/login is an auth-entry route,<br/>so the 401 interceptor does NOT clear state
        AX-->>AS: rejected promise
        AS-->>V: throws
        V->>C: renders the API message on the form
    else account not active
        API-->>AX: 403 { message: "This account has been deactivated." }
        AX-->>V: error shown on the form
    else success
        API->>DB: INSERT personal_access_tokens
        DB-->>API: token row
        API-->>AX: 200 { message, user, token }
        AX-->>AS: response.data
        AS->>AS: setAuth(user, token)
        AS->>AS: localStorage['user'], localStorage['token']
        AS-->>V: user
        V->>R: push(redirect query ?? auth.homeRoute())
        R->>R: beforeEach — already initialized, isAuthenticated
        R-->>C: customer home (/) or owner dashboard
    end
```

## After login — every subsequent request

```mermaid
sequenceDiagram
    autonumber
    participant Comp as Any view / store
    participant AX as Axios client
    participant API as Laravel API
    participant AS as useAuthStore
    participant R as vue-router

    Comp->>AX: api.get('/owner/contracts')
    AX->>AX: interceptor reads localStorage token
    AX->>API: GET with Authorization: Bearer <token>
    alt token valid
        API-->>Comp: 200 payload
    else token revoked or expired
        API-->>AX: 401 { message: "Unauthenticated." }
        AX->>AS: clearAuth()
        AX->>R: push({ name: 'login', query: { redirect } })
    end
```

## Session restore on page refresh

```mermaid
sequenceDiagram
    autonumber
    participant M as main.js
    participant AS as useAuthStore
    participant AX as Axios client
    participant API as GET /api/auth/me
    participant R as vue-router

    M->>AS: initializeAuth()
    alt no token in localStorage
        AS->>AS: clearAuth() then initialized = true
    else token present
        AS->>AX: me()
        AX->>API: GET /api/auth/me (Bearer)
        alt 200
            API-->>AS: { user }
            AS->>AS: setUser(user)
        else 401
            AS->>AS: clearAuth()
        else network/server error
            Note over AS: session is kept — a server outage<br/>must not destroy a valid session
        end
        AS->>AS: initialized = true
    end
    M->>R: app.use(router) then mount
    R->>R: beforeEach awaits auth.initialized
```

The token is the only identity: `MeController` reads `$request->user()` and
never accepts a user id from the caller
(`AuthenticationTest::test_me_ignores_any_user_id_supplied_by_the_caller`).
