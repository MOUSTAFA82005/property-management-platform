import { createRouter, createWebHistory } from 'vue-router'

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
  // Owner area
  // -------------------------------------------------------
  {
    path: '/owner',
    component: () => import('../layouts/OwnerLayout.vue'),
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
        path: 'contracts/:id',
        name: 'owner.contracts.show',
        component: () => import('../views/owner/Contracts/Show.vue'),
      },

      // Payments
      {
        path: 'payments',
        name: 'owner.payments.index',
        component: () => import('../views/owner/Payments/Index.vue'),
      },
      {
        path: 'payments/create',
        name: 'owner.payments.create',
        component: () => import('../views/owner/Payments/Create.vue'),
      },
      {
        path: 'payments/:id',
        name: 'owner.payments.show',
        component: () => import('../views/owner/Payments/Show.vue'),
      },
    ],
  },

  // -------------------------------------------------------
  // Customer area
  // -------------------------------------------------------
  {
    path: '/',
    component: () => import('../layouts/CustomerLayout.vue'),
    children: [
      {
        path: '',
        name: 'customer.home',
        component: () => import('../views/customer/Home.vue'),
      },

      // Properties
      {
        path: 'properties',
        name: 'customer.properties.index',
        component: () => import('../views/customer/Properties/Index.vue'),
      },
      {
        path: 'properties/:id',
        name: 'customer.properties.show',
        component: () => import('../views/customer/Properties/Show.vue'),
      },

      // Units
      {
        path: 'units/:id',
        name: 'customer.units.show',
        component: () => import('../views/customer/Units/Show.vue'),
      },

      // Purchase Requests
      {
        path: 'purchase-requests',
        name: 'customer.purchase-requests.index',
        component: () => import('../views/customer/PurchaseRequests/Index.vue'),
      },
      {
        path: 'purchase-requests/:id',
        name: 'customer.purchase-requests.show',
        component: () => import('../views/customer/PurchaseRequests/Show.vue'),
      },

      // Contracts
      {
        path: 'contracts',
        name: 'customer.contracts.index',
        component: () => import('../views/customer/Contracts/Index.vue'),
      },
      {
        path: 'contracts/:id',
        name: 'customer.contracts.show',
        component: () => import('../views/customer/Contracts/Show.vue'),
      },

      // Payments
      {
        path: 'payments',
        name: 'customer.payments.index',
        component: () => import('../views/customer/Payments/Index.vue'),
      },
      {
        path: 'payments/:id',
        name: 'customer.payments.show',
        component: () => import('../views/customer/Payments/Show.vue'),
      },

      // Profile
      {
        path: 'profile',
        name: 'customer.profile',
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

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  const isPublic = to.name === 'login' || to.name === 'register'

  if (!token && !isPublic) {
    return next({ name: 'login' })
  }

  if (token && isPublic) {
    return next({ path: '/owner/dashboard' })
  }

  next()
})

export default router
