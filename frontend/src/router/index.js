import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  // -------------------------------------------------------
  // Auth
  // -------------------------------------------------------
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/auth/RegisterView.vue'),
  },

  // -------------------------------------------------------
  // Owner area  (requires auth + owner role)
  // -------------------------------------------------------
  {
    path: '/owner',
    component: () => import('../layouts/OwnerLayout.vue'),
    meta: { requiresAuth: true, requiresOwner: true },
    children: [
      { path: '',          redirect: '/owner/dashboard' },
      {
        path: 'dashboard',
        name: 'owner.dashboard',
        component: () => import('../views/owner/Dashboard.vue'),
      },

      // Properties
      {
        path: 'properties',
        name: 'owner.properties.index',
        component: () => import('../views/owner/Properties/Index.vue'),
      },
      {
        path: 'properties/create',
        name: 'owner.properties.create',
        component: () => import('../views/owner/Properties/Create.vue'),
      },
      {
        path: 'properties/:id',
        name: 'owner.properties.show',
        component: () => import('../views/owner/Properties/Show.vue'),
      },
      {
        path: 'properties/:id/edit',
        name: 'owner.properties.edit',
        component: () => import('../views/owner/Properties/Edit.vue'),
      },

      // Units
      {
        path: 'units',
        name: 'owner.units.index',
        component: () => import('../views/owner/Units/Index.vue'),
      },
      {
        path: 'units/create',
        name: 'owner.units.create',
        component: () => import('../views/owner/Units/Create.vue'),
      },
      {
        path: 'units/:id/edit',
        name: 'owner.units.edit',
        component: () => import('../views/owner/Units/Edit.vue'),
      },

      // Buildings
      {
        path: 'buildings',
        name: 'owner.buildings.index',
        component: () => import('../views/owner/Buildings/Index.vue'),
      },
      {
        path: 'buildings/create',
        name: 'owner.buildings.create',
        component: () => import('../views/owner/Buildings/Create.vue'),
      },
      {
        path: 'buildings/:id/edit',
        name: 'owner.buildings.edit',
        component: () => import('../views/owner/Buildings/Edit.vue'),
      },

      // Customers
      {
        path: 'customers',
        name: 'owner.customers.index',
        component: () => import('../views/owner/Customers/Index.vue'),
      },
      {
        path: 'customers/:id',
        name: 'owner.customers.show',
        component: () => import('../views/owner/Customers/Show.vue'),
      },

      // Purchase Requests
      {
        path: 'purchase-requests',
        name: 'owner.purchase-requests.index',
        component: () => import('../views/owner/PurchaseRequests/Index.vue'),
      },
      {
        path: 'purchase-requests/:id',
        name: 'owner.purchase-requests.show',
        component: () => import('../views/owner/PurchaseRequests/Show.vue'),
      },

      // Contracts
      {
        path: 'contracts',
        name: 'owner.contracts.index',
        component: () => import('../views/owner/Contracts/Index.vue'),
      },
      {
        path: 'contracts/create',
        name: 'owner.contracts.create',
        component: () => import('../views/owner/Contracts/Create.vue'),
      },
      {
        path: 'contracts/:id/edit',
        name: 'owner.contracts.edit',
        component: () => import('../views/owner/Contracts/Edit.vue'),
      },
      {
        path: 'notifications',
        name: 'owner.notifications',
        component: () => import('../views/NotificationsView.vue'),
      },
      {
        path: 'contracts/:id',
        name: 'owner.contracts.show',
        component: () => import('../views/owner/Contracts/Show.vue'),
      },

      // Profile
      {
        path: 'profile',
        name: 'owner.profile',
        component: () => import('../views/owner/Profile.vue'),
      },

      // Payments
      {
        path: 'payments',
        name: 'owner.payments.index',
        component: () => import('../views/owner/Payments/Index.vue'),
      },
      {
        path: 'payments/:id',
        name: 'owner.payments.show',
        component: () => import('../views/owner/Payments/Show.vue'),
      },
    ],
  },

  // -------------------------------------------------------
  // Customer / Public area
  // -------------------------------------------------------
  {
    path: '/',
    component: () => import('../layouts/CustomerLayout.vue'),
    children: [
      // Public routes — no auth required
      {
        path: '',
        name: 'customer.home',
        meta: { public: true },
        component: () => import('../views/customer/Home.vue'),
      },
      {
        path: 'properties',
        name: 'customer.properties.index',
        meta: { public: true },
        component: () => import('../views/customer/Properties/Index.vue'),
      },
      {
        path: 'properties/:id',
        name: 'customer.properties.show',
        meta: { public: true },
        component: () => import('../views/customer/Properties/Show.vue'),
      },
      {
        path: 'units/:id',
        name: 'customer.units.show',
        meta: { public: true },
        component: () => import('../views/customer/Units/Show.vue'),
      },

      // Private customer-only routes — require auth AND customer role
      {
        path: 'notifications',
        name: 'customer.notifications',
        meta: { requiresCustomer: true },
        component: () => import('../views/NotificationsView.vue'),
      },
      {
        path: 'purchase-requests',
        name: 'customer.purchase-requests.index',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/PurchaseRequests/Index.vue'),
      },
      {
        path: 'purchase-requests/:id',
        name: 'customer.purchase-requests.show',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/PurchaseRequests/Show.vue'),
      },
      {
        path: 'contracts',
        name: 'customer.contracts.index',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/Contracts/Index.vue'),
      },
      {
        path: 'contracts/:id',
        name: 'customer.contracts.show',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/Contracts/Show.vue'),
      },
      {
        path: 'payments',
        name: 'customer.payments.index',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/Payments/Index.vue'),
      },
      {
        path: 'payments/:id',
        name: 'customer.payments.show',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/Payments/Show.vue'),
      },
      {
        path: 'profile',
        name: 'customer.profile',
        meta: { requiresCustomer: true },
        component: () => import('../views/customer/Profile.vue'),
      },
    ],
  },

  // Catch-all
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Restore the session behind a stored token before deciding anything.
  // initializeAuth() is idempotent, so this only hits /auth/me once.
  if (!auth.initialized) {
    await auth.initializeAuth()
  }

  const isAuthPage = to.name === 'login' || to.name === 'register'
  const isPublic = to.meta?.public === true
  const requiresAuth = to.meta?.requiresAuth === true
  const requiresOwner = to.meta?.requiresOwner === true
  const requiresCustomer = to.meta?.requiresCustomer === true

  // Signed-in users have no business on the login/register screens.
  if (isAuthPage) {
    return auth.isAuthenticated ? auth.homeRoute() : true
  }

  if (isPublic) {
    return true
  }

  // Any protected route without a session → login, remembering where they were
  // headed so the login page can send them back.
  if ((requiresAuth || requiresOwner || requiresCustomer) && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  // Owner portal is owner-only. The API enforces this too (role:owner) — this
  // guard just avoids rendering a page that would only 403.
  if (requiresOwner && !auth.isOwner()) {
    return '/'
  }

  // Customer account pages are customer-only; owners go to their portal.
  if (requiresCustomer && !auth.isCustomer()) {
    return '/owner/dashboard'
  }

  return true
})

export default router
