# 🏢 PropSpace – Property Management Platform

> **AI / ChatGPT Context Guide & Project Overview**
> *This document provides a complete technical, architectural, and business overview of the PropSpace Property Management Platform to give AI assistants (like ChatGPT) and development team members full context of the project.*

---

## 🎯 Project Overview & Business Model

PropSpace is a web-based Property Management Platform designed to connect property owners with customers across Egypt.

### 👥 User Roles & Permissions
There are **STRICTLY TWO ROLES** in this system. **THERE IS NO ADMIN ROLE.**

1. **OWNER (`/owner/...`)**
   - Has full administrative and portfolio management permissions.
   - Manages properties, units, customers, purchase requests, contracts, and payments via the **Owner Dashboard**.

2. **CUSTOMER (`/` and `/profile`, `/properties`, etc.)**
   - Public user who browses properties and units on the website.
   - Submits purchase requests for specific property units.
   - Accesses personal account dashboard (`/profile`) to manage personal information, track purchase request statuses, view active contracts, and monitor payment schedules.

---

## 🛠️ Technology Stack

- **Frontend:** Vue 3 (Composition API with `<script setup>`), Vite, Pinia, Vue Router 4, Bootstrap 5, Vanilla CSS.
- **Backend:** Laravel 11 (PHP 8.3+), RESTful API, Laravel Sanctum token authentication.
- **Database:** MySQL.

---

## 📂 Project Architecture & Directory Structure

```text
property-management-platform/
├── backend/                  # Laravel 11 API Backend Application
│   ├── app/                  # Models, Controllers, Middleware
│   ├── database/             # Migrations, Seeders, Factories
│   └── routes/api.php        # API Endpoints
│
├── frontend/                 # Vue.js 3 Single Page Application (SPA)
│   ├── src/
│   │   ├── components/
│   │   │   ├── customer/     # Navbar, HeroSection, PropertyCard, CustomerDashboardLayout, SiteFooter, etc.
│   │   │   └── owner/        # Owner UI Components
│   │   ├── layouts/
│   │   │   ├── CustomerLayout.vue  # Main wrapper with Navbar & Footer for public/customer pages
│   │   │   └── OwnerLayout.vue     # Owner portal wrapper with dark side navigation
│   │   ├── router/
│   │   │   └── index.js      # Vue Router route definitions
│   │   ├── views/
│   │   │   ├── auth/         # LoginView.vue, RegisterView.vue
│   │   │   ├── customer/     # Home.vue (Polished), Profile.vue, Properties/, Units/, PurchaseRequests/, Contracts/, Payments/
│   │   │   └── owner/        # Dashboard.vue, Properties/, Units/, Customers/, PurchaseRequests/, Contracts/, Payments/
│   │   ├── style.css         # Global styles & typography (Inter font, variables)
│   │   └── style-skeleton.css# Simple wireframe/skeleton styling for developer placeholder pages
│   ├── index.html
│   └── package.json
│
└── README.md
```

---

## 🌐 Routes & Frontend Navigation Map

### 1. Customer Area (Public & Account)
Layout: `CustomerLayout.vue`

- `/` – **Home Page** *(POLISHED PRODUCTION UI - DO NOT REDESIGN)*
  - Includes Navbar, Hero Section, Property Search, Featured Properties, Why Choose Us, How It Works, CTA Section, and Footer.
- `/properties` – Properties Catalog (Search/filter placeholders & property cards skeleton)
- `/properties/:id` – Property Details (Overview, price, available units table)
- `/units/:id` – Unit Details (Specifications, price, request purchase CTA)
- `/profile` – **Customer Account Dashboard** *(Uses `CustomerDashboardLayout.vue`)*
  - Left Sidebar Menu: `My Profile`, `Purchase Requests`, `Contracts`, `Payments`, `Logout`.
  - Main Content: Personal info overview & quick summary stats.
- `/purchase-requests` – Customer Purchase Requests Table (Wrapped in Account Dashboard Layout)
- `/contracts` – Customer Signed Contracts Table (Wrapped in Account Dashboard Layout)
- `/payments` – Customer Payment History & Installments Table (Wrapped in Account Dashboard Layout)

### 2. Owner Area (Management Portal)
Layout: `OwnerLayout.vue`

- `/owner/dashboard` – Owner Dashboard Overview (Stat cards & summary metrics)
- `/owner/properties` – Properties Table (`+ Add Property`, Edit/Delete action buttons)
- `/owner/properties/create` – Create Property Form
- `/owner/properties/:id/edit` – Edit Property Form
- `/owner/units` – Units Inventory Table
- `/owner/customers` – Customer Profiles & Inquiries Table
- `/owner/purchase-requests` – Purchase Requests Table with placeholder Approve/Reject actions
- `/owner/contracts` – Contracts Management Table
- `/owner/payments` – Payments & Installments Tracking Table

