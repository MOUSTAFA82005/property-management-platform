# Owner Dashboard API

**Source:** `backend/app/Http/Controllers/Owner/DashboardController.php`,
`frontend/src/services/dashboard.js`, `frontend/src/views/owner/Dashboard.vue`.

One endpoint returning every aggregate the owner dashboard renders. All figures
are scoped to the authenticated owner with `ownedBy()`, computed as grouped
database aggregates — the endpoint never loads whole tables to count them in
PHP. Only the two "recent" lists fetch rows, capped at five.

| Method | Endpoint | Auth | Role |
|--------|----------|------|------|
| GET | `/api/owner/dashboard` | Yes | Owner |

No query parameters.

## Success — `200 OK`

```json
{
  "data": {
    "properties":  { "total": 3, "published": 2, "unpublished": 1 },
    "buildings":   { "total": 5 },
    "units":       { "total": 12, "available": 6, "occupied": 3, "reserved": 3 },
    "customers":   { "total": 6 },
    "contracts":   { "total": 5, "active": 3, "expired": 1, "terminated": 1 },
    "purchase_requests": { "total": 8, "pending": 3, "approved": 3, "rejected": 1, "cancelled": 1 },
    "payments": {
      "total": 20,
      "paid_count": 12, "pending_count": 3, "overdue_count": 3, "cancelled_count": 2,
      "collected_amount": 164300, "pending_amount": 43500, "overdue_amount": 44000
    },
    "monthly_expected_rent": 43500,
    "recent_payments": [ { "id": 20, "amount": "13500.00", "status": "paid", "contract": { "user": { "name": "Omar Sabry" }, "unit": { "unit_number": "A-101" } } } ],
    "recent_purchase_requests": [ { "id": 8, "status": "pending", "customer": { "name": "Nour Khalil" }, "unit": { "unit_number": "M-503" } } ],
    "revenue_by_month": [
      { "month": "2026-03", "label": "Mar 2026", "total": 9800 },
      { "month": "2026-04", "label": "Apr 2026", "total": 14000 },
      { "month": "2026-05", "label": "May 2026", "total": 30000 },
      { "month": "2026-06", "label": "Jun 2026", "total": 43500 },
      { "month": "2026-07", "label": "Jul 2026", "total": 29500 },
      { "month": "2026-08", "label": "Aug 2026", "total": 13500 }
    ],
    "property_overview": [
      {
        "id": 1, "name": "Nile View Residences", "city": "Cairo",
        "status": "active", "is_published": true,
        "units": { "total": 5, "available": 2, "occupied": 2, "reserved": 1 }
      }
    ]
  }
}
```

## Field notes

| Field | Meaning |
|-------|---------|
| `customers.total` | customers with a contract on, or a purchase request against, one of this owner's units — not the platform's user count |
| `payments.collected_amount` | `SUM(amount)` of `paid` payments |
| `monthly_expected_rent` | `SUM(monthly_rent)` over **active** contracts |
| `recent_payments` | five most recent by `due_date`, as `PaymentResource` with `contract.user` and `contract.unit` |
| `recent_purchase_requests` | five most recent, as `PurchaseRequestResource` with `customer` and `unit.building.property` |
| `revenue_by_month` | collected revenue over the last six months, with **no gaps** — a month with no income is emitted as `0` |
| `property_overview` | one row per property with its unit breakdown, ordered by name, built from one grouped query |

`revenue_by_month` groups on the database side and picks its month expression
per driver: `DATE_FORMAT` on MySQL, `strftime` on SQLite (the test and E2E
databases).

## Errors

| Code | Cause |
|------|-------|
| `401` | no token |
| `403` | the caller is not an owner (`role:owner` middleware) |

Asserted by `tests/Feature/OwnerDashboardTest.php` and
`frontend/e2e/owner-dashboard.spec.js`.
