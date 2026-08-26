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
      TenantController.php       # CRUD /tenants
      ContractController.php     # CRUD /contracts
      PaymentController.php      # CRUD /payments
  Models/
    User.php
    Property.php
    Building.php
    Unit.php
    Tenant.php
    Contract.php
    Payment.php
routes/
  api.php                        # All API route declarations
database/
  migrations/                    # Full schema — do not modify
```

## Authentication

Uses Laravel Sanctum (token-based).

- `POST /api/register` — public
- `POST /api/login` — public
- All other routes require `Authorization: Bearer <token>`

## Data Ownership

Every resource is scoped to the authenticated property manager:
`User → Properties → Buildings → Units → Contracts → Payments`

Controllers must verify ownership before any read/write/delete operation.

## Team Notes

- **Do not modify migrations.** They define the agreed database schema.
- Each controller method has a `// TODO` block describing what to implement.
- Models, routes, and infrastructure files are complete — implement only the controller and Vue view logic.
- The `auth:sanctum` middleware is already applied to all protected routes in `routes/api.php`.
