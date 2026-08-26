# 🏢 Property Management Platform

![Project Status](https://img.shields.io/badge/Status-In%20Development-blue)
![Laravel](https://img.shields.io/badge/Backend-Laravel_11-EF3B2D?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Frontend-Vue.js_3-4FC08D?style=flat&logo=vuedotjs&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat&logo=mysql&logoColor=white)

A comprehensive, scalable, and intuitive web-based **Property Management System (PMS)**. Designed to streamline real estate operations, it provides powerful tools for property owners, managers, and tenants to handle everything from lease contracts to payment tracking.

---

## ✨ Key Features & Modules

- **🔐 Authentication & Role Management:** Secure login and registration for admins, property managers, and tenants.
- **🏢 Property & Building Management:** Track and organize portfolios consisting of properties, buildings, and specific residential or commercial units.
- **👤 Tenant Management:** Maintain tenant profiles, contact information, and rental history.
- **📄 Contract & Lease Administration:** Generate, manage, and renew rental agreements.
- **💳 Financial & Payment Tracking:** Process payments, track invoices, and monitor financial health.
- **📊 Interactive Dashboards:** Specialized views and notifications for administrators and tenants.

---

## 🛠️ Technology Stack

The platform is architected as a decoupled system featuring a robust backend API and a modern, reactive frontend Single Page Application (SPA).

### Backend (API)
- **Framework:** Laravel 11.x (PHP 8.3+)
- **Database:** MySQL
- **Authentication:** Laravel Sanctum (Token-based API Auth)
- **Architecture:** RESTful API principles

### Frontend (SPA)
- **Framework:** Vue.js 3 (Composition API)
- **Build Tool:** Vite
- **State Management:** Pinia
- **Routing:** Vue Router 4
- **UI & Styling:** Bootstrap 5 & Vanilla CSS
- **HTTP Client:** Axios

---

## 📂 Project Structure

```text
property-management-platform/
│
├── backend/                  # Laravel API application
│   ├── app/Models/           # Eloquent Data Models
│   ├── database/migrations/  # Database schema (Users, Tenants, Properties, etc.)
│   ├── routes/               # API endpoint definitions
│   └── ...
│
├── frontend/                 # Vue.js Single Page Application
│   ├── src/
│   │   ├── views/            # Route components (Dashboard, Properties, Tenants, Auth)
│   │   ├── router/           # Vue Router configuration
│   │   ├── services/         # API integration (Axios)
│   │   └── App.vue
│   └── package.json
│
├── docs/                     # Project Documentation
│   ├── API/                  # API Specifications (Postman/Swagger)
│   ├── ERD/                  # Entity Relationship Diagrams
│   ├── UI-UX/                # Wireframes and Design Assets
│   └── UML/                  # System Architecture Diagrams
│
└── README.md
```

---

## 🚀 Getting Started

Follow these instructions to set up the project locally for development and testing.

### Prerequisites
- PHP 8.3+
- Composer
- Node.js (v18+) and npm
- MySQL Server

### 1. Backend Setup (Laravel)
```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment variables and set up application key
cp .env.example .env
php artisan key:generate

# Configure your database credentials in the .env file
# DB_CONNECTION=mysql
# DB_DATABASE=safi_pms
# DB_USERNAME=root
# DB_PASSWORD=

# Run database migrations
php artisan migrate
```

### 2. Frontend Setup (Vue.js)
```bash
# Navigate to frontend directory
cd frontend

# Install Node dependencies
npm install

# Start the Vite development server
npm run dev
```
*The frontend will typically be accessible at `http://localhost:5173`.*

---

## 👥 Team & Responsibilities

This project is collaboratively built by our dedicated development team, broken down into key focus areas:

- **Member 1** — Authentication, Authorization & User Management
- **Member 2** — Properties, Buildings & Units Architecture
- **Member 3** — Tenants, Leases & Contracts Management
- **Member 4** — Payments, Invoices & Financial Reporting
- **Member 5** — Interactive Dashboards & Real-time Notifications

---

*For more detailed technical documentation, please refer to the `docs/` directory.*
