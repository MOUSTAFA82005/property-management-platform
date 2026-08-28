# PropSpace API Reference

The PropSpace backend is a **stateless JSON API** built on Laravel, authenticated
with **Laravel Sanctum personal access tokens**. Every endpoint documented here
was read out of `backend/routes/api.php` and its controller, and the endpoint
index below was generated from `php artisan route:list`.

| | |
|---|---|
| Routes | 56 API routes |
| Auth | Sanctum bearer token (`Authorization: Bearer <token>`) |
| Roles | `owner`, `customer` — there is no admin role |
| Format | JSON in, JSON out |

## Contents

| Document | Covers |
|----------|--------|
| [`authentication.md`](./authentication.md) | register, login, logout, me |
| [`properties.md`](./properties.md) | public catalog + owner property CRUD, publish/unpublish |
| [`buildings.md`](./buildings.md) | owner building CRUD |
| [`units.md`](./units.md) | public unit browsing + owner unit CRUD |
| [`purchase-requests.md`](./purchase-requests.md) | customer requests, owner approve/reject |
| [`contracts.md`](./contracts.md) | owner contract CRUD (create / edit / delete) + customer read |
| [`payments.md`](./payments.md) | owner payment CRUD + customer read |
| [`customers.md`](./customers.md) | owner's related-customer directory |
| [`profile.md`](./profile.md) | shared profile read/update |
| [`notifications.md`](./notifications.md) | notification list, unread count, mark read |
| [`dashboard.md`](./dashboard.md) | owner dashboard aggregates |
| [`authorization.md`](./authorization.md) | how access is enforced server-side |

Related: [ERD](../ERD/README.md) · [UML diagrams](../UML/README.md)

---

## Base URL

There is no hosted deployment; the base URL is whatever the backend is served
from, plus `/api`.

| Context | Base URL | Where it comes from |
|---------|----------|---------------------|
| Local development | `http://127.0.0.1:8000/api` | `php artisan serve` default; the SPA's fallback in `frontend/src/services/api.js` |
| SPA override | `${VITE_API_BASE_URL}` | a `.env` in `frontend/`, read at build time by Vite |
| Playwright E2E | `http://127.0.0.1:8001/api` | `frontend/.env.e2e` |

All examples below use `http://127.0.0.1:8000/api`.

## Authentication

Sanctum **token** authentication, not cookie/session authentication. The SPA
never calls `/sanctum/csrf-cookie`, `SESSION_DRIVER` is `file`, and no
`sessions` table is migrated.

```http
POST /api/owner/contracts HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|GZ2OnW2ZcbLi5zCOlgKIDj7fQFIznysRon9yyNeX9c4f1c0f
Accept: application/json
Content-Type: application/json
```

`frontend/src/services/api.js` attaches the header in a request interceptor
from `localStorage['token']`; no view or service sets it by hand. A token is
issued by `POST /api/auth/register` and `POST /api/auth/login`, and revoked by
`POST /api/auth/logout` (only the token that made the request).

Tokens do not expire: `config/sanctum.php` sets `'expiration' => null`.

## Request format

- `Content-Type: application/json` and `Accept: application/json` on every
  request. `bootstrap/app.php` renders **every** `api/*` failure as JSON, so an
  error is never an HTML page.
- Body parameters are JSON; there are no file uploads anywhere in this API.
- Query parameters are used for filtering, searching and pagination
  (`?search=`, `?status=`, `?per_page=`, `?page=`).

## Response format

Three shapes exist, and each endpoint's page says which one it returns.

**1. Paginated collection** — every owner list endpoint, plus notifications:

```json
{
  "data": [ { "id": 1 } ],
  "links": { "first": "...?page=1", "last": "...?page=3", "prev": null, "next": "...?page=2" },
  "meta": {
    "current_page": 1, "from": 1, "last_page": 3,
    "links": [ { "url": null, "label": "&laquo; Previous", "page": null, "active": false } ],
    "path": "http://127.0.0.1:8000/api/owner/contracts",
    "per_page": 2, "to": 2, "total": 5
  }
}
```