### 3. Authentication
- `/login` – Simple login form with demo routing buttons for Owner & Customer.
- `/register` – Simple customer registration form.

---

## 🎨 UI Guidelines & Team Rules

1. **Home Page (`/`) is Fully Polished:**
   - Designed with rich aesthetics (Purple `#864CFF`, Dark Navy `#1a1a2e`, Cyan `#47BFFF`).
   - Uses native inline SVGs and Inter font.
   - **CRITICAL:** Do NOT modify, redesign, or refactor the Home page unless explicitly requested.

2. **All Pages Consume the Real API:**
   - Every view reads from the Laravel API through the single Axios client in `src/services/api.js`.
   - There is no mock business data anywhere in `src/`. If a screen needs data, it needs an endpoint.
   - The plain styling of the non-home pages comes from `style-skeleton.css` and is intentional.

4. **Two Roles Only:**
   - `owner` and `customer`. There is no admin, tenant or property_manager role.
   - Unit statuses are `available`, `occupied`, `reserved`. There is no `sold`.
   - Owners may only ever read or write records belonging to properties they own; this is
     enforced by policies and query scoping in the API, not by the SPA.

---

## 🚀 Running PropSpace Locally

### 1. Backend (Laravel API on :8000)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Point DB_* at your MySQL database, then:
php artisan migrate:fresh --seed
php artisan serve
```

### 2. Frontend (Vue SPA on :5173)

```bash
cd frontend
npm install
npm run dev
```

The SPA calls `http://127.0.0.1:8000/api` by default. Override with `VITE_API_BASE_URL`.

---

## 🔑 Demo Credentials

`php artisan migrate:fresh --seed` creates a complete scenario. **Every account uses the
password `password`.**

| Role | Email | Notes |
| --- | --- | --- |
| Owner | `owner@propspace.com` | Hassan Farouk — Nile View, Palm Gardens |
| Owner | `owner2@propspace.com` | Nadia Mansour — Alexandria Marina |
| Customer | `customer@propspace.com` | Omar Sabry — deals with both owners |
| Customer | `customer2@propspace.com` | Salma Adel — Hassan only |
| Customer | `customer3@propspace.com` | Youssef Ibrahim — Nadia only |
| Customer | `customer4@propspace.com` | Dina Hafez — Hassan only |
| Customer | `customer5@propspace.com` | Karim Nassar — deals with both owners |

The relationships are deliberately asymmetric so ownership isolation is testable: an endpoint
that leaks shows it immediately.

Seeded volume: 3 properties, 5 buildings, 12 units (available/occupied/reserved), 5 contracts,
20 payments (paid/pending/overdue/cancelled) and 7 purchase requests.

---

## ✅ Tests

### Backend

```bash
cd backend
php artisan test
```

Runs against in-memory SQLite (configured in `phpunit.xml`) — it never touches your MySQL
database.

### Frontend build

```bash
cd frontend
npm run build
```

### End-to-end (Playwright)

```bash
cd frontend
npm install
npx playwright install chromium   # first run only
npm run e2e
```

`npm run e2e` builds the SPA in `e2e` mode, then Playwright starts both servers itself:

- the API on **:8001** using `backend/.env.e2e`
- the SPA on **:4173**

**Your normal database is never touched.** `backend/.env.e2e` points at a throwaway
`backend/database/e2e.sqlite`, which is rebuilt from `DemoDataSeeder` before every spec file.
Ports 8001/4173 are used so a running `php artisan serve` / `npm run dev` won't collide.

Useful variants:

```bash
npm run e2e:ui        # interactive runner
npm run e2e:report    # open the last HTML report
npx playwright test e2e/isolation.spec.js   # one file
```

---

## 🤖 CI

`.github/workflows/ci.yml` runs on every push and pull request, in three jobs:

| Job | What it does |
| --- | --- |
| `backend` | `composer install`, `migrate:fresh --seed`, `php artisan test` |
| `frontend` | `npm ci`, `npm run build` |
| `e2e` | installs both stacks + Chromium, then `npm run e2e` |

CI uses SQLite rather than MySQL: nothing under test depends on MySQL-specific behaviour, and
the one query with a vendor-specific branch (the dashboard's monthly revenue aggregate) has a
SQLite path. Composer, npm and Playwright browsers are cached. When E2E fails, the HTML report
and failure traces are uploaded as artifacts.
