# PropSpace

**A real-estate property management platform for Egypt** — a public property
marketplace and a private owner workspace, sharing one Laravel API and one Vue
single-page application.

---

## What PropSpace is

PropSpace connects **property owners** with **customers** looking for a home or
a commercial space.

A visitor browses published properties and their units without an account. A
signed-in customer submits a purchase request for a specific unit, then tracks
their contracts and payment schedule from their own account pages. The owner
runs everything from the other side: their portfolio (properties → buildings →
units), the requests coming in, the contracts they write, and the money those
contracts generate.

### The problem it solves

Small and mid-size property owners run their portfolios on spreadsheets,
WhatsApp threads and paper leases. Nobody has one answer to "which units are
free right now?", "who is behind on rent?" or "what did we agree with this
tenant?". Customers, meanwhile, have no way to see what is actually available
or to follow up on an enquiry.

PropSpace puts both sides on the same records:

- **one inventory** — properties, buildings and units with a live status
  (`available`, `occupied`, `reserved`);
- **one enquiry pipeline** — purchase requests that an owner approves or
  rejects, reserving the unit when approved;
- **one lease record** — contracts that tie a unit to a customer, with the
  unit's occupancy kept in step automatically;
- **one payment ledger** — the schedule and its status, feeding the owner's
  revenue dashboard;
- **notifications on both sides**, so neither party has to keep checking.

### Who uses it

| | |
|---|---|
| **Visitors** | browse the public catalog, no account needed |
| **Customers** | request units, follow their requests, read their contracts and payments |
| **Owners** | manage the portfolio, decide on requests, write contracts, record payments |

---

## Roles

PropSpace has exactly **two roles**, both rows in the same `users` table,
separated by `users.role`.

### Customer

- Browse published properties and their available units
- View property and unit details
- Submit a purchase request for an available unit
- Track and cancel their own purchase requests
- View their own contracts
- View their own payment schedule
- Manage their profile, including changing their password
- Receive notifications, and mark them read

### Owner

- The **Owner Dashboard** — portfolio, occupancy, request and payment
  aggregates, plus a six-month revenue chart
- Manage **properties** (create, read, update, delete; publish / unpublish)
- Manage **buildings**
- Manage **units**
- See the **customers** connected to their properties, and each customer's
  records with them
- Review **purchase requests** and approve or reject them
- Manage **contracts** (create, read, update, delete)
- Manage **payments** (create, read, update, delete)
- Manage their profile
- Receive notifications, and mark them read

> **There is no Admin role**, and there is **no maintenance module**. The role
> enum accepts `owner` and `customer` only, and unit statuses are exactly
> `available`, `occupied` and `reserved` — there is no `sold`.
>
> There is also **no separate tenants table**. The tenant on a lease is a
> customer: `contracts.user_id` points at `users.id`.

---

## Features

Everything below is implemented and reachable in the running application.

### Authentication

- Registration (`POST /api/auth/register`) — self-service registration creates
  a **customer**; owner self-registration is off unless
  `ALLOW_OWNER_REGISTRATION=true`
- Login (`POST /api/auth/login`) issuing a **Laravel Sanctum** token
- Logout (`POST /api/auth/logout`) revoking **only** the current token, so
  other devices stay signed in
- Session rehydration on refresh via `GET /api/auth/me`
- Role-based access: `role:owner` middleware on the whole owner API, plus
  per-record policies
- Deactivated accounts (`users.status = 'inactive'`) are refused at login
- Egyptian mobile number validation on `phone`

### Public catalog (no account)

- Property listing with search (name / city / address) and city and type filters
- Property detail pages with unit inventory
- Unit detail pages
- Only `is_published = true` **and** `status = 'active'` properties are
  visible; anything else is a `404`, not a `403`

### Customer

- Everything in the public catalog
- Submit a purchase request on an available unit, with duplicate-request and
  availability guards
