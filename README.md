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

2. **All Other Pages are Functional Skeletons / Wireframes:**
   - Intentionally designed as simple, clean UI placeholders (`style-skeleton.css`).
   - Built so team members can easily plug in backend API integration, Pinia stores, and final feature components without altering layout routes.

3. **Customer Navbar Rule:**
   - Public navbar contains: `Home`, `Properties`, `About`, `Contact`, `Account` (`/profile`), `Login`, `Register`.
   - Private customer features (`Requests`, `Contracts`, `Payments`) live exclusively INSIDE the `Account Dashboard` (`/profile`) sidebar.

4. **Decoupled Development Rule:**
   - Frontend mock data is local only.
   - Do NOT modify Laravel backend code, routes, controllers, or database migrations when building frontend skeletons.

---

## 🚀 How to Run the Project

### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```
*App opens at `http://localhost:5173`.*

### Backend Setup (Laravel API)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```
