# Owner Customer Directory API

**Source:** `backend/app/Http/Controllers/Owner/CustomerController.php`,
`backend/app/Http/Resources/UserResource.php`,
`frontend/src/services/customers.js`.

The owner's view of the people they deal with. There is **no tenants table** —
these are `users` rows with `role = 'customer'`.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/owner/customers` | Yes | Owner |
| GET | `/api/owner/customers/{customer}` | Yes | Owner |

This is a **read-only** directory: an owner cannot create, edit or delete
customer accounts. Customers create their own accounts through registration
and manage them through `/api/profile`.

---

## List related customers

### Request

`GET /api/owner/customers`

**Authorization:** `Gate::authorize('viewAny', Contract::class)` — the owner role.

### Scope

Not the user table. A customer appears only when they:

- hold a contract on one of this owner's units, **or**
- have raised a purchase request against one.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `search` | string | — | matches `name`, `email` or `phone` |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 2,
      "name": "Omar Sabry",
      "email": "customer@propspace.com",
      "phone": "01098000001",
      "role": "customer",
      "status": "active",
      "contracts_count": 1,
      "purchase_requests_count": 2,
      "created_at": "2026-08-28T15:29:07+00:00"
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 6 }
}
```

Both counts are **scoped to this owner's units** — they are not the customer's
totals across the platform.

### Errors

`401` unauthenticated · `403` not an owner.

---

## View one customer

### Request

`GET /api/owner/customers/{customer}`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `customer` | integer | Yes | user id |

A customer id that is not connected to this owner **404s** rather than 403s,
so the list scoping cannot be walked around by guessing ids:

```json
{ "message": "Customer not found." }
```

### Success — `200 OK`

```json
{
  "data": {
    "id": 2,
    "name": "Omar Sabry",
    "email": "customer@propspace.com",
    "phone": "01098000001",
    "role": "customer",
    "status": "active",
    "contracts": [ { "id": 1, "unit": { "unit_number": "A-101" }, "status": "active" } ],
    "purchase_requests": [ { "id": 4, "unit_id": 5, "status": "pending" } ],
    "created_at": "2026-08-28T15:29:07+00:00"
  }
}
```

`contracts` and `purchase_requests` are filtered to records on **this owner's**
properties only — an owner never sees a customer's dealings with anyone else.

### Errors

`401` · `403` (not an owner) · `404` (unknown, not a customer, or unrelated).
