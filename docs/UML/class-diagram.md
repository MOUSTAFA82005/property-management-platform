# Class Diagram — backend domain model

**Derived from:** `backend/app/Models/*.php`, `backend/app/Policies/*.php`,
`backend/app/Notifications/*.php`, `backend/app/Http/Middleware/EnsureUserHasRole.php`.

Only the methods that carry domain meaning are shown. Eloquent's inherited API
(`save`, `find`, `where`, …) is omitted deliberately.

## Domain models

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string phone
        +string role
        +string status
        +properties() HasMany
        +contracts() HasMany
        +purchaseRequests() HasMany
        +payments() HasManyThrough
        +notifications() MorphMany
        +unreadNotifications() MorphMany
        +createToken(name) NewAccessToken
    }

    class Property {
        +int id
        +int owner_id
        +string name
        +string address
        +string city
        +string property_type
        +string status
        +bool is_published
        +owner() BelongsTo
        +buildings() HasMany
        +units() HasManyThrough
        +scopeOwnedBy(query, owner)
        +ownerId() int
    }

    class Building {
        +int id
        +int property_id
        +string name
        +int floors_count
        +property() BelongsTo
        +units() HasMany
        +scopeOwnedBy(query, owner)
        +ownerId() int
    }

    class Unit {
        +int id
        +int building_id
        +string unit_number
        +int floor
        +string unit_type
        +decimal area
        +int bedrooms
        +int bathrooms
        +decimal monthly_rent
        +string status
        +building() BelongsTo
        +property() HasOneThrough
        +contracts() HasMany
        +purchaseRequests() HasMany
        +payments() HasManyThrough
        +scopeOwnedBy(query, owner)
        +scopePubliclyVisible(query)
        +ownerId() int
    }

    class Contract {
        +int id
        +int user_id
        +int unit_id
        +date start_date
        +date end_date
        +decimal monthly_rent
        +decimal security_deposit
        +string status
        +user() BelongsTo
        +unit() BelongsTo
        +payments() HasMany
        +scopeOwnedBy(query, owner)
        +ownerId() int
    }

    class Payment {
        +int id
        +int contract_id
        +decimal amount
        +date due_date
        +date paid_date
        +string payment_method
        +string status
        +string reference
        +contract() BelongsTo
        +scopeOwnedBy(query, owner)
        +ownerId() int
        +customerId() int
    }

    class PurchaseRequest {
        +int id
        +int customer_id
        +int unit_id
        +string status
        +string notes
        +customer() BelongsTo
        +unit() BelongsTo
        +scopeOwnedBy(query, owner)
        +ownerId() int
    }

    class DatabaseNotification {
        +uuid id
        +string type
        +string notifiable_type
        +int notifiable_id
        +array data
        +datetime read_at
        +markAsRead()
    }

    User "1" --> "0..*" Property : owner_id
    User "1" --> "0..*" Contract : user_id
    User "1" --> "0..*" PurchaseRequest : customer_id
    User "1" --> "0..*" DatabaseNotification : notifiable
    Property "1" --> "0..*" Building : property_id
    Building "1" --> "0..*" Unit : building_id
    Unit "1" --> "0..*" Contract : unit_id
    Unit "1" --> "0..*" PurchaseRequest : unit_id
    Contract "1" --> "0..*" Payment : contract_id
    Property "1" ..> "0..*" Unit : hasManyThrough
    Unit "1" ..> "0..*" Payment : hasManyThrough
