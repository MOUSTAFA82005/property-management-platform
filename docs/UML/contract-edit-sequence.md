# Sequence — Owner edits a contract

**Derived from:** `backend/app/Http/Controllers/Owner/ContractController@update`,
`backend/app/Policies/ContractPolicy::update()`,
`backend/app/Notifications/ContractNotification::updated()`,
`frontend/src/views/owner/Contracts/Edit.vue`,
`frontend/src/views/owner/Contracts/ContractForm.vue` (`mode="edit"`),
`frontend/src/stores/contracts.js`.

```mermaid
sequenceDiagram
    autonumber
    actor O as Owner
    participant F as ContractForm.vue (mode=edit)
    participant ST as useContractsStore
    participant AX as Axios client
    participant MW as auth:sanctum + role:owner
    participant CC as Owner ContractController@update
    participant P as ContractPolicy
    participant TX as DB::transaction
    participant DB as Database
    participant N as ContractNotification
    participant CU as Customer (notified)

    O->>F: opens /owner/contracts/{id}/edit
    F->>AX: GET /owner/units + /owner/customers
    F->>AX: GET /api/owner/contracts/{id}
    AX-->>F: contract
    F->>F: prefill the form — dates sliced to YYYY-MM-DD
    Note over F: the currently let unit is kept in the picker<br/>even though it is 'occupied', or the field<br/>would render blank

    O->>F: changes fields, submits
    F->>ST: updateOwnerContract(id, payload)
    ST->>AX: PUT /api/owner/contracts/{id}
    AX->>MW: Bearer token
    MW->>CC: authenticated owner
    CC->>P: Gate::authorize('update', contract)
    P->>DB: contract.ownerId() via unit → building → property
    alt not this owner's contract
        P-->>CC: false → 403
    end
    CC->>CC: validate (all fields optional/"sometimes",<br/>end_date after:start_date)

    opt user_id supplied
        CC->>DB: User::findOrFail(user_id)
        alt role != customer
            CC-->>AX: 422 "The selected customer is invalid."
        end
    end

    opt unit_id supplied
        CC->>DB: Unit::with('building.property')->findOrFail(unit_id)
        alt unit belongs to another owner
            CC-->>AX: 403 "You are not authorized to use this unit."
        end
    end

    alt moving to a different unit
        CC->>CC: unitIsLettableTo(targetUnit, customer ?? contract.user)
        alt target not available and not reserved for this customer
            CC-->>AX: 422 "This unit is not available."
        end
    end

    CC->>TX: begin transaction
    TX->>DB: UPDATE contracts SET ...validated
    alt the unit changed
        TX->>DB: UPDATE units SET status='occupied' WHERE id = new unit
        TX->>DB: releaseUnit(previous unit, excluding this contract)
        Note over TX: the old unit becomes 'available' only if no<br/>OTHER active contract still holds it
    end
    TX-->>CC: commit
    CC->>DB: reload user, unit.building.property, payments
    CC->>N: ContractNotification::updated(contract)
    N->>DB: INSERT notifications (notifiable = contract.user)
    CC-->>AX: 200 ContractResource
    AX-->>ST: updated contract
    ST-->>F: success → redirect to the contract list
    CU-->>CU: "Contract updated" appears in their bell
```

## Behaviour this diagram is asserting

| Step | Implementation detail |
|------|----------------------|
| Partial update | every rule is `sometimes`; only the keys sent are written |
| Staying on the same unit | always allowed — no availability check is run |
| Moving to a new unit | must clear the same bar as creating a contract, otherwise an edit would be a way to let an already-taken unit |
| Reserved target unit | allowed only when the contract's customer holds an **approved** purchase request on it |
| Old unit release | `releaseUnit()` frees it only if no other `active` contract references it — a unit accumulates contracts over time |
| Transaction | `DB::transaction` wraps the contract update **and** both unit status writes |
| Notification | sent after the transaction commits, to the customer on the contract |
| `PATCH` | the route accepts `PUT` and `PATCH`; the SPA uses `PUT` |

Covered by `tests/Feature/OwnerContractTest.php` and
`frontend/e2e/owner-contracts.spec.js`
("an edit is saved and shows up in the list", "the edit form surfaces API
validation errors").
