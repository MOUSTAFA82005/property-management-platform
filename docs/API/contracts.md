# Contracts API

**Source:** `backend/app/Http/Controllers/Owner/ContractController.php`,
`backend/app/Http/Controllers/Customer/ContractController.php`,
`backend/app/Policies/ContractPolicy.php`,
`backend/app/Http/Resources/ContractResource.php`,
`backend/app/Notifications/ContractNotification.php`,
`frontend/src/services/contracts.js`, `frontend/src/stores/contracts.js`.

A contract links a **unit** to a **customer** (`contracts.user_id` → `users.id`).
There is no separate tenants table; the tenant is the customer on the contract.

Validation for create and update is **inline in the controller** — there is no
`StoreContractRequest`/`UpdateContractRequest` class.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/owner/contracts` | Yes | Owner |
| POST | `/api/owner/contracts` | Yes | Owner |
| GET | `/api/owner/contracts/{contract}` | Yes | Owner |
| PUT / PATCH | `/api/owner/contracts/{contract}` | Yes | Owner |
| DELETE | `/api/owner/contracts/{contract}` | Yes | Owner |
| GET | `/api/contracts` | Yes | Customer |
| GET | `/api/contracts/{contract}` | Yes | Owner or Customer (policy-scoped) |

---

## List contracts (owner)

`GET /api/owner/contracts`

**Auth:** required. **Role:** Owner.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

Scoped with `Contract::ownedBy($request->user())`, ordered by `id`, eager
loading `user`, `unit.building.property` and `payments`.

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "unit_id": 1,
      "start_date": "2026-03-01T00:00:00.000000Z",
      "end_date": "2027-03-31T00:00:00.000000Z",
      "monthly_rent": "14000.00",
      "security_deposit": "28000.00",
      "status": "active",
      "notes": "Twelve-month lease, rent due on the 1st of each month.",
      "created_at": "2026-08-28T15:29:09.000000Z",
      "updated_at": "2026-08-28T15:29:09.000000Z",
      "user": { "id": 2, "name": "Omar Sabry", "email": "customer@propspace.com", "phone": "01098000001", "role": "customer", "status": "active", "created_at": "2026-08-28T15:29:07+00:00" },
      "unit": { "id": 1, "building_id": 1, "property_id": 1, "property_name": "Nile View Residences", "unit_number": "A-101", "floor": 1, "unit_type": "Apartment", "area": 110, "bedrooms": 2, "bathrooms": 1, "monthly_rent": 14000, "status": "occupied", "building": { "id": 1, "name": "Tower A", "floors_count": 6, "description": "North tower, river-facing units." } },
      "payments": [
        { "id": 1, "contract_id": 1, "amount": "14000.00", "due_date": "2026-04-01T00:00:00.000000Z", "paid_date": "2026-04-03T00:00:00.000000Z", "payment_method": "bank_transfer", "status": "paid", "reference": "PAY-2026-0001", "notes": null }
      ]
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 2, "total": 5 }
}
```

`monthly_rent` and `security_deposit` are decimal **strings** (`decimal:2`
cast); dates are ISO timestamps, not bare dates.

### Errors

`401` unauthenticated · `403` not an owner.

---

## Create contract

`POST /api/owner/contracts`

**Auth:** required. **Role:** Owner. **Policy:** `ContractPolicy::create` (owner role).

### Body

```json
{
  "user_id": 4,
  "unit_id": 12,
  "start_date": "2026-09-01",
  "end_date": "2027-08-31",
  "monthly_rent": 14000,
  "security_deposit": 28000,
  "status": "active",
  "notes": "Twelve-month lease."
}
```

### Validation

| Field | Rules | Required |
|-------|-------|----------|
| `user_id` | integer, `exists:users,id`, **and the user's role must be `customer`** | Yes |
| `unit_id` | integer, `exists:units,id`, **and the unit must belong to the authenticated owner** | Yes |
| `start_date` | date | Yes |
| `end_date` | date, `after:start_date` | Yes |
| `monthly_rent` | numeric, min 0 | Yes |
| `security_deposit` | numeric, min 0 | No (DB default `0`) |
| `status` | one of `active`, `expired`, `terminated` | No (DB default `active`) |
| `notes` | string | No |

### Unit availability rule

`unitIsLettableTo($unit, $customer)`:

- `available` → allowed;
- `reserved` → allowed **only** if that customer has an `approved`
  purchase request on that unit (this contract is the next step of that flow);
- `occupied` → refused.

### Side effects

1. the contract row is inserted;
2. the unit's status becomes `occupied`;
3. the **customer** receives a `contract.created` notification.

There is no database transaction around `store()` — steps 1 and 2 are separate
statements.

### Success — `201 Created`

`{ "data": { ...ContractResource with user, unit, payments } }`

### Errors

| Code | Body | Cause |
|------|------|-------|
| `401` | `Unauthenticated.` | no token |
| `403` | `This action is unauthorized.` | not an owner |
| `403` | `You are not authorized to use this unit.` | the unit belongs to another owner |
| `422` | `The selected customer is invalid.` | `user_id` is not a `customer` |
| `422` | `This unit is not available.` | occupied, or reserved for someone else |
| `422` | `errors{}` | field validation |

---

## View contract