- Track request status (`pending` → `approved` / `rejected` / `cancelled`)
- Cancel a request — cancelling an approved one releases the unit's reservation
- View own contracts, with unit and property detail
- View own payments, with contract and unit detail
- Profile page: name, email, phone, password change (requires the current
  password)
- Notification bell and a full notification history page

### Owner

- **Dashboard**: property/building/unit/customer/contract/request/payment
  counts, occupancy split, collected / pending / overdue amounts, expected
  monthly rent, six-month revenue chart, per-property unit breakdown, recent
  payments and recent requests
- **Properties CRUD** + publish/unpublish, with search and status filters
- **Buildings CRUD**, filterable by property
- **Units CRUD**, filterable by property, building and status, with unit-number
  uniqueness enforced per building
- **Customers**: a directory of customers who actually deal with this owner —
  built from contracts and purchase requests, not from the user table
- **Purchase requests**: review, approve (reserves the unit) or reject
- **Contracts CRUD** (see below)
- **Payments CRUD**, with search across reference, method, customer and unit
- Profile page and account menu
- Notification bell and history page

### Contracts

The contract module is complete, and the destructive operations are guarded:

| Operation | Behaviour |
|-----------|-----------|
| **Create** | validates the customer is really a customer, the unit really belongs to the owner, and the unit is lettable; then marks the unit `occupied` and notifies the customer |
| **View** | owner sees contracts on their own units; the customer on the lease sees their own |
| **Edit** | all fields optional; moving the contract to a **different** unit re-runs the availability check, marks the new unit `occupied` and releases the old one — all inside a `DB::transaction` |
| **Delete** | refused with `409` while payments still reference it; otherwise deletes and releases the unit inside a transaction |
| **Ownership** | enforced by `ContractPolicy` walking unit → building → property → `owner_id` |
| **Occupancy** | a unit is only released when **no other active contract** still holds it |
| **Reserved units** | a reserved unit can be let only to the customer whose approved purchase request reserved it |

### Notifications

- Owner **and** customer notifications, in both directions
- Nine event types across contracts, payments and purchase requests
- Stored in Laravel's own polymorphic `notifications` table
- Unread / read state (`read_at`), unread count endpoint
- Notification **dropdown** in both portals (five most recent), with an unread
  badge (`9+` above nine) and a one-shot bell animation when the count rises
- **Mark one as read** and **mark all as read**
- A full **notification history page** at `/notifications` (customer) and
  `/owner/notifications` (owner), with an all / unread filter
- **Polling**: the unread count is fetched every 60 seconds while signed in.
  There is no WebSocket or broadcasting layer in this project.

Full detail: [`docs/API/notifications.md`](docs/API/notifications.md) and
[`docs/UML/notification-sequence.md`](docs/UML/notification-sequence.md).

---

## UI / UX

The frontend is a real-estate-focused SPA built on the project's own design
system (`src/style.css`, `src/style-skeleton.css`, `src/style-motion.css`):

- **Two surfaces on one token layer** — `owner-*` classes for the owner
  workspace, `sk-*` for the customer experience, so both read as one product
- **Property imagery**: curated architectural photographs from
  `public/property/`, chosen deterministically from a property's id and type,
  with a drawn SVG cover as a fallback if an image fails to load
- **Property cards** with cover art, price-from, unit counts and status
- **Owner dashboard** with stat cards, a Chart.js revenue line chart and a
  unit-status doughnut chart, plus a per-property unit breakdown
- **Owner sidebar** grouped by workflow (Overview / Property management /
  Management / Account), collapsible on small screens
- **Account menu**: a real keyboard-operable menu (arrow keys, Home/End,
  Escape, click-away, focus returned to the trigger) in both portals
- **Notification bell** with badge, dropdown panel and history page
- **Animations**: entrance transitions and the bell nudge, all `transform`/
  `opacity` only, with a `prefers-reduced-motion` guard that disables every
  decorative animation
- **Loading states**: shimmering `skel-line` placeholders while data is in
  flight, plus
  explicit empty states (`EmptyState.vue`) and inline error messages from the
  API
