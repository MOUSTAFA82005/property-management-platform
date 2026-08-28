# Property Management Platform — Backend

Laravel 13 REST API for the Property Management Platform.

## Requirements

- PHP 8.4+
- Composer
- MySQL 8+

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:

```
DB_DATABASE=property_management
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Seed a test property manager account (`test@example.com` / `password`):

```bash
php artisan db:seed
```

Start the development server:

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

---

## Architecture

```
app/
  Http/
    Controllers/
      AuthController.php         # POST /register, /login, /logout | GET /me
      DashboardController.php    # GET /dashboard
      PropertyController.php     # CRUD /properties
      BuildingController.php     # CRUD /buildings
      UnitController.php         # CRUD /units
      CustomerController.php     # List / show customers (users.role = customer)
      ContractController.php     # CRUD /contracts
      PaymentController.php      # CRUD /payments
  Models/
    User.php
    Property.php
    Building.php
    Unit.php
    Contract.php
    Payment.php
routes/
  api.php                        # All API route declarations
database/
  migrations/                    # Full schema — do not modify
```

## Authentication

Uses Laravel Sanctum (token-based).

- `POST /api/auth/register` — public
- `POST /api/auth/login` — public
- The property catalog (`GET /api/properties`, `/api/properties/{property}`,
  `/api/properties/{property}/units`, `/api/units/{unit}`) — public
- All other routes require `Authorization: Bearer <token>`

## Data Ownership

`User (Customer) → Contracts → Payments`

Customer payment access is scoped through the authenticated user's own contracts. Owners manage properties and related resources via the owner API.

Controllers must verify ownership before any read/write/delete operation.

## Team Notes

- **Do not modify migrations.** They define the agreed database schema.
- Every controller method is implemented; there are no stubs left. `php artisan route:list --path=api`
  should always show 52 routes, all backed by real controllers.
- `auth:sanctum` protects every route except the auth entry points and the public property catalog
  (`GET /api/properties`, `/api/properties/{property}`, `/api/properties/{property}/units`,
  `/api/units/{unit}`), which anonymous visitors must be able to reach.
- Everything under `/api/owner` additionally passes through `role:owner`
  (`App\Http\Middleware\EnsureUserHasRole`). Role separation alone is not enough: policies also
  check that the specific record belongs to the authenticated owner, via the `ownedBy()` scope and
  `ownerId()` resolver defined on each model.
- Ownership and isolation are covered by the PHPUnit suite and by the Playwright specs in
  `frontend/e2e/isolation.spec.js`. Add a regression test alongside any change to these paths.