**2. Unpaginated collection** — `{ "data": [...] }` with no `meta`. Used by the
customer's own lists (`GET /api/contracts`, `GET /api/payments`) and
`GET /api/properties/{property}/units`, which call `->get()` rather than
`->paginate()`.

**3. Single resource** — `{ "data": { ... } }`.

The auth and profile endpoints are the exception: they return
`{ "user": {...} }` (and `{ "message": ..., "token": ... }` on login/register)
rather than a `data` wrapper, because they predate the resource-collection
endpoints and the SPA reads them that way.

`frontend/src/services/pagination.js` normalises all three shapes for the
stores.

## HTTP status codes

| Code | When PropSpace uses it |
|------|------------------------|
| `200 OK` | successful read, update, publish/unpublish, approve/reject, mark-read |
| `201 Created` | `POST` that created a row: register, property, building, unit, contract, payment, purchase request |
| `204 No Content` | successful `DELETE` of a property, building, unit, contract or payment (empty body) |
| `401 Unauthorized` | no token, revoked/expired token, or wrong login credentials |
| `403 Forbidden` | wrong role for the route, a record that is not yours, or a deactivated account at login |
| `404 Not Found` | unknown id, an unpublished property/unit requested publicly, or a customer unrelated to the owner |
| `409 Conflict` | a delete blocked by dependent records (contract with payments, property/building/unit with contracts or requests) |
| `422 Unprocessable Content` | validation failure, or a rejected domain transition (unit not available, request already decided, duplicate open request) |

`500` is not a documented outcome of any endpoint — the dependency conflicts
that would otherwise become database errors are all caught and returned as
`409` or `422`.

## Error format

Validation errors (`422`) use Laravel's standard shape:

```json
{
  "message": "The user id field is required. (and 4 more errors)",
  "errors": {
    "user_id": ["The user id field is required."],
    "unit_id": ["The unit id field is required."],
    "start_date": ["The start date field is required."]
  }
}
```

Every other error is a bare message:

```json
{ "message": "This action is unauthorized." }
```

```json
{ "message": "This contract still has payments recorded against it. Remove those payments first." }
```

With `APP_DEBUG=true` (the `.env.example` default), `404` and `500` responses
additionally carry `exception`, `file`, `line` and `trace`. With
`APP_DEBUG=false` only `message` is returned.

`frontend/src/services/pagination.js → normalizeError()` maps these into
`{ status, message, errors, isValidation, isForbidden, isNotFound }`.

---

## Endpoint index

Generated from `php artisan route:list`. **Auth** = a Sanctum token is
required. **Role** = the role gate applied by middleware.

### Authentication

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| POST | `/api/auth/register` | No | Public | Create an account, receive a token |
| POST | `/api/auth/login` | No | Public | Exchange credentials for a token |
| POST | `/api/auth/logout` | Yes | Any | Revoke the current token |
| GET | `/api/auth/me` | Yes | Any | The token holder's user record |

### Public catalog

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| GET | `/api/properties` | No | Public | List published, active properties |
| GET | `/api/properties/{property}` | No | Public | Property details |
| GET | `/api/properties/{property}/units` | No | Public | Units in a published property |
| GET | `/api/units/{unit}` | No | Public | Unit details |

### Customer

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| GET | `/api/purchase-requests` | Yes | Customer | Own purchase requests |
| POST | `/api/purchase-requests` | Yes | Customer | Submit a request for a unit |
| GET | `/api/purchase-requests/{purchaseRequest}` | Yes | Owner or Customer | One request (policy-scoped) |
| DELETE | `/api/purchase-requests/{purchaseRequest}` | Yes | Customer | Cancel own request |
| GET | `/api/contracts` | Yes | Customer | Own contracts |
| GET | `/api/contracts/{contract}` | Yes | Owner or Customer | One contract (policy-scoped) |
| GET | `/api/payments` | Yes | Customer | Own payments |
| GET | `/api/payments/{payment}` | Yes | Owner or Customer | One payment (policy-scoped) |