- **Responsive**: mobile-first breakpoints throughout; the owner account menu
  is covered by an E2E test at a narrow viewport
- **Accessibility**: `aria-expanded` / `aria-controls` / `role="dialog"` and
  `role="menuitem"` on the interactive menus, focus management on open/close

Bootstrap 5's **stylesheet** is loaded for its reset and base typography, and
Font Awesome for icons. Bootstrap's **JavaScript bundle is deliberately not
imported** — no `data-bs-*` component is used, and every menu and dialog is
plain Vue state. **Tailwind is not used anywhere in this project.**

---

## Tech stack

Verified from `backend/composer.json`, `frontend/package.json` and the project
configuration.

### Backend

| | |
|---|---|
| PHP | `^8.3` (CI runs 8.4) |
| Laravel | `laravel/framework ^13.17` |
| Auth | `laravel/sanctum ^4.0` — bearer tokens, no session/cookie auth |
| Console | `laravel/tinker ^3.0` |
| Database | **MySQL** in development; SQLite in the test suite and the E2E suite |
| Testing | `phpunit/phpunit ^12.5`, `nunomaduro/collision`, `fakerphp/faker` |
| Style | `laravel/pint` |

### Frontend

| | |
|---|---|
| Framework | Vue `^3.5` (Composition API, `<script setup>`) |
| Build | Vite `^8.2` with `@vitejs/plugin-vue ^6.0` |
| Routing | `vue-router ^5.2` |
| State | `pinia ^4.0` |
| HTTP | `axios ^1.19` — one shared client with auth interceptors |
| Charts | `chart.js ^4.5` |
| CSS | the project's own design system + `bootstrap ^5.3` **stylesheet only** |
| Icons | `@fortawesome/fontawesome-free ^7.3` |
| E2E | `@playwright/test 1.56.1` |

---

## Project structure

```text
property-management-platform/
├── backend/                        Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Auth/           register, login, logout, me
│   │   │   │   ├── Customer/       public catalog + customer account
│   │   │   │   ├── Owner/          the owner portal API
│   │   │   │   └── NotificationController.php
│   │   │   ├── Middleware/         EnsureUserHasRole (alias: role)
│   │   │   ├── Requests/           form requests (Auth / Customer / Owner)
│   │   │   └── Resources/          API resources (JSON shaping)
│   │   ├── Models/                 User, Property, Building, Unit,
│   │   │                           Contract, Payment, PurchaseRequest
│   │   ├── Notifications/          ActivityNotification + 3 subclasses
│   │   ├── Policies/               per-model authorization + ownership trait
│   │   └── Providers/
│   ├── bootstrap/app.php           middleware aliases, JSON exception rendering
│   ├── config/                     auth, sanctum, cors, database, …
│   ├── database/
│   │   ├── migrations/             the schema — source of truth for the ERD
│   │   ├── factories/              UserFactory
│   │   └── seeders/                DemoDataSeeder (one coherent scenario)
│   ├── routes/api.php              every API route
│   └── tests/                      Feature + Unit (PHPUnit)
│
├── frontend/                       Vue 3 SPA
│   ├── e2e/                        Playwright specs + helpers
│   ├── public/property/            property photography
│   ├── src/
│   │   ├── components/             auth, customer, owner, notifications, ui
│   │   ├── layouts/                CustomerLayout.vue, OwnerLayout.vue
│   │   ├── router/index.js         routes + auth/role guards
│   │   ├── services/               one Axios client + one module per resource
│   │   ├── stores/                 Pinia stores
│   │   ├── utils/                  formatting, property imagery
│   │   ├── views/                  auth/, customer/, owner/, NotificationsView
│   │   ├── style.css               design tokens and the owner surface
│   │   ├── style-skeleton.css      the customer surface
│   │   └── style-motion.css        motion system + reduced-motion guard
│   ├── playwright.config.js
│   └── vite.config.js
│
├── docs/
│   ├── API/                        endpoint reference
│   ├── ERD/                        database entity relationship diagram
│   └── UML/                        use case, class, sequence, activity, state
│
├── .github/workflows/ci.yml        backend + frontend + E2E jobs
└── README.md
```

