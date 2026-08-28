# State Diagrams

**Derived from:** the enum definitions in the migrations, and every write that
changes a status: `Owner\ContractController` (`releaseUnit`, `unitIsLettableTo`),
`Owner\PurchaseRequestController` (`approve`, `reject`),
`Customer\PurchaseRequestController` (`store`, `destroy`),
`Owner\PaymentController`, `Owner\PropertyController` (`publish`, `unpublish`).

Only statuses that exist in the schema are represented. There is no `sold`
unit status and no maintenance state anywhere in this system.

## Unit

`units.status` — `available` | `occupied` | `reserved`, default `available`.

```mermaid
stateDiagram-v2
    [*] --> available : unit created (default)

    available --> reserved : owner approves a purchase request<br/>on this unit
    reserved --> available : customer cancels their approved request<br/>(and no other approved request holds it)
    reserved --> occupied : owner writes a contract for the customer<br/>whose approved request reserved it

    available --> occupied : owner writes a contract directly
    occupied --> available : contract deleted, or moved to another unit —<br/>and no other ACTIVE contract still holds it

    available --> occupied : owner sets the status manually (PUT /owner/units/{id})
    occupied --> available : owner sets the status manually
    reserved --> occupied : owner sets the status manually
```

The manual transitions exist because `UpdateUnitRequest` accepts
`status in [available, occupied, reserved]`; the automatic ones are the ones
the contract and purchase-request flows perform.

`releaseUnit()` never frees a unit that another **active** contract still
references — a unit accumulates contracts over time, so "this contract is
gone" is not on its own a reason to advertise the unit.

## Contract

`contracts.status` — `active` | `expired` | `terminated`, default `active`.

```mermaid
stateDiagram-v2
    [*] --> active : created (default; status is optional on create)
    active --> expired : owner sets status on PUT /owner/contracts/{id}
    active --> terminated : owner sets status on PUT /owner/contracts/{id}
    expired --> active : owner sets status back
    terminated --> active : owner sets status back
    active --> [*] : DELETE, only when no payments exist (else 409)
    expired --> [*] : DELETE, only when no payments exist
    terminated --> [*] : DELETE, only when no payments exist
```

There is **no automatic expiry job**: nothing in the codebase transitions a
contract to `expired` when `end_date` passes. `routes/console.php` schedules
nothing. Status is whatever the owner sets.

Only `active` contracts hold a unit occupied — `releaseUnit()` counts
`status = 'active'` only.

## Payment

`payments.status` — `pending` | `paid` | `overdue` | `cancelled`, default `pending`.

```mermaid
stateDiagram-v2
    [*] --> pending : created (status is REQUIRED on create,<br/>so any of the four can be the initial state)
    [*] --> paid
    [*] --> overdue
    [*] --> cancelled

    pending --> paid : owner records settlement (paid_date set)
    pending --> overdue : owner marks it late
    pending --> cancelled : owner voids it
    overdue --> paid : late settlement
    overdue --> cancelled : written off
    paid --> pending : correction
    cancelled --> pending : reinstated
    paid --> [*] : DELETE /owner/payments/{id}
    pending --> [*] : DELETE
    overdue --> [*] : DELETE
    cancelled --> [*] : DELETE
```

Every transition is an owner edit — there is **no scheduled job that marks
payments overdue**. A status change (and only a status change) notifies the
customer; editing the notes does not.

Payments are the only records that can be deleted freely; they are what blocks
deleting a contract.

## Purchase request

`purchase_requests.status` — `pending` | `approved` | `rejected` | `cancelled`,
default `pending`.

```mermaid
stateDiagram-v2
    [*] --> pending : customer submits (POST /api/purchase-requests)
    pending --> approved : owner approves — only if the unit is still available;<br/>the unit becomes reserved
    pending --> rejected : owner rejects — the unit is untouched
    pending --> cancelled : customer withdraws
    approved --> cancelled : customer withdraws; the reservation is released<br/>if no other approved request holds the unit
    approved --> [*] : consumed by a contract<br/>(the row stays approved; the unit becomes occupied)
    rejected --> [*]
    cancelled --> [*]
```

`approved` and `rejected` are terminal for the owner: approving or rejecting a
request that is not `pending` returns `422 "This request has already been
{status} and cannot be approved."`. Rows are never deleted — `destroy()` is a
transition to `cancelled`.

## Property publication

`properties.status` (`active`/`inactive`) and `properties.is_published`
(boolean) together decide public visibility.

```mermaid
stateDiagram-v2
    [*] --> Unpublished : created (is_published = false unless sent)
    Unpublished --> Published : POST /owner/properties/{id}/publish
    Published --> Unpublished : POST /owner/properties/{id}/unpublish
    Published --> Hidden : status set to inactive
    Hidden --> Published : status set back to active
    note right of Published
        Visible in the public catalog only when
        is_published = true AND status = 'active'.
        Anything else answers 404.
    end note
```