`GET /api/owner/contracts/{contract}` (owner) ·
`GET /api/contracts/{contract}` (customer or owner)

**Policy:** `ContractPolicy::view` — a customer may see a contract where
`contract.user_id` is their own id; an owner may see contracts on their own
units. Nobody else, in either direction.

### Success — `200 OK`

`{ "data": { ...ContractResource } }` with `user`, `unit.building.property`
and `payments` loaded.

### Errors

`401` · `403` (someone else's contract) · `404` (unknown id).

---

## Update contract

### Request

`PUT /api/owner/contracts/{contract}` — `PATCH` is also routed. The SPA uses `PUT`.

**Auth:** required. **Role:** Owner. **Policy:** `ContractPolicy::update`
(ownership through unit → building → property).

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `contract` | integer | Yes | Contract id (route parameter) |

### Body

Every field is optional (`sometimes`) — send only what changes.

```json
{
  "unit_id": 12,
  "user_id": 4,
  "start_date": "2026-09-01",
  "end_date": "2027-08-31",
  "monthly_rent": 15000,
  "security_deposit": 30000,
  "status": "active",
  "notes": "Renewed for a further year."
}
```

### Validation

| Field | Rules |
|-------|-------|
| `user_id` | `sometimes`, integer, `exists:users,id`; must resolve to a `customer` |
| `unit_id` | `sometimes`, integer, `exists:units,id`; must belong to the authenticated owner |
| `start_date` | `sometimes`, date |
| `end_date` | `sometimes`, date, `after:start_date` |
| `monthly_rent` | `sometimes`, numeric, min 0 |
| `security_deposit` | `sometimes`, numeric, min 0 |
| `status` | `sometimes`, in `active,expired,terminated` |
| `notes` | nullable, string |

### Unit reassignment behaviour

- **Same unit** (or `unit_id` omitted): no availability check at all.
- **Different unit:** the target must clear `unitIsLettableTo()` for whoever
  will hold the lease after the update (the new `user_id` if one was sent,
  otherwise the existing customer) — otherwise an edit would be a way to let a
  unit that is already taken.
- On a successful move, inside **one `DB::transaction`**:
  1. the contract row is updated;
  2. the **target** unit becomes `occupied`;
  3. the **previous** unit is released to `available` — but only if no other
     `active` contract still references it (a unit accumulates contracts over
     time).

After the transaction commits, the contract's customer receives a
`contract.updated` notification.

### Success — `200 OK`

```json
{
  "data": {
    "id": 2,
    "user_id": 3,
    "unit_id": 2,
    "start_date": "2026-06-01T00:00:00.000000Z",
    "end_date": "2027-06-30T00:00:00.000000Z",
    "monthly_rent": "15000.00",
    "security_deposit": "27000.00",
    "status": "active",
    "notes": "Renewal of a previous lease in the same tower.",
    "created_at": "2026-08-28T15:29:09.000000Z",
    "updated_at": "2026-08-28T15:31:57.000000Z",
    "user": { "id": 3, "name": "Salma Adel", "role": "customer" },
    "unit": { "id": 2, "unit_number": "A-102", "status": "occupied" },
    "payments": []
  }
}
```

### Errors

| Code | Body | Cause |
|------|------|-------|
| `401` | `Unauthenticated.` | no token |
| `403` | `This action is unauthorized.` | not an owner, or not this owner's contract |
| `403` | `You are not authorized to use this unit.` | target unit belongs to another owner |
| `404` | model not found | unknown contract id |
| `422` | `The selected customer is invalid.` | `user_id` is not a customer |
| `422` | `This unit is not available.` | target unit occupied, or reserved for another customer |
| `422` | `errors{}` | field validation, e.g. `end_date` not after `start_date` |

---

## Delete contract

### Request

`DELETE /api/owner/contracts/{contract}`

**Auth:** required. **Role:** Owner. **Policy:** `ContractPolicy::delete`.

### Payment dependency

`payments.contract_id` is `RESTRICT` on delete, because collection history has
to outlive the contract. The controller checks first and refuses cleanly:

```json
{ "message": "This contract still has payments recorded against it. Remove those payments first." }
```

…with status `409 Conflict`. Delete or reassign the payments first.

### Transaction and unit release

With no payments, inside one `DB::transaction`:

1. the contract row is deleted (hard delete — no soft deletes in this project);
2. `releaseUnit()` sets the unit back to `available`, **unless** another
   `active` contract still holds it.

The notification payload is built from values captured **before** the delete
(contract id, unit number), because the record no longer exists when it is
sent. The customer then receives `contract.deleted`, linking to the contract
list rather than to a page that no longer exists.

### Success — `204 No Content`

Empty body.

### Errors

| Code | Cause |
|------|-------|
| `401` | no token |
| `403` | not an owner, or not this owner's contract |
| `404` | unknown contract id |
| `409` | the contract still has payments |

---

## Customer: my contracts

`GET /api/contracts`

**Auth:** required. **Role:** Customer (`ContractPolicy::viewAny`).

Returns the token holder's own contracts, newest first, with
`unit.building.property` and `payments` loaded. **Not paginated** — the
response is `{ "data": [ ... ] }` with no `meta`.

`GET /api/contracts/{contract}` returns one contract, subject to the same
policy as the owner detail endpoint.

### Errors

`401` unauthenticated · `403` another customer's contract.
