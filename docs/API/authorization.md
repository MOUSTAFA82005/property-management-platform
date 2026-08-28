# API Security & Authorization

**Source:** `backend/bootstrap/app.php`, `backend/routes/api.php`,
`backend/app/Http/Middleware/EnsureUserHasRole.php`,
`backend/app/Policies/*`, `backend/app/Policies/Concerns/ChecksPropertyOwnership.php`,
the `scopeOwnedBy()` methods on every model,
`backend/tests/Feature/OwnerIsolationTest.php`,
`backend/tests/Feature/CustomerIsolationTest.php`,
`frontend/e2e/isolation.spec.js`.

## The guarantees

1. **Customers cannot access owner endpoints.** `/api/owner/*` is wrapped in
   `role:owner`; a customer's token gets `403` before any controller runs.
2. **Owners cannot access another owner's resources.** Being an owner is never
   sufficient — every policy requires `record.ownerId() === user.id`, and
   every owner list query is scoped with `ownedBy($user)`.
3. **Customers cannot see another customer's records.** Customer endpoints
   read from the token holder's own relations, and the policies re-check
   `user_id`/`customer_id` on every detail route.
4. **Frontend button visibility is not the security mechanism.** Route guards
   and conditional buttons exist so the SPA does not render a page that would
   only 403. The API enforces every rule independently, and the isolation
   tests call the endpoints directly to prove it.

## The chain

```text
Request with Authorization: Bearer <token>
        ↓
auth:sanctum                 resolves personal_access_tokens → User   (401 if it fails)
        ↓
role:owner middleware        EnsureUserHasRole, owner routes only     (403 on wrong role)
        ↓
Policy via Gate::authorize   e.g. ContractPolicy::update              (403 on someone else's record)
        ↓
Ownership verification       ChecksPropertyOwnership::owns()
                             → record.ownerId() walked through
                               unit → building → property → owner_id
        ↓
Validation                   FormRequest or inline rules              (422)
        ↓
Query scoping                Model::ownedBy($user) on every list
        ↓
Controller → API Resource → JSON
```

## Layer by layer

### 1. Authentication — `auth:sanctum`

Applied to one route group in `routes/api.php`. Everything outside it is
public: registration, login, and the read-only property/unit catalog. A
missing, expired or revoked token gives:

```json
{ "message": "Unauthenticated." }
```

with `401`. `bootstrap/app.php` forces JSON rendering for `api/*`, so this is
never an HTML redirect to a login page.

### 2. Role gate — `role:owner`

`App\Http\Middleware\EnsureUserHasRole`, aliased `role` in `bootstrap/app.php`:

```php
Route::prefix('owner')->middleware('role:owner')->group(...);
```

It checks `in_array($user->role, $roles, true)` and nothing else — it says
nothing about *which* records may be touched. Failure:

```json
{ "message": "This action is unauthorized." }
```

with `403`.

### 3. Policies

Auto-discovered by Laravel's `App\Policies\{Model}Policy` convention (no
registration in `AppServiceProvider`), and invoked explicitly with
`Gate::authorize()` in each controller action.

| Policy | `viewAny` | `view` | `create` | `update` / `delete` | extra |
|--------|-----------|--------|----------|---------------------|-------|
| `PropertyPolicy` | owner | owns | owner | owns | `publish`, `unpublish` (owns) |
| `BuildingPolicy` | owner | owns | owner | owns | — |
| `UnitPolicy` | owner | owns | owner | owns | — |
| `ContractPolicy` | owner or customer | customer: own `user_id`; owner: owns | owner | owns | — |
| `PaymentPolicy` | owner or customer | customer: own via contract; owner: owns | owner | owns | — |
| `PurchaseRequestPolicy` | owner or customer | customer: own `customer_id`; owner: owns | **customer** | `delete`: the requesting customer | `approve`, `reject` (owns) |

`PurchaseRequestPolicy::create` is the one place where **only a customer** is
allowed — an owner cannot raise a purchase request.

### 4. Ownership verification

`ChecksPropertyOwnership::owns()`:

```php
if (! $this->isOwner($user)) return false;
$ownerId = $record->ownerId();
return $ownerId !== null && $ownerId === $user->id;
```

`ownerId()` is defined on each model and walks the chain:

| Model | Path to the owner |
|-------|-------------------|
| `Property` | `owner_id` |
| `Building` | `property.owner_id` |
| `Unit` | `building.property.owner_id` |
| `Contract` | `unit.building.property.owner_id` |
| `Payment` | `contract.unit.building.property.owner_id` |
| `PurchaseRequest` | `unit.building.property.owner_id` |

### 5. Query scoping

Policies protect one named record. Lists are protected separately, because no
single record is named: every owner index endpoint applies
`Model::ownedBy($request->user())`, which reuses the same chain as a
`whereHas`. Two independent mechanisms, both always on.

Customer endpoints are scoped structurally instead — they start from
`$request->user()->contracts()`, `->payments()`, `->notifications()` — so there
is no code path that could reach another user's rows.

### 6. Cross-resource writes

Wherever a request body names a *different* resource, that resource is
authorized too, so a foreign key cannot be used as a way in:

| Endpoint | Extra check |
|----------|-------------|
| `POST /api/owner/buildings` | `Gate::authorize('update', $property)` |
| `PUT /api/owner/buildings/{id}` with `property_id` | target property authorized |
| `POST /api/owner/units` | `Gate::authorize('update', $building)` |
| `PUT /api/owner/units/{id}` with `building_id` | target building authorized |
| `POST` / `PUT` `/api/owner/contracts` | the unit's `property.owner_id` compared to the token holder |
| `POST /api/owner/payments` | `Gate::authorize('update', $contract)` |
| `PUT /api/owner/payments/{id}` with `contract_id` | target contract authorized |

### 7. Values never taken from the request body

| Value | Where it comes from |
|-------|---------------------|
| `properties.owner_id` | `$request->user()->id` |
| `purchase_requests.customer_id` | `$request->user()->id` |
| `users.role` at registration | `RegisterRequest::resolvedRole()`, whitelisted |
| `users.role` / `status` on profile update | never written — not validated, not copied |
| the notification's user | `$request->user()->notifications()` |

## 404 instead of 403, on purpose

Three places answer `404` where `403` would leak information:

| Case | Why |
|------|-----|
| an unpublished or inactive property, or a unit inside one | an unpublished property must be indistinguishable from one that does not exist |
| `GET /api/owner/customers/{customer}` for an unrelated customer | otherwise list scoping could be walked around by guessing ids |
| `POST /api/notifications/{id}/read` for someone else's notification | `findOrFail` on the caller's own relation |

## Test coverage

| Suite | What it proves |
|-------|----------------|
| `tests/Feature/AuthenticationTest.php` (27 tests) | token issue/revoke, role gate, deactivated accounts, `me` ignores caller-supplied ids |
| `tests/Feature/OwnerIsolationTest.php` (13) | an owner cannot read or write another owner's records through any endpoint |
| `tests/Feature/CustomerIsolationTest.php` (9) | a customer cannot read another customer's records, and cannot escalate their role |
| `tests/Feature/CustomerPaymentAccessTest.php` (3) | payment visibility follows contract ownership |
| `tests/Feature/OwnerCustomerListTest.php` (4) | the customer directory is built from real relationships |
| `frontend/e2e/isolation.spec.js` | the same checks through the browser **and** by calling the API directly |
