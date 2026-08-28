# Payments API

**Source:** `backend/app/Http/Controllers/Owner/PaymentController.php`,
`backend/app/Http/Controllers/Customer/PaymentController.php`,
`StorePaymentRequest.php`, `UpdatePaymentRequest.php`,
`backend/app/Policies/PaymentPolicy.php`,
`backend/app/Http/Resources/PaymentResource.php`,
`backend/app/Notifications/PaymentNotification.php`,
`frontend/src/services/payments.js`.

A payment always belongs to a contract (`payments.contract_id`). Owners create
and manage them; customers can only read their own.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/owner/payments` | Yes | Owner |
| POST | `/api/owner/payments` | Yes | Owner |
| GET | `/api/owner/payments/{payment}` | Yes | Owner |
| PUT / PATCH | `/api/owner/payments/{payment}` | Yes | Owner |
| DELETE | `/api/owner/payments/{payment}` | Yes | Owner |
| GET | `/api/payments` | Yes | Customer |
| GET | `/api/payments/{payment}` | Yes | Owner or Customer (policy-scoped) |

---

## List payments (owner)

`GET /api/owner/payments`

Scoped with `Payment::ownedBy($owner)` — through contract → unit → building →
property. Ordered by `due_date` descending, then `id`, with `contract.user` and
`contract.unit` loaded.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | `pending` \| `paid` \| `overdue` \| `cancelled` | — | exact match |
| `contract_id` | integer | — | payments on one contract |
| `search` | string | — | matches `reference`, `payment_method`, the customer's name, or the unit number |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 6,
      "contract_id": 1,
      "amount": "14000.00",
      "due_date": "2026-09-01T00:00:00.000000Z",
      "paid_date": null,
      "payment_method": null,
      "status": "pending",
      "reference": "PAY-2026-0006",
      "notes": null,
      "created_at": "2026-08-28T15:29:09.000000Z",
      "updated_at": "2026-08-28T15:29:09.000000Z",
      "contract": { "id": 1, "user": { "id": 2, "name": "Omar Sabry" }, "unit": { "id": 1, "unit_number": "A-101" } }
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 20 }
}
```

`contract` is present only when the relation was loaded (index, show, store,
update). `amount` is a decimal string.

---

## Record a payment

`POST /api/owner/payments`

### Body

```json
{
  "contract_id": 1,
  "amount": 14000,
  "due_date": "2026-10-01",
  "paid_date": null,
  "payment_method": "bank_transfer",
  "status": "pending",
  "reference": "PAY-2026-0021",
  "notes": null
}
```

### Validation (`StorePaymentRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `contract_id` | integer, `exists:contracts,id` | Yes |
| `amount` | numeric, **greater than 0** | Yes |
| `due_date` | date | Yes |
| `paid_date` | date | No (nullable) |
| `payment_method` | string, max 255 | No |
| `status` | in `pending`, `paid`, `overdue`, `cancelled` | **Yes** |
| `reference` | string, max 255, unique in `payments` | No (nullable) |
| `notes` | string | No |

`status` is required on create, so a payment can be recorded as already paid.

The contract is authorized with `Gate::authorize('update', $contract)`: a
payment may only be raised against a contract on one of your own units.

### Side effect

The **customer** on the contract receives a `payment.recorded` notification.

### Success — `201 Created`

`{ "data": { ...PaymentResource with contract.user and contract.unit } }`

### Errors

`401` · `403` (not an owner, or the contract is not yours) · `422` validation
(including a duplicate `reference`).

---

## View payment

`GET /api/owner/payments/{payment}` (owner) ·
`GET /api/payments/{payment}` (customer or owner)

**Policy:** `PaymentPolicy::view` — a customer may see a payment whose
contract is theirs (`Payment::customerId()`); an owner may see payments
collected on their own units.

### Errors

`401` · `403` · `404`.

---

## Update payment

`PUT /api/owner/payments/{payment}` (`PATCH` also routed)

### Validation (`UpdatePaymentRequest`)

Same fields as create, all `sometimes`. `reference` uniqueness ignores the
payment being edited. If `contract_id` is sent, the **target** contract is
authorized too, so re-pointing a payment cannot be a way to reach another
owner's records.

### Notification rule

The customer is notified with `payment.updated` **only when `status` actually
changed**. Correcting a typo in the notes notifies nobody
(`NotificationTest::test_a_payment_notifies_only_on_a_real_status_change`).

### Success — `200 OK`

`{ "data": { ...PaymentResource } }`

### Errors

`401` · `403` · `404` · `422`.

---

## Delete payment

`DELETE /api/owner/payments/{payment}`

Nothing depends on a payment, so there is no `409` here — the delete always
succeeds for a payment you own. Deleting payments is how a contract that is
blocked by `409` becomes deletable.

### Success — `204 No Content`

### Errors

`401` · `403` · `404`.

---

## Customer: my payments

`GET /api/payments`

Returns the token holder's payments through their contracts
(`User::payments()` — a `hasManyThrough`), ordered by `due_date` descending,
with `contract.unit.building.property` loaded.

**Not paginated:** `{ "data": [ ... ] }` with no `meta`.

### Errors

`401` unauthenticated · `403` another customer's payment on the detail route.
