# Sequence — Owner deletes a contract

**Derived from:** `backend/app/Http/Controllers/Owner/ContractController@destroy`,
`backend/app/Policies/ContractPolicy::delete()`,
`backend/app/Notifications/ContractNotification::deleted()`,
`frontend/src/views/owner/Contracts/Index.vue`,
`frontend/src/views/owner/Contracts/Show.vue`,
`frontend/src/stores/contracts.js`.

```mermaid
sequenceDiagram
    autonumber
    actor O as Owner
    participant V as Contracts Index / Show
    participant ST as useContractsStore
    participant AX as Axios client
    participant MW as auth:sanctum + role:owner
    participant CC as Owner ContractController@destroy
    participant P as ContractPolicy
    participant TX as DB::transaction
    participant DB as Database
    participant N as ContractNotification
    participant CU as Customer (notified)

    O->>V: clicks Delete
    V->>O: in-page confirmation
    alt cancelled
        V-->>O: nothing happens
    else confirmed
        V->>ST: deleteOwnerContract(id)
        ST->>AX: DELETE /api/owner/contracts/{id}
        AX->>MW: Bearer token
        MW->>CC: authenticated owner
        CC->>P: Gate::authorize('delete', contract)
        P->>DB: ownerId() via unit → building → property
        alt not this owner's contract
            P-->>CC: false → 403
        end

        CC->>DB: contract.payments()->exists()
        alt payments exist
            CC-->>AX: 409 "This contract still has payments recorded<br/>against it. Remove those payments first."
            AX-->>V: message shown to the owner
        else no payments
            CC->>CC: capture customer, contract id, unit number<br/>before the row disappears
            CC->>TX: begin transaction
            TX->>DB: DELETE FROM contracts WHERE id = ?
            TX->>DB: releaseUnit(unit, excluding this contract)
            Note over TX: unit returns to 'available' only if no other<br/>ACTIVE contract still holds it
            TX-->>CC: commit
            CC->>N: ContractNotification::deleted(contractId, unitNumber)
            N->>DB: INSERT notifications (notifiable = the customer)
            CC-->>AX: 204 No Content
            AX-->>ST: success
            ST-->>V: row removed from the list
            CU-->>CU: "Contract removed" in their bell,<br/>linking to the list, not to a dead record
        end
    end
```

## Why 409 rather than a cascade

`payments.contract_id` is `RESTRICT` on delete: collection history has to
outlive the contract. Deleting through the database would fail with a foreign
key error, so the controller checks first and answers `409 Conflict` with a
message the UI can show directly. The same pattern is used by
`PropertyController@destroy`, `BuildingController@destroy` and
`UnitController@destroy`.

**A customer is never able to reach this endpoint** — `role:owner` stops them
at the middleware, and `ContractPolicy::delete()` would refuse anyway
(`owner-contracts.spec.js`: "a customer cannot edit or delete a contract
through the owner API").

## Deletion effects

| Effect | Happens? |
|--------|----------|
| Contract row deleted | yes (hard delete — no soft deletes anywhere in this project) |
| Unit released to `available` | only when no other `active` contract references it |
| Payments deleted | never — their existence blocks the delete with 409 |
| Purchase requests touched | no |
| Customer notified | yes, after the transaction commits |
