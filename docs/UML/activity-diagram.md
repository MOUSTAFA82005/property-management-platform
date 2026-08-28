# Activity Diagram — from listing to collected rent

**Derived from:** `Customer\PurchaseRequestController`,
`Owner\PurchaseRequestController`, `Owner\ContractController`,
`Owner\PaymentController`, `Owner\PropertyController` (publish/unpublish),
`Unit::scopePubliclyVisible()`.

This is the one business flow that crosses both roles and touches every
entity: **Property → Building → Unit → Purchase request → Contract → Payment.**

```mermaid
flowchart TD
    Start([Start]) --> P1["Owner creates a property"]
    P1 --> P2["Owner adds a building to it"]
    P2 --> P3["Owner adds units to the building<br/>status = available"]
    P3 --> P4{"Publish the property?"}
    P4 -- no --> P5["Property stays private<br/>invisible to the catalog"]
    P5 --> P4
    P4 -- yes --> P6["POST /owner/properties/{id}/publish<br/>is_published = true"]

    P6 --> C1["Customer browses GET /api/properties"]
    C1 --> C2["Opens a property, sees its available units"]
    C2 --> C3{"Unit status = available?"}
    C3 -- no --> C4["No request button is offered"]
    C4 --> C1
    C3 -- yes --> C5{"Already has a pending or<br/>approved request on this unit?"}
    C5 -- yes --> C6["422 You already have an open<br/>request for this unit"]
    C6 --> C1
    C5 -- no --> C7["POST /api/purchase-requests<br/>status = pending"]
    C7 --> N1[/"Owner notified:<br/>purchase_request.submitted"/]

    N1 --> O1["Owner reviews the request"]
    O1 --> O2{"Decision"}

    O2 -- reject --> O3["POST .../reject<br/>request = rejected<br/>unit unchanged"]
    O3 --> N2[/"Customer notified: rejected"/]
    N2 --> EndR([End])

    O2 -- "customer withdraws" --> O4["DELETE /api/purchase-requests/{id}<br/>request = cancelled"]
    O4 --> O5{"Was it approved?"}
    O5 -- yes --> O6["Unit released to available<br/>unless another approved request holds it"]
    O5 -- no --> O7["Unit unchanged"]
    O6 --> N3[/"Owner notified: cancelled"/]
    O7 --> N3
    N3 --> EndC([End])

    O2 -- approve --> O8{"Request still pending<br/>AND unit still available?"}
    O8 -- no --> O9["422 — cannot approve"]
    O9 --> O1
    O8 -- yes --> O10["Transaction:<br/>request = approved<br/>unit = reserved"]
    O10 --> N4[/"Customer notified: approved"/]

    N4 --> K1["Owner opens the contract form"]
    K1 --> K2["POST /api/owner/contracts"]
    K2 --> K3{"Unit lettable to this customer?"}
    K3 -- "occupied, or reserved for<br/>someone else" --> K4["422 This unit is not available"]
    K4 --> K1
    K3 -- "available, or reserved by<br/>THIS customer's approved request" --> K5["Contract created<br/>status = active"]
    K5 --> K6["Unit status = occupied"]
    K6 --> N5[/"Customer notified:<br/>contract.created"/]

    N5 --> M1["Owner records a payment<br/>POST /api/owner/payments"]
    M1 --> M2["Payment created<br/>status = pending / paid / overdue / cancelled"]
    M2 --> N6[/"Customer notified:<br/>payment.recorded"/]
    N6 --> M3{"Payment settled later?"}
    M3 -- yes --> M4["PUT /api/owner/payments/{id}<br/>status = paid, paid_date set"]
    M4 --> N7[/"Customer notified only because<br/>the status actually changed"/]
    N7 --> M5["Dashboard aggregates update:<br/>collected_amount, revenue_by_month"]
    M3 -- "no, still due" --> M5
    M5 --> Done([End])
```

## Decision points, and where each is enforced

| Decision | Enforced in | Failure response |
|----------|-------------|------------------|
| Is the property visible to the public? | `Customer\PropertyController::publicQuery()` — `is_published && status='active'` | `404` (indistinguishable from not existing) |
| Is the unit requestable? | `Customer\PurchaseRequestController@store` — `unit.status === 'available'` | `422 "That unit is not currently available."` |
| Duplicate open request? | same method — any `pending`/`approved` request by this customer on this unit | `422 "You already have an open request for this unit."` |
| Can the request be approved? | `Owner\PurchaseRequestController@approve` — status `pending` **and** unit `available` | `422` with the reason |
| Can a contract be written on this unit? | `ContractController::unitIsLettableTo()` | `422 "This unit is not available."` |
| Is the unit the owner's? | `ContractController@store/@update` | `403 "You are not authorized to use this unit."` |
| Can a payment be raised on this contract? | `PaymentController@store` → `Gate::authorize('update', $contract)` | `403` |

## Two shortcuts the flow allows

- A contract can be created **directly on an `available` unit**; the purchase
  request path is not mandatory.
- A purchase request can be **cancelled by the customer at any point while it
  is `pending` or `approved`**, which releases a reservation it created.

Both are in the code, so both are in the diagram.
