# Notifications API

**Source:** `backend/app/Http/Controllers/NotificationController.php`,
`backend/app/Http/Resources/NotificationResource.php`,
`backend/app/Notifications/ActivityNotification.php` and its subclasses,
`backend/database/migrations/2026_08_28_090000_create_notifications_table.php`,
`frontend/src/services/notifications.js`,
`frontend/src/stores/notifications.js`,
`frontend/src/components/notifications/NotificationBell.vue`,
`frontend/src/views/NotificationsView.vue`.

One set of endpoints serves **both roles**. Notifications live outside the
`/api/owner` group deliberately: a notification belongs to a user, not to a
portal, and every query is scoped to the token holder inside the controller.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/notifications` | Yes | Any |
| GET | `/api/notifications/unread-count` | Yes | Any |
| POST | `/api/notifications/{notification}/read` | Yes | Any |
| POST | `/api/notifications/read-all` | Yes | Any |

**No realtime transport.** There are no WebSockets, no broadcasting and no
Laravel Echo in this project (`BROADCAST_CONNECTION=log`). The frontend polls
the unread count.

---

## List notifications

### Request

`GET /api/notifications`

**Authentication:** required. **Role:** any authenticated user.

### Query parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `unread` | boolean | `false` | when truthy, only unread notifications |
| `per_page` | integer | 15 | page size (the bell asks for 5, the history page for 20) |
| `page` | integer | 1 | page number |

Ordered newest first (`latest()`), read from `$request->user()->notifications()`.

### Success — `200 OK` (paginated)

```json
{
  "data": [
    {
      "id": "6d46ab89-e84e-4af6-99e3-327d01d8980a",
      "type": "purchase_request.submitted",
      "title": "New purchase request",
      "message": "Omar Sabry has requested unit A-102.",
      "url": "/owner/purchase-requests/9",
      "is_read": false,
      "read_at": null,
      "created_at": "2026-08-28T15:31:28+00:00"
    }
  ],
  "links": { "first": "...?page=1", "last": "...?page=1", "prev": null, "next": null },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

`NotificationResource` flattens the stored `data` JSON: `type`, `title`,
`message` and `url` are read out of the payload (with fallbacks `activity`,
`Activity`, `''`, `null`), while `is_read` and `read_at` come from the
`read_at` column.

`id` is a **UUID string**, not an integer.

`url` is an in-app SPA route resolved for the recipient's role — the same
event links to `/owner/contracts/12` for an owner and `/contracts/12` for a
customer. It can be `null`.

### Errors

`401` unauthenticated.

---

## Unread count

### Request

`GET /api/notifications/unread-count`

This is the endpoint the bell polls — once a minute while a user is signed in.

### Success — `200 OK`

```json
{ "count": 3 }
```

Note the **bare shape**: no `data` wrapper.

### Errors

`401` unauthenticated.

---

## Mark one as read

### Request

`POST /api/notifications/{notification}/read`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `notification` | uuid | Yes | the notification id |

No body.

The lookup is `$request->user()->notifications()->findOrFail($id)` — scoped to
the caller's own relation, so **another user's notification id is a `404`,
never a silent success**. Marking an already-read notification is idempotent.

### Success — `200 OK`

```json
{
  "data": {
    "id": "6d46ab89-e84e-4af6-99e3-327d01d8980a",
    "type": "purchase_request.submitted",
    "title": "New purchase request",
    "message": "Omar Sabry has requested unit A-102.",
    "url": "/owner/purchase-requests/9",
    "is_read": true,
    "read_at": "2026-08-28T15:31:40+00:00",
    "created_at": "2026-08-28T15:31:28+00:00"
  }
}
```

### Errors

`401` unauthenticated · `404` unknown id, or an id belonging to another user.

---

## Mark all as read

### Request

`POST /api/notifications/read-all`

No body. Calls `markAsRead()` on the caller's unread notifications.

### Success — `200 OK`

```json
{ "count": 0 }
```

The response carries the **recomputed unread count**, which the store uses to
correct its optimistic zero.

### Errors

`401` unauthenticated.

---

## What generates a notification

| Event `type` | Triggered by | Recipient |
|--------------|--------------|-----------|
| `contract.created` | `POST /api/owner/contracts` | the customer on the lease |
| `contract.updated` | `PUT /api/owner/contracts/{id}` | the customer on the lease |
| `contract.deleted` | `DELETE /api/owner/contracts/{id}` | the customer who held it |
| `payment.recorded` | `POST /api/owner/payments` | the customer on the contract |
| `payment.updated` | `PUT /api/owner/payments/{id}` — **only on a status change** | the customer on the contract |
| `purchase_request.submitted` | `POST /api/purchase-requests` | the **owner** of the unit |
| `purchase_request.cancelled` | `DELETE /api/purchase-requests/{id}` | the **owner** of the unit |
| `purchase_request.approved` | `POST /api/owner/purchase-requests/{id}/approve` | the customer who raised it |
| `purchase_request.rejected` | `POST /api/owner/purchase-requests/{id}/reject` | the customer who raised it |

Notifications are written synchronously through Laravel's `database` channel
(`via()` returns `['database']`). They are **not queued** — the project uses
the `database` queue driver with no worker running in development.

---

## Ownership enforcement

Scoping is **structural, not checked**: every query starts from
`$request->user()->notifications()` or `->unreadNotifications()`, so there is
no code path that could read or write another user's row. The table is
polymorphic (`notifiable_type` + `notifiable_id`) with no foreign key, and
carries no role column.

Asserted by `NotificationTest::test_a_user_only_ever_sees_their_own_notifications`
and `frontend/e2e/notifications.spec.js`.

---

## Frontend polling behaviour

Documented separately because it is client behaviour, not an API contract
(`frontend/src/stores/notifications.js`):

| Behaviour | Detail |
|-----------|--------|
| Poll interval | 60 000 ms, unread **count** only |
| Poll lifecycle | starts when `auth.isAuthenticated` becomes true, stops and resets when it becomes false; a guest never polls and sees no bell |
| List fetching | only when the dropdown opens (`per_page: 5`) or the history page loads (`per_page: 20`) |
| Badge | the count, or `9+` above nine |
| Arrival cue | one bell nudge animation when the count **rises** — never on a decrease, never looping |
| Optimistic updates | mark-read and mark-all update the UI first and roll back if the request fails |
| Failed poll | swallowed silently; the next poll retries |
