# Sequence — Notifications

**Derived from:** `backend/app/Notifications/ActivityNotification.php` and its
three subclasses, `backend/app/Http/Controllers/NotificationController.php`,
`backend/app/Http/Resources/NotificationResource.php`,
`backend/database/migrations/2026_08_28_090000_create_notifications_table.php`,
`frontend/src/stores/notifications.js`,
`frontend/src/services/notifications.js`,
`frontend/src/components/notifications/NotificationBell.vue`,
`frontend/src/views/NotificationsView.vue`.

## 1. Raising and storing a notification

```mermaid
sequenceDiagram
    autonumber
    actor A as Actor (owner or customer)
    participant C as Controller action
    participant U as User (Notifiable)
    participant NC as ActivityNotification subclass
    participant DB as notifications table

    A->>C: performs an action (create contract,<br/>record payment, submit/approve a request)
    C->>C: writes the domain change first
    C->>U: $recipient->notify(SomeNotification::event(...))
    U->>NC: via($notifiable)
    NC-->>U: ['database'] only
    U->>NC: toDatabase($notifiable) → payload()
    NC->>NC: builds { type, title, message, url }
    Note over NC: routeFor() picks an owner path or a<br/>customer path from the recipient's role,<br/>so the same event links correctly for both
    U->>DB: INSERT (uuid id, type, notifiable_type,<br/>notifiable_id, data, read_at = NULL)
```

Notifications are written **synchronously** — `ActivityNotification` does not
implement `ShouldQueue`, because the project runs the `database` queue driver
with no worker in development, so a queued notification would never arrive.

### Who gets notified, and when

| Event | Raised in | Recipient |
|-------|-----------|-----------|
| `contract.created` | `Owner\ContractController@store` | the customer on the lease |
| `contract.updated` | `Owner\ContractController@update` | the customer on the lease |
| `contract.deleted` | `Owner\ContractController@destroy` | the customer who held the lease |
| `payment.recorded` | `Owner\PaymentController@store` | the customer on the contract |
| `payment.updated` | `Owner\PaymentController@update` | the customer — **only on a real status change** |
| `purchase_request.submitted` | `Customer\PurchaseRequestController@store` | the **owner** of the unit |
| `purchase_request.cancelled` | `Customer\PurchaseRequestController@destroy` | the **owner** of the unit |
| `purchase_request.approved` | `Owner\PurchaseRequestController@approve` | the customer who raised it |
| `purchase_request.rejected` | `Owner\PurchaseRequestController@reject` | the customer who raised it |

Notifications flow **both ways**: owner actions notify customers, and customer
actions notify the owner of the unit. An actor is never notified of their own
action.

## 2. Polling, reading and marking read

```mermaid
sequenceDiagram
    autonumber
    actor U as Signed-in user
    participant B as NotificationBell.vue
    participant S as useNotificationsStore
    participant AX as Axios client
    participant NCtl as NotificationController
    participant DB as notifications table

    Note over B,S: startPolling() runs while auth.isAuthenticated,<br/>stopPolling() + reset() the moment it is false

    loop every 60 000 ms
        S->>AX: GET /api/notifications/unread-count
        AX->>NCtl: unreadCount()
        NCtl->>DB: user.unreadNotifications()->count()
        DB-->>NCtl: n
        NCtl-->>S: { count: n }
        alt n rose since the last poll
            S->>S: hasNewArrival = true
            S->>B: one bell nudge animation (never a loop)
        end
        S->>B: badge = n, or "9+" above nine
    end

    U->>B: clicks the bell
    B->>S: fetchNotifications({ per_page: 5 })
    S->>AX: GET /api/notifications?per_page=5
    AX->>NCtl: index()
    NCtl->>DB: user.notifications()->latest()->paginate()
    NCtl-->>S: { data: [NotificationResource], links, meta }
    S-->>B: the five most recent, newest first

    U->>B: clicks one
    B->>S: markRead(id)
    S->>S: optimistic: is_read = true, count - 1
    S->>AX: POST /api/notifications/{id}/read
    AX->>NCtl: markRead()
    NCtl->>DB: user.notifications()->findOrFail(id)
    Note over NCtl: scoped to the token holder's own relation —<br/>another user's id is a 404, never a silent success
    NCtl->>DB: markAsRead() → read_at = now()
    NCtl-->>S: 200 { data: NotificationResource }
    alt the request failed
        S->>S: roll back is_read, refetch the count
    end
    B->>B: navigate to notification.url if present

    U->>B: clicks "Mark all as read"
    B->>S: markAllRead()
    S->>S: optimistic: all read, count = 0
    S->>AX: POST /api/notifications/read-all
    NCtl->>DB: user.unreadNotifications->markAsRead()
    NCtl-->>S: { count: 0 }
```

## 3. Full history page

```mermaid
sequenceDiagram
    autonumber
    actor U as Signed-in user
    participant V as NotificationsView.vue
    participant S as useNotificationsStore
    participant NCtl as NotificationController

    U->>V: /notifications (customer) or /owner/notifications (owner)
    V->>S: fetchNotifications({ page: 1, per_page: 20 })
    S->>NCtl: GET /api/notifications?page=1&per_page=20
    NCtl-->>S: paginated NotificationResource collection
    V->>S: fetchUnreadCount()
    V->>U: renders history with an all / unread filter,<br/>per-row "mark as read" and "mark all as read"
```

One Vue view serves both portals — the owner route renders it inside
`OwnerLayout`, the customer route inside `CustomerLayout`, and the data is
identical because a notification belongs to a user, not to a portal.

## Mechanism summary

| Question | Answer, from the code |
|----------|----------------------|
| Realtime? | **No.** No WebSockets, no broadcasting, no Echo. `BROADCAST_CONNECTION=log`. |
| How does the UI stay current? | Polling `GET /api/notifications/unread-count` every 60 seconds while signed in. |
| Is the list polled too? | No — only the count. The list is fetched when the dropdown opens or the history page loads. |
| Where are they stored? | Laravel's `notifications` table, polymorphic on `notifiable`. |
| Read state | `read_at` — `NULL` while unread. `NotificationResource` exposes it as `is_read` + `read_at`. |
| Scoping | structural: every query starts from `$request->user()->notifications()`. |
| Guests | no bell is rendered at all (`notifications.spec.js`: "a guest sees no bell at all"). |
