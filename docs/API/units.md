# Units API

**Source:** `backend/app/Http/Controllers/Owner/UnitController.php`,
`backend/app/Http/Controllers/Customer/UnitController.php`,
`StoreUnitRequest.php`, `UpdateUnitRequest.php`,
`backend/app/Policies/UnitPolicy.php`,
`backend/app/Http/Resources/UnitResource.php`,
`frontend/src/services/units.js`.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/properties/{property}/units` | No | Public |
| GET | `/api/units/{unit}` | No | Public |
| GET | `/api/owner/units` | Yes | Owner |
| POST | `/api/owner/units` | Yes | Owner |
| GET | `/api/owner/units/{unit}` | Yes | Owner |
| PUT / PATCH | `/api/owner/units/{unit}` | Yes | Owner |
| DELETE | `/api/owner/units/{unit}` | Yes | Owner |

Unit statuses are exactly `available`, `occupied`, `reserved`. There is no
`sold` status.

---

## Public: units in a property

`GET /api/properties/{property}/units`

**Auth:** not required. The property must be published and active, otherwise
`404 Property not found.`

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `status` | `available` \| `occupied` \| `reserved` | **`available`** | when omitted, only available units are returned, so a visitor sees what they can act on. Send an explicit status to show the full inventory. |

Ordered by `unit_number`. **Not paginated** — `{ "data": [ ... ] }`.

### Success — `200 OK`

```json
{
  "data": [
    {
      "id": 2,
      "building_id": 1,
      "property_id": 1,
      "property_name": "Nile View Residences",
      "unit_number": "A-102",
      "floor": 1,
      "unit_type": "Apartment",
      "area": 95,
      "bedrooms": 2,
      "bathrooms": 1,
      "monthly_rent": 12000,
      "status": "available",
      "building": { "id": 1, "name": "Tower A", "floors_count": 6, "description": "North tower." },
      "created_at": "2026-08-28T15:29:09+00:00",
      "updated_at": "2026-08-28T15:29:09+00:00"
    }
  ]
}
```

`GET /api/units/{unit}` returns one unit. A unit inside an unpublished or
inactive property answers `404 Unit not found.` (`Unit::scopePubliclyVisible`).

Note the numeric casts in `UnitResource`: `area` and `monthly_rent` are
**floats** here, whereas the same values on a contract are decimal strings.

---

## Owner: list units

`GET /api/owner/units`

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `building_id` | integer | — | units in one building |
| `property_id` | integer | — | units in one property |
| `status` | enum | — | exact status match |
| `search` | string | — | matches `unit_number` or `unit_type` |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

Scoped with `Unit::ownedBy($owner)`, ordered by `id`, with `building.property`
loaded. Returns a paginated `UnitResource` collection.

---

## Create unit

`POST /api/owner/units`

### Body

```json
{
  "building_id": 1,
  "unit_number": "A-103",
  "floor": 1,
  "unit_type": "Apartment",
  "area": 95.5,
  "bedrooms": 2,
  "bathrooms": 1,
  "monthly_rent": 12000,
  "status": "available"
}
```

### Validation (`StoreUnitRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `building_id` | integer, `exists:buildings,id` | Yes |
| `unit_number` | string, max 255; **unique within the building** | Yes |
| `floor` | integer, 0–200 | No (default `0`) |
| `unit_type` | string, max 100 | Yes |
| `area` | numeric, 0–99999.99 | No (nullable) |
| `bedrooms` | integer, 0–50 | No (default `0`) |
| `bathrooms` | integer, 0–50 | No (default `0`) |
| `monthly_rent` | numeric, 0–99999999.99 | Yes |
| `status` | in `available`, `occupied`, `reserved` | No (default `available`) |

The building is authorized with `Gate::authorize('update', $building)` — a unit
inherits its ownership from its building.

`guardUniqueUnitNumber()` turns a violation of the
`unique(building_id, unit_number)` index into a normal validation error:

```json
{ "message": "...", "errors": { "unit_number": ["That unit number is already used in this building."] } }
```

### Success — `201 Created`

`{ "data": { ...UnitResource } }`

### Errors

`401` · `403` (not an owner, or the building is not yours) · `422`.

---

## View / update unit

`GET /api/owner/units/{unit}` — policy `view`; loads `building.property`,
`contracts` and `purchaseRequests`.

`PUT /api/owner/units/{unit}` — policy `update`. All fields `sometimes`.
Moving a unit to another building authorizes the target building as well, and
the unit-number uniqueness check runs against the **target** building,
ignoring the unit itself.

### Errors

`401` · `403` · `404` · `422`.

---

## Delete unit

`DELETE /api/owner/units/{unit}`

Both `contracts.unit_id` and `purchase_requests.unit_id` are `RESTRICT`, so:

| Code | Body |
|------|------|
| `409` | `This unit has contracts against it and cannot be deleted.` |
| `409` | `This unit has purchase requests against it and cannot be deleted.` |

### Success — `204 No Content`

### Errors

`401` · `403` · `404` · `409`.