### Shared (both roles)

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| GET | `/api/profile` | Yes | Any | Own profile |
| PUT | `/api/profile` | Yes | Any | Update own profile / password |
| GET | `/api/notifications` | Yes | Any | Own notifications, paginated |
| GET | `/api/notifications/unread-count` | Yes | Any | Unread count (polled by the bell) |
| POST | `/api/notifications/{notification}/read` | Yes | Any | Mark one as read |
| POST | `/api/notifications/read-all` | Yes | Any | Mark all as read |

### Owner — portfolio

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| GET | `/api/owner/dashboard` | Yes | Owner | Scoped aggregates |
| GET | `/api/owner/properties` | Yes | Owner | List own properties |
| POST | `/api/owner/properties` | Yes | Owner | Create a property |
| GET | `/api/owner/properties/{property}` | Yes | Owner | Property details |
| PUT / PATCH | `/api/owner/properties/{property}` | Yes | Owner | Update a property |
| DELETE | `/api/owner/properties/{property}` | Yes | Owner | Delete a property |
| POST | `/api/owner/properties/{property}/publish` | Yes | Owner | Make it public |
| POST | `/api/owner/properties/{property}/unpublish` | Yes | Owner | Hide it from the catalog |
| GET | `/api/owner/buildings` | Yes | Owner | List buildings |
| POST | `/api/owner/buildings` | Yes | Owner | Create a building |
| GET | `/api/owner/buildings/{building}` | Yes | Owner | Building details |
| PUT / PATCH | `/api/owner/buildings/{building}` | Yes | Owner | Update a building |
| DELETE | `/api/owner/buildings/{building}` | Yes | Owner | Delete a building |
| GET | `/api/owner/units` | Yes | Owner | List units |
| POST | `/api/owner/units` | Yes | Owner | Create a unit |
| GET | `/api/owner/units/{unit}` | Yes | Owner | Unit details |
| PUT / PATCH | `/api/owner/units/{unit}` | Yes | Owner | Update a unit |
| DELETE | `/api/owner/units/{unit}` | Yes | Owner | Delete a unit |

### Owner — lettings and money

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| GET | `/api/owner/customers` | Yes | Owner | Customers related to this owner |
| GET | `/api/owner/customers/{customer}` | Yes | Owner | One related customer, with their records |
| GET | `/api/owner/purchase-requests` | Yes | Owner | Requests on own units |
| GET | `/api/owner/purchase-requests/{purchaseRequest}` | Yes | Owner | One request |
| POST | `/api/owner/purchase-requests/{purchaseRequest}/approve` | Yes | Owner | Approve — reserves the unit |
| POST | `/api/owner/purchase-requests/{purchaseRequest}/reject` | Yes | Owner | Reject — unit untouched |
| GET | `/api/owner/contracts` | Yes | Owner | List contracts on own units |
| POST | `/api/owner/contracts` | Yes | Owner | Create a contract |
| GET | `/api/owner/contracts/{contract}` | Yes | Owner | Contract details |
| PUT / PATCH | `/api/owner/contracts/{contract}` | Yes | Owner | Update a contract |
| DELETE | `/api/owner/contracts/{contract}` | Yes | Owner | Delete a contract |
| GET | `/api/owner/payments` | Yes | Owner | List payments |
| POST | `/api/owner/payments` | Yes | Owner | Record a payment |
| GET | `/api/owner/payments/{payment}` | Yes | Owner | Payment details |
| PUT / PATCH | `/api/owner/payments/{payment}` | Yes | Owner | Update a payment |
| DELETE | `/api/owner/payments/{payment}` | Yes | Owner | Delete a payment |

> `/api/purchase-requests/{id}`, `/api/contracts/{id}` and `/api/payments/{id}`
> sit outside the `role:owner` group, so an owner can reach them too — the
> policy then decides. Every other customer route is effectively
> customer-only because its policy refuses owners.

## Verifying this document

```bash
cd backend
php artisan route:list          # the endpoint index above
php artisan test                # 156 tests over auth, isolation, CRUD, notifications
```
