# Purchase Requests API

**Source:** `backend/app/Http/Controllers/Customer/PurchaseRequestController.php`,
`backend/app/Http/Controllers/Owner/PurchaseRequestController.php`,
`StorePurchaseRequestRequest.php`,
`backend/app/Policies/PurchaseRequestPolicy.php`,
`backend/app/Http/Resources/PurchaseRequestResource.php`,
`backend/app/Notifications/PurchaseRequestNotification.php`,
`frontend/src/services/purchaseRequests.js`.

A purchase request is a customer's enquiry about a specific unit. Approving
one **reserves** the unit; a contract written for that customer then converts
the reservation into an occupancy.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/purchase-requests` | Yes | Customer |
| POST | `/api/purchase-requests` | Yes | Customer |
| GET | `/api/purchase-requests/{purchaseRequest}` | Yes | Owner or Customer |
| DELETE | `/api/purchase-requests/{purchaseRequest}` | Yes | Customer |
| GET | `/api/owner/purchase-requests` | Yes | Owner |
| GET | `/api/owner/purchase-requests/{purchaseRequest}` | Yes | Owner |
| POST | `/api/owner/purchase-requests/{purchaseRequest}/approve` | Yes | Owner |
| POST | `/api/owner/purchase-requests/{purchaseRequest}/reject` | Yes | Owner |

There is **no generic update endpoint** — the backend exposes explicit
transitions instead.

---

## Customer: my requests

`GET /api/purchase-requests`

Filtered to `customer_id = the token holder`, newest first, with
`unit.building.property` loaded.

### Query parameters

| Parameter | Type | Default |
|-----------|------|---------|
| `status` | `pending` \| `approved` \| `rejected` \| `cancelled` | — |
| `per_page` | integer | 15 |
| `page` | integer | 1 |

### Success — `200 OK` (paginated `PurchaseRequestResource` collection)

---

## Customer: submit a request

`POST /api/purchase-requests`

**Policy:** `PurchaseRequestPolicy::create` — customers only.

### Body

```json
{ "unit_id": 2, "notes": "Interested, please advise." }
```

### Validation

| Field | Rules | Required |
|-------|-------|----------|
| `unit_id` | integer, `exists:units,id` | Yes |
| `notes` | string, max 2000 | No |

### Additional rules, enforced in the controller

| Rule | Response when it fails |
|------|------------------------|
| the unit must be publicly visible (published + active property) | `404 Unit not found.` |
| `unit.status` must be `available` | `422 That unit is not currently available.` |
| the customer must not already have a `pending` or `approved` request on this unit | `422 You already have an open request for this unit.` |

`customer_id` comes from the token, never from the body.

### Side effect

The **owner of the unit** receives a `purchase_request.submitted`
notification.

### Success — `201 Created`

```json
{
  "data": {
    "id": 9,
    "customer_id": 2,
    "unit_id": 2,
    "status": "pending",
    "notes": "Interested, please advise.",
    "unit": { "id": 2, "unit_number": "A-102", "property_name": "Nile View Residences" },
    "created_at": "2026-08-28T15:31:28+00:00",
    "updated_at": "2026-08-28T15:31:28+00:00"
  }
}
```

### Errors

`401` · `403` (an owner attempting to create one) · `404` · `422`.

---

## View a request

`GET /api/purchase-requests/{purchaseRequest}` ·
`GET /api/owner/purchase-requests/{purchaseRequest}`

**Policy:** `view` — a customer sees their own; an owner sees requests against
units in their own properties.

### Errors

`401` · `403` · `404`.

---

## Customer: cancel a request

`DELETE /api/purchase-requests/{purchaseRequest}`

**Policy:** `delete` — only the requesting customer.

This is **not a row deletion**: the request transitions to `cancelled` and the
updated resource is returned.

| Rule | Response |
|------|----------|
| status must be `pending` or `approved` | `422 This request has already been {status}.` |

If the request was `approved`, its reservation is released inside a
transaction — the unit returns to `available` unless another approved request
still holds it. The **owner** is then notified with
`purchase_request.cancelled`.

### Success — `200 OK`

`{ "data": { ...PurchaseRequestResource with status "cancelled" } }`

Note this returns `200` with a body, not `204`.

### Errors

`401` · `403` (someone else's request) · `404` · `422`.

---

## Owner: list requests

`GET /api/owner/purchase-requests`

Scoped with `PurchaseRequest::ownedBy($owner)` through unit → building →
property, ordered by `id`, with `customer` and `unit.building.property` loaded.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | enum | — | exact match |
| `search` | string | — | matches the customer's name or email, or the unit number |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

### Success — `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "customer_id": 3,
      "unit_id": 3,
      "status": "approved",
      "notes": "Viewing completed, paperwork with the customer.",
      "customer": { "id": 3, "name": "Salma Adel", "email": "customer2@propspace.com", "role": "customer", "status": "active" },
      "unit": { "id": 3, "unit_number": "B-102", "status": "reserved" },
      "created_at": "2026-08-28T15:29:09+00:00",
      "updated_at": "2026-08-28T15:29:09+00:00"
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 8 }
}
```

---

## Owner: approve

`POST /api/owner/purchase-requests/{purchaseRequest}/approve`

**Policy:** `approve` — only the owner of the requested unit. No body.

| Precondition | Failure response |
|--------------|------------------|
| request status is `pending` | `422 This request has already been {status} and cannot be approved.` |
| `unit.status` is `available` | `422 That unit is no longer available, so this request cannot be approved.` |

Inside one transaction: the request becomes `approved` and the unit becomes
`reserved`. The customer is notified with `purchase_request.approved`.

### Success — `200 OK`

`{ "data": { ...PurchaseRequestResource, status: "approved" } }`

---

## Owner: reject

`POST /api/owner/purchase-requests/{purchaseRequest}/reject`

**Policy:** `reject`. No body. Must be `pending`, otherwise `422` with the
same wording. The unit is left exactly as it was. The customer is notified
with `purchase_request.rejected`.

### Success — `200 OK`

`{ "data": { ...PurchaseRequestResource, status: "rejected" } }`

### Errors (both transitions)

`401` · `403` (not the owner of the unit) · `404` · `422`.
