# Sequence — Owner creates a contract

**Derived from:** `backend/app/Http/Controllers/Owner/ContractController@store`,
`backend/app/Policies/ContractPolicy`, `backend/app/Notifications/ContractNotification`,
`frontend/src/views/owner/Contracts/ContractForm.vue`,
`frontend/src/views/owner/Contracts/Create.vue`,
`frontend/src/stores/contracts.js`, `frontend/src/services/contracts.js`.

```mermaid
sequenceDiagram
    autonumber
    actor O as Owner
    participant F as ContractForm.vue (mode=create)
    participant ST as useContractsStore
    participant AX as Axios client
    participant MW as auth:sanctum + role:owner
    participant CC as Owner ContractController@store
    participant P as ContractPolicy
    participant DB as Database
    participant N as ContractNotification
    participant CU as Customer (notified)

    O->>F: opens /owner/contracts/create
    F->>AX: GET /owner/units?per_page=100
    F->>AX: GET /owner/customers?per_page=100
    Note over F: the unit picker offers units that are<br/>available or reserved only
    O->>F: picks customer + unit, dates, rent, deposit
    F->>ST: createOwnerContract(payload)
    ST->>AX: POST /api/owner/contracts
    AX->>MW: Bearer token
    MW->>CC: authenticated owner
    CC->>P: Gate::authorize('create', Contract::class)
    P-->>CC: true (role = owner)
    CC->>CC: validate user_id, unit_id, start_date,<br/>end_date (after:start_date), monthly_rent,<br/>security_deposit?, status?, notes?
    CC->>DB: User::findOrFail(user_id)
    alt selected user is not a customer
        CC-->>AX: 422 "The selected customer is invalid."
    end
    CC->>DB: Unit::with('building.property')->findOrFail(unit_id)
    alt unit belongs to another owner
        CC-->>AX: 403 "You are not authorized to use this unit."
    end
    CC->>CC: unitIsLettableTo(unit, customer)
    Note over CC: available → yes.<br/>reserved → only if this customer has an<br/>approved purchase request on the unit.<br/>occupied → no.
    alt not lettable
        CC-->>AX: 422 "This unit is not available."
    else lettable
        CC->>DB: INSERT contracts
        CC->>DB: UPDATE units SET status = 'occupied'
        CC->>DB: eager load user, unit.building.property, payments
        CC->>N: ContractNotification::created(contract)
        N->>DB: INSERT notifications (notifiable = the customer)
        CC-->>AX: 201 ContractResource
        AX-->>ST: contract
        ST-->>F: success
        F->>O: redirect to /owner/contracts
        CU-->>CU: bell badge rises on the next poll (≤ 60s)
    end
```

## Notes on what the code actually does

- **No database transaction wraps `store()`.** The insert and the unit status
  update are two statements; the transaction in this controller exists only on
  `update()` and `destroy()`. Documented as implemented, not as idealised.
- The customer, not the owner, is notified — the customer cannot see the owner
  create the lease any other way
  (`NotificationTest::test_creating_a_contract_notifies_the_customer_not_the_owner`).
- `status` defaults to `active` at the database level when it is not sent.
- Validation is inline in the controller; there is no `StoreContractRequest`
  form-request class for contracts.
