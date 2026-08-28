# Properties API

**Source:** `backend/app/Http/Controllers/Owner/PropertyController.php`,
`backend/app/Http/Controllers/Customer/PropertyController.php`,
`backend/app/Http/Requests/Owner/StorePropertyRequest.php`,
`UpdatePropertyRequest.php`, `backend/app/Policies/PropertyPolicy.php`,
`backend/app/Http/Resources/PropertyResource.php`,
`frontend/src/services/properties.js`.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/properties` | No | Public |
| GET | `/api/properties/{property}` | No | Public |
| GET | `/api/owner/properties` | Yes | Owner |
| POST | `/api/owner/properties` | Yes | Owner |
| GET | `/api/owner/properties/{property}` | Yes | Owner |
| PUT / PATCH | `/api/owner/properties/{property}` | Yes | Owner |
| DELETE | `/api/owner/properties/{property}` | Yes | Owner |
| POST | `/api/owner/properties/{property}/publish` | Yes | Owner |
| POST | `/api/owner/properties/{property}/unpublish` | Yes | Owner |

---

## Public: browse the catalog

`GET /api/properties`

**Auth:** not required.

Only properties with `is_published = true` **and** `status = 'active'` are ever
returned, and the `owner` relationship is deliberately **not** loaded, so the
catalog never exposes owner contact details.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `search` | string | — | matches `name`, `city` or `address` (LIKE) |
| `city` | string | — | exact city match |
| `property_type` | string | — | exact type match |
| `per_page` | integer | **12** | page size |
| `page` | integer | 1 | page number |

Ordered newest first (`latest()`).

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 1,
      "name": "Nile View Residences",
      "address": "18 Corniche El Nil, Garden City",
      "city": "Cairo",
      "description": "Riverside apartments with balconies overlooking the Nile.",
      "property_type": "Apartment Building",
      "status": "active",
      "is_published": true,
      "owner_id": 1,
      "buildings_count": 2,
      "units_count": 5,
      "available_units_count": 2,
      "from_price": 7000,
      "units": [ { "id": 1, "unit_number": "A-101" } ],
      "created_at": "2026-08-28T15:29:09+00:00",
      "updated_at": "2026-08-28T15:29:09+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "...?page=2" },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 12, "total": 2 }
}
```

`buildings_count`, `units_count`, `available_units_count` and `from_price` are
computed by `PropertyResource`, not columns. `from_price` is the lowest
`monthly_rent` among the property's units (`0` when it has none), and
`buildings_count` is floored at 1 for display.

`GET /api/properties/{property}` returns the same resource for one property,
with `units.building` and `buildings` loaded. An unpublished or inactive
property answers `404 Property not found.` — deliberately indistinguishable
from a property that does not exist.

---

## Owner: list properties

`GET /api/owner/properties`

**Auth:** required. **Role:** Owner. **Policy:** `viewAny`.

Scoped with `Property::ownedBy($owner)`, ordered by `id`.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `search` | string | — | matches `name`, `city` or `address` |
| `status` | `active` \| `inactive` | — | exact match |
| `is_published` | boolean | — | `1`/`0`, `true`/`false` |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

### Success — `200 OK`

Paginated `PropertyResource` collection (same fields as above).

---

## Owner: create property

`POST /api/owner/properties`

### Body

```json
{
  "name": "Nile View Residences",
  "address": "18 Corniche El Nil, Garden City",
  "city": "Cairo",
  "description": "Riverside apartments.",
  "property_type": "Apartment Building",
  "status": "active",
  "is_published": false
}
```

### Validation (`StorePropertyRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `name` | string, max 255 | Yes |
| `address` | string, max 2000 | Yes |
| `city` | string, max 255 | Yes |
| `description` | string, max 5000 | No |
| `property_type` | string, max 100 | Yes |
| `status` | in `active`, `inactive` | No (default `active`) |
| `is_published` | boolean | No (default `false`) |

`owner_id` is **never** read from the body — it is taken from the token.

### Success — `201 Created`

`{ "data": { ...PropertyResource } }`

### Errors

`401` · `403` (not an owner) · `422` validation.

---

## Owner: view / update property

`GET /api/owner/properties/{property}` — policy `view` (must be yours);
returns the property with `owner`, `buildings.units` and `units` loaded.

`PUT /api/owner/properties/{property}` — policy `update`. Same fields as
create, all `sometimes`; `description` and `is_published` may be sent alone.

### Errors

`401` · `403` (another owner's property) · `404` · `422`.

---

## Owner: delete property

`DELETE /api/owner/properties/{property}`

Buildings and units cascade at the database level, but contracts and purchase
requests are `RESTRICT`, so the controller refuses cleanly first:

| Code | Body |
|------|------|
| `409` | `This property still has units under contract. Remove those contracts first.` |
| `409` | `This property still has purchase requests against its units. Resolve those first.` |

### Success — `204 No Content`

### Errors

`401` · `403` · `404` · `409` (as above).

---

## Owner: publish / unpublish

`POST /api/owner/properties/{property}/publish`
`POST /api/owner/properties/{property}/unpublish`

**Policies:** `PropertyPolicy::publish` / `::unpublish` — ownership only.
No body. Sets `is_published` to `true` / `false`.

### Success — `200 OK`

`{ "data": { ...PropertyResource with the new is_published } }`

A property is only visible in the public catalog when `is_published = true`
**and** `status = 'active'`.

### Errors

`401` · `403` (not yours) · `404`.