### The folders that matter most

| Path | Why you would open it |
|------|----------------------|
| `backend/routes/api.php` | the whole API surface on one screen |
| `backend/app/Http/Controllers/Owner/` | everything the owner portal does |
| `backend/app/Policies/` | who may touch which record |
| `backend/database/migrations/` | the schema — the ERD is generated from here |
| `backend/database/seeders/DemoDataSeeder.php` | the demo scenario and its accounts |
| `frontend/src/services/` | every API call the SPA makes |
| `frontend/src/stores/` | client state, including the notification poller |
| `frontend/src/router/index.js` | the SPA's routes and guards |
| `frontend/e2e/` | how the app is expected to behave, end to end |

---

## Documentation

```text
docs/
├── API/     ← endpoint reference, request/response shapes, error format
├── ERD/     ← database entity relationship diagram
└── UML/     ← use case, class, sequence, activity and state diagrams
```

| | |
|---|---|
| **[API reference](docs/API/README.md)** | base URL, auth, formats, status codes, full endpoint index |
| — [Authentication](docs/API/authentication.md) | register, login, logout, me |
| — [Properties](docs/API/properties.md) | public catalog + owner CRUD, publish/unpublish |
| — [Buildings](docs/API/buildings.md) | owner building CRUD |
| — [Units](docs/API/units.md) | public browsing + owner unit CRUD |
| — [Purchase requests](docs/API/purchase-requests.md) | customer requests, owner approve/reject |
| — [Contracts](docs/API/contracts.md) | create, view, **edit**, **delete** |
| — [Payments](docs/API/payments.md) | owner CRUD + customer read |
| — [Customers](docs/API/customers.md) | the owner's related-customer directory |
| — [Profile](docs/API/profile.md) | shared profile read/update |
| — [Notifications](docs/API/notifications.md) | list, unread count, mark read |
| — [Dashboard](docs/API/dashboard.md) | owner aggregates |
| — [Authorization](docs/API/authorization.md) | how access is enforced server-side |
| **[ERD](docs/ERD/README.md)** | tables, keys, cardinality, constraints ([Mermaid source](docs/ERD/propspace-erd.mmd)) |
| **[UML](docs/UML/README.md)** | index of all diagrams |
| — [Use case](docs/UML/use-case.md) | actors and what each can do |
| — [Class diagram](docs/UML/class-diagram.md) | models, policies, notifications |
| — [Auth & authorization](docs/UML/authentication-authorization.md) | the request pipeline |
| — [Login sequence](docs/UML/login-sequence.md) | sign-in and session restore |
| — [Contract create](docs/UML/contract-create-sequence.md) · [edit](docs/UML/contract-edit-sequence.md) · [delete](docs/UML/contract-delete-sequence.md) | the contract flows |
| — [Notification sequence](docs/UML/notification-sequence.md) | raise → store → poll → read |
| — [Activity diagram](docs/UML/activity-diagram.md) | listing → request → contract → payment |
| — [State diagrams](docs/UML/state-diagram.md) | unit, contract, payment, request statuses |

Every diagram is Mermaid source inside Markdown — it renders on GitHub and
stays editable in any text editor.

---

## Getting started

### Requirements

| | |
|---|---|
| PHP | 8.3 or newer, with `pdo_mysql` (and `pdo_sqlite` to run the tests) |
| Composer | 2.x |
| Node.js | 20 or newer (CI uses 22) |
| MySQL | 8.x, or MariaDB |