```

### Cardinality, read from the migrations

| Association | Cardinality | Note |
|-------------|-------------|------|
| `User (owner) → Property` | `1 .. 0..*` | `properties.owner_id` NOT NULL |
| `Property → Building` | `1 .. 0..*` | cascade delete |
| `Building → Unit` | `1 .. 0..*` | cascade delete |
| `Unit → Contract` | `1 .. 0..*` | a unit accumulates leases over time; at most one is `active` |
| `User (customer) → Contract` | `1 .. 0..*` | `contracts.user_id` NOT NULL |
| `Contract → Payment` | `1 .. 0..*` | restrict on delete |
| `Unit → PurchaseRequest` | `1 .. 0..*` | restrict on delete |
| `User (customer) → PurchaseRequest` | `1 .. 0..*` | restrict on delete |
| `User → DatabaseNotification` | `1 .. 0..*` | polymorphic, no FK |

There is **no `Tenant` class**. The tenant on a lease is a `User` with
`role = 'customer'`, reached via `Contract::user()`.

## Policies

```mermaid
classDiagram
    class ChecksPropertyOwnership {
        <<trait>>
        #isOwner(user) bool
        #isCustomer(user) bool
        #owns(user, record) bool
    }

    class PropertyPolicy {
        +viewAny(user)
        +view(user, property)
        +create(user)
        +update(user, property)
        +delete(user, property)
        +publish(user, property)
        +unpublish(user, property)
    }
    class BuildingPolicy {
        +viewAny(user)
        +view(user, building)
        +create(user)
        +update(user, building)
        +delete(user, building)
    }
    class UnitPolicy {
        +viewAny(user)
        +view(user, unit)
        +create(user)
        +update(user, unit)
        +delete(user, unit)
    }
    class ContractPolicy {
        +viewAny(user)
        +view(user, contract)
        +create(user)
        +update(user, contract)
        +delete(user, contract)
    }
    class PaymentPolicy {
        +viewAny(user)
        +view(user, payment)
        +create(user)
        +update(user, payment)
        +delete(user, payment)
    }
    class PurchaseRequestPolicy {
        +viewAny(user)
        +view(user, request)
        +create(user)
        +delete(user, request)
        +approve(user, request)
        +reject(user, request)
    }

    ChecksPropertyOwnership <|.. PropertyPolicy
    ChecksPropertyOwnership <|.. BuildingPolicy
    ChecksPropertyOwnership <|.. UnitPolicy
    ChecksPropertyOwnership <|.. ContractPolicy
    ChecksPropertyOwnership <|.. PaymentPolicy
    ChecksPropertyOwnership <|.. PurchaseRequestPolicy
```

`owns()` is the single ownership predicate: it returns true only when the user
is an owner **and** `record->ownerId()` equals their id. Being an owner is
never sufficient on its own.

Policies are resolved by Laravel's conventional `App\Policies\{Model}Policy`
auto-discovery — there is no policy registration in `AppServiceProvider`.

## Notifications

```mermaid
classDiagram
    class Notification {
        <<Laravel>>
    }

    class ActivityNotification {
        <<abstract>>
        +via(notifiable) array
        +toDatabase(notifiable) array
        #payload(notifiable)* array
        #routeFor(notifiable, ownerPath, customerPath) string
    }

    class ContractNotification {
        +CREATED = "contract.created"
        +UPDATED = "contract.updated"
        +DELETED = "contract.deleted"
        +created(contract)$ self
        +updated(contract)$ self
        +deleted(contractId, unitNumber)$ self
    }

    class PaymentNotification {
        +RECORDED = "payment.recorded"
        +UPDATED = "payment.updated"
        +recorded(payment)$ self
        +updated(payment)$ self
    }

    class PurchaseRequestNotification {
        +SUBMITTED = "purchase_request.submitted"
        +CANCELLED = "purchase_request.cancelled"
        +APPROVED = "purchase_request.approved"
        +REJECTED = "purchase_request.rejected"
        +submitted(request, customerName)$ self
        +cancelled(request, customerName)$ self
        +approved(request)$ self
        +rejected(request)$ self
    }

    Notification <|-- ActivityNotification
    ActivityNotification <|-- ContractNotification
    ActivityNotification <|-- PaymentNotification
    ActivityNotification <|-- PurchaseRequestNotification
```

`via()` returns `['database']` for every notification — there is no mail
channel and no broadcasting configured. They are deliberately **not queued**:
the app runs the `database` queue driver with no worker in development, so a
queued notification would never arrive.

Every payload has the same four keys, which is what lets one Vue component
render any notification: `type`, `title`, `message`, `url`.

## HTTP layer

```mermaid
classDiagram
    class EnsureUserHasRole {
        <<middleware, alias: role>>
        +handle(request, next, ...roles) Response
    }

    class OwnerControllers {
        <<namespace App/Http/Controllers/Owner>>
        DashboardController
        PropertyController
        BuildingController
        UnitController
        ContractController
        PaymentController
        PurchaseRequestController
        CustomerController
    }

    class CustomerControllers {
        <<namespace App/Http/Controllers/Customer>>
        PropertyController
        UnitController
        PurchaseRequestController
        ContractController
        PaymentController
        ProfileController
    }

    class AuthControllers {
        <<namespace App/Http/Controllers/Auth>>
        RegisterController
        LoginController
        LogoutController
        MeController
    }

    class NotificationController {
        +index(request)
        +unreadCount(request)
        +markRead(request, notification)
        +markAllRead(request)
    }

    EnsureUserHasRole ..> OwnerControllers : guards /api/owner/*
```

API Resources shape every response:
`UserResource`, `PropertyResource`, `BuildingResource`, `UnitResource`,
`ContractResource`, `PaymentResource`, `PurchaseRequestResource`,
`NotificationResource`.
