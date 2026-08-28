# Buildings API

**Source:** `backend/app/Http/Controllers/Owner/BuildingController.php`,
`StoreBuildingRequest.php`, `UpdateBuildingRequest.php`,
`backend/app/Policies/BuildingPolicy.php`,
`backend/app/Http/Resources/BuildingResource.php`,
`frontend/src/services/buildings.js`.

Buildings are **owner-only**. There is no public buildings endpoint — the
public catalog exposes properties and units only.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/owner/buildings` | Yes | Owner |
| POST | `/api/owner/buildings` | Yes | Owner |
| GET | `/api/owner/buildings/{building}` | Yes | Owner |
| PUT / PATCH | `/api/owner/buildings/{building}` | Yes | Owner |
| DELETE | `/api/owner/buildings/{building}` | Yes | Owner |

Ownership is derived through the building's property
(`Building::scopeOwnedBy` → `property.owner_id`).

---

## List buildings

`GET /api/owner/buildings`

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `property_id` | integer | — | only buildings in this property |
| `search` | string | — | matches `name` (LIKE) |
| `per_page` | integer | 15 | page size |
| `page` | integer | 1 | page number |

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": 1,
      "property_id": 1,
      "name": "Tower A",
      "floors_count": 6,
      "description": "North tower, river-facing units.",
      "units_count": 3,
      "property": { "id": 1, "name": "Nile View Residences", "city": "Cairo" },
      "created_at": "2026-08-28T15:29:09+00:00",
      "updated_at": "2026-08-28T15:29:09+00:00"
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 5 }
}
```

`units_count` appears when the controller counted units (index and show);
`units` (a `UnitResource` collection) appears on `show`.

---

## Create building

`POST /api/owner/buildings`

### Body

```json
{
  "property_id": 1,
  "name": "Tower A",
  "floors_count": 6,
  "description": "North tower, river-facing units."
}
```

### Validation (`StoreBuildingRequest`)

| Field | Rules | Required |
|-------|-------|----------|
| `property_id` | integer, `exists:properties,id` | Yes |
| `name` | string, max 255 | Yes |
| `floors_count` | integer, 1–200 | No (default `1`) |
| `description` | string, max 5000 | No |

The controller additionally runs `Gate::authorize('update', $property)`, so a
building can only ever be attached to a property you own — that is a `403`,
not a `422`.

### Success — `201 Created`

`{ "data": { ...BuildingResource } }`

### Errors

`401` · `403` (not an owner, or the property is not yours) · `422`.

---

## View / update building

`GET /api/owner/buildings/{building}` — policy `view`; loads `property` and
`units`.

`PUT /api/owner/buildings/{building}` — policy `update`. All fields
`sometimes`. If `property_id` is sent, the **target** property is authorized
too, so a building can only be moved within your own portfolio.

### Errors

`401` · `403` · `404` · `422`.

---

## Delete building

`DELETE /api/owner/buildings/{building}`

Units cascade at the database level, but the controller refuses first when
anything depends on them:

| Code | Body |
|------|------|
| `409` | `This building still has units under contract. Remove those contracts first.` |
| `409` | `This building still has purchase requests against its units. Resolve those first.` |

### Success — `204 No Content`

### Errors

`401` · `403` · `404` · `409`.