### 1. Backend — Laravel API on :8000

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Create a database and point `.env` at it:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=propspace
DB_USERNAME=root
DB_PASSWORD=
```

Then build the schema and load the demo scenario:

```bash
php artisan migrate --seed        # first run
# or, to start over from scratch:
php artisan migrate:fresh --seed
php artisan serve                 # http://127.0.0.1:8000
```

Seeding is not strictly required, but without it there are no accounts to sign
in with — the seeder is the only source of demo data.

### 2. Frontend — Vue SPA on :5173

```bash
cd frontend
npm install
npm run dev                       # http://127.0.0.1:5173
```

### Running the two together

They are separate servers. Start the API first, then the SPA:

- the SPA calls `http://127.0.0.1:8000/api` by default;
- override it with `VITE_API_BASE_URL` in a `frontend/.env` file — Vite reads
  it at **build/dev-server start**, so restart `npm run dev` after changing it;
- CORS is Laravel's default configuration (`backend/config/cors.php`);
- authentication is a bearer token, so no `SANCTUM_STATEFUL_DOMAINS`
  configuration is needed.

### Demo credentials

`php artisan migrate:fresh --seed` creates one coherent scenario. **Every
seeded account uses the password `password`.**

| Role | Email | Who they are |
|------|-------|--------------|
| Owner | `owner@propspace.com` | Hassan Farouk — holds all three properties |
| Customer | `customer@propspace.com` | Omar Sabry — tenant, Nile View A-101 |
| Customer | `customer2@propspace.com` | Salma Adel — tenant, Nile View B-102 |
| Customer | `customer3@propspace.com` | Youssef Ibrahim — tenant, Marina M-501 |
| Customer | `customer4@propspace.com` | Dina Hafez — former tenant of B-102 |
| Customer | `customer5@propspace.com` | Karim Nassar — former tenant of M-502 |
| Customer | `customer6@propspace.com` | Nour Khalil — enquiring only, no contract |

Seeded volume: 3 properties, 5 buildings, 12 units across all three statuses,
5 contracts, 20 payments across all four statuses, 8 purchase requests.

---

## Environment variables

Copy `backend/.env.example` to `backend/.env` — it is the authoritative list.
Never commit a real `.env`; `backend/.gitignore` already excludes it.

### Backend (`backend/.env`)

| Variable | Example | Notes |
|----------|---------|-------|
| `APP_NAME` | `PropSpace` | |
| `APP_ENV` | `local` | |
| `APP_KEY` | *(generated)* | `php artisan key:generate` — required |
| `APP_DEBUG` | `true` | set `false` outside development; it controls whether errors carry a stack trace |
| `APP_URL` | `http://localhost:8000` | |
| `DB_CONNECTION` | `mysql` | the app runs on MySQL |
| `DB_HOST` / `DB_PORT` | `127.0.0.1` / `3306` | |
| `DB_DATABASE` | `propspace` | |
| `DB_USERNAME` / `DB_PASSWORD` | | your MySQL credentials |
| `SESSION_DRIVER` | `file` | the API is stateless — **no `sessions` table is migrated**, so keep this on a driver that needs no table |
| `CACHE_STORE` | `database` | uses the `cache` table |
| `QUEUE_CONNECTION` | `database` | no worker runs in development, which is why notifications are sent synchronously |
| `BROADCAST_CONNECTION` | `log` | there is no realtime layer |
| `MAIL_MAILER` | `log` | nothing sends real email |
| `ALLOW_OWNER_REGISTRATION` | `false` | when `true`, `POST /api/auth/register` also accepts `"role": "owner"`. Leave it `false` unless you need to create an owner from the public form |
| `BCRYPT_ROUNDS` | `12` | |

The `AWS_*`, `REDIS_*` and `MEMCACHED_*` entries in `.env.example` are Laravel
defaults; nothing in PropSpace uses them.

**The test suite ignores all of the above** — `phpunit.xml` pins
`DB_CONNECTION=sqlite` with an in-memory database, so `php artisan test` never
touches your MySQL data.

### Frontend (`frontend/.env`, optional)

| Variable | Default | Notes |
|----------|---------|-------|
| `VITE_API_BASE_URL` | `http://127.0.0.1:8000/api` | the fallback is hard-coded in `src/services/api.js`. Read at build time — restart the dev server after changing it |

