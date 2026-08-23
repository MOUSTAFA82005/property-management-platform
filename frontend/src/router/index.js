import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/public/HomeView.vue'),
  },
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
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/dashboard/DashboardView.vue'),
  },
  {
    path: '/properties',
    name: 'properties',
    component: () => import('../views/properties/PropertiesView.vue'),
  },
  {
    path: '/units',
    name: 'units',
    component: () => import('../views/units/UnitsView.vue'),
  },
  {
    path: '/tenants',
    name: 'tenants',
    component: () => import('../views/tenants/TenantsView.vue'),
  },
  {
    path: '/contracts',
    name: 'contracts',
    component: () => import('../views/contracts/ContractsView.vue'),
  },
  {
    path: '/payments',
    name: 'payments',
    component: () => import('../views/payments/PaymentsView.vue'),
  },
  {
  path: '/tenant-dashboard',
  name: 'tenant-dashboard',
  component: () => import('../views/tenant/TenantDashboardView.vue'),
},
{
  path: '/users',
  name: 'users',
  component: () => import('../views/users/UsersView.vue'),
},
{
  path: '/profile',
  name: 'profile',
  component: () => import('../views/profile/ProfileView.vue'),
},
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router