`frontend/.env.e2e` is committed and pins the Playwright suite to
`http://127.0.0.1:8001/api`; leave it alone.

---

## API overview

Base URL: `http://127.0.0.1:8000/api` in local development.

Authentication is a **Sanctum bearer token**:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

56 routes across nine areas:

| Area | Routes | Access |
|------|--------|--------|
| **Authentication** | `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` | public / any |
| **Public catalog** | `GET /properties`, `GET /properties/{id}`, `GET /properties/{id}/units`, `GET /units/{id}` | public |
| **Customer** | `/purchase-requests` (list, create, show, cancel), `GET /contracts[/{id}]`, `GET /payments[/{id}]` | customer |
| **Profile** | `GET /profile`, `PUT /profile` | any |
| **Notifications** | `GET /notifications`, `GET /notifications/unread-count`, `POST /notifications/{id}/read`, `POST /notifications/read-all` | any |
| **Owner dashboard** | `GET /owner/dashboard` | owner |
| **Owner portfolio** | `/owner/properties` (+ `/publish`, `/unpublish`), `/owner/buildings`, `/owner/units` — full CRUD each | owner |
| **Owner lettings** | `/owner/purchase-requests` (+ `/approve`, `/reject`), `/owner/contracts` (full CRUD), `/owner/customers` | owner |
| **Owner payments** | `/owner/payments` — full CRUD | owner |

The complete reference, with request bodies, validation rules, response
examples and error codes, is in **[`docs/API/`](docs/API/README.md)**. To see
the live route table:

```bash
cd backend && php artisan route:list
```

---

## Authorization

Authorization is enforced **server-side, on every request**. Hiding a button in
the SPA is never what protects a record.

```text
Authentication
    ↓
Sanctum (auth:sanctum)          401 without a valid token
    ↓
Role middleware (role:owner)    403 for a customer on an owner route
    ↓
Policy authorization            403 on someone else's record
    ↓
Ownership verification          record.ownerId() == user.id, walked through
                                unit → building → property → owner_id
    ↓
Query scoping (ownedBy)         lists can only ever contain your own records
```

- `App\Http\Middleware\EnsureUserHasRole` (aliased `role`) gates the whole
  `/api/owner` group.
- Six policies (`Property`, `Building`, `Unit`, `Contract`, `Payment`,
  `PurchaseRequest`) share `ChecksPropertyOwnership`, where being an owner is
  never sufficient on its own — the record must also be yours.
- Every owner list query applies `Model::ownedBy($user)`, and customer
  endpoints read from the token holder's own relations.
- Values that decide ownership (`owner_id`, `customer_id`, `role`) are taken
  from the token, never from the request body.

Detail: [`docs/API/authorization.md`](docs/API/authorization.md) and
[`docs/UML/authentication-authorization.md`](docs/UML/authentication-authorization.md).

---

## Notifications

Notifications are stored in Laravel's own polymorphic `notifications` table.
The `User` model already used `Notifiable`, so the framework's `database`
channel was one migration away and no bespoke store was needed.

| | |
|---|---|
| **Generated by** | controller actions, after the domain change is written |
| **Stored in** | `notifications` — UUID id, `notifiable_type` + `notifiable_id`, JSON `data`, `read_at` |
| **Payload** | always `{ type, title, message, url }`, so one component renders any notification |
| **Links** | resolved per role — the same event links to `/owner/...` for an owner and `/...` for a customer |
| **Retrieved via** | `GET /api/notifications` (paginated, newest first, optional `?unread=1`) |
| **Unread count** | `GET /api/notifications/unread-count` → `{ "count": n }` |
| **Read state** | `read_at` — `NULL` while unread; exposed as `is_read` |
| **Mark read** | `POST /api/notifications/{id}/read` (one) · `POST /api/notifications/read-all` (all) |
| **Scope** | every query starts from the token holder's own relation; another user's id is a `404` |
| **Delivery** | synchronous `database` channel only — not queued, no mail, no broadcast |
| **Freshness** | the SPA polls the unread count every 60 s while signed in; the list is fetched when the dropdown opens |

Both roles are notified, in both directions:

| Event | Who is told |
|-------|-------------|
| contract created / updated / deleted | the customer on the lease |
| payment recorded / status changed | the customer on the contract |
| purchase request submitted / cancelled | the **owner** of the unit |
| purchase request approved / rejected | the customer who raised it |

---

## Testing

### Backend — PHPUnit

```bash
cd backend
php artisan test
```

**156 tests, 637 assertions.** They run against in-memory SQLite (pinned in
`phpunit.xml`), so your MySQL database is never touched.

| Suite | Tests | Covers |
|-------|------:|--------|
| `AuthenticationTest` | 27 | registration rules, role whitelisting, login, deactivated accounts, token revocation, the role gate |
| `OwnerContractTest` | 24 | contract create/edit/delete, unit occupancy, ownership, payment-dependency refusal |
| `DemoSeedTest` | 21 | the seeded scenario and the schema invariants behind the ERD |
| `PurchaseRequestWorkflowTest` | 17 | submit, approve, reject, cancel, reservation release |
| `OwnerIsolationTest` | 13 | an owner cannot reach another owner's records |
| `CustomerIsolationTest` | 9 | customer isolation and profile-escalation attempts |
| `NotificationTest` | 9 | who is notified, read state, unread count, scoping |
| `PublicCatalogTest` | 9 | unpublished properties are invisible |
| `OwnerDashboardTest` | 6 | scoped aggregates |
| `PropertyAndUnitTest` | 6 | property/unit CRUD rules |
| `OwnerCustomerListTest` | 4 | the customer directory is built from real relationships |
| `CustomerPaymentAccessTest`, `CustomerRecordDetailTest`, `OwnerListOrderingTest` | 9 | payment visibility, eager-load safety, deterministic ordering |

### Frontend build

```bash
cd frontend
npm run build
```

### End-to-end — Playwright

```bash
cd frontend
npm install
npx playwright install chromium     # first run only
npm run e2e
```

**14 spec files** covering authentication, the public catalog, customer
journeys, owner CRUD for every resource, isolation, notifications, the account
menus and profiles.

`npm run e2e` builds the SPA in `e2e` mode and Playwright starts both servers
itself:

- the API on **:8001** using `backend/.env.e2e`
- the SPA on **:4173**

**Your normal database is never touched** — `backend/.env.e2e` points at a
throwaway `backend/database/e2e.sqlite`, rebuilt from `DemoDataSeeder` before
every spec file. The unusual ports avoid colliding with a running
`php artisan serve` / `npm run dev`.

```bash
npm run e2e:ui                              # interactive runner
npm run e2e:report                          # open the last HTML report
npx playwright test e2e/isolation.spec.js   # a single file
```

---

## CI

`.github/workflows/ci.yml` runs on every push and pull request:

| Job | What it does |
|-----|--------------|
| `backend` | `composer install`, `migrate:fresh --seed`, `php artisan test` |
| `frontend` | `npm ci`, `npm run build` |
| `e2e` | installs both stacks + Chromium, then `npm run e2e` |

CI uses SQLite rather than MySQL: nothing under test depends on
MySQL-specific behaviour, and the one query with a vendor-specific branch (the
dashboard's monthly revenue aggregate) has a SQLite path. Composer, npm and
Playwright browsers are cached; on an E2E failure the HTML report and traces
are uploaded as artifacts.

---

## Conventions

- **Two roles only**: `owner` and `customer`. No admin, no `property_manager`,
  no `tenant` role — and no maintenance module.
- **Unit statuses**: `available`, `occupied`, `reserved`. There is no `sold`.
- **"Customer", not "tenant"**, everywhere — the tables, the API and the UI all
  use the same word.
- **No mock business data in `src/`**: every screen reads from the API. If a
  screen needs data, it needs an endpoint.
- **Ownership is enforced in the API**, by policies and query scoping — never
  by what the SPA chooses to render.
