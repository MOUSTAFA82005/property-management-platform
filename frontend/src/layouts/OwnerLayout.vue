<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router    = useRouter()
const authStore = useAuthStore()

const user     = computed(() => authStore.user)
const initials = computed(() => {
  const name = user.value?.name || ''
  return name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() || '')
    .join('')
  || 'O'
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="owner-layout">
    <!-- Sidebar -->
    <aside class="owner-sidebar">
      <div class="owner-sidebar-header">
        <RouterLink to="/owner/dashboard" class="owner-sidebar-logo">
          <svg width="28" height="26" viewBox="0 0 48 46" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="#864CFF" d="M25.946 44.938c-.664.845-2.021.375-2.021-.698V33.937a2.26 2.26 0 0 0-2.262-2.262H10.287c-.92 0-1.456-1.04-.92-1.788l7.48-10.471c1.07-1.497 0-3.578-1.842-3.578H1.237c-.92 0-1.456-1.04-.92-1.788L10.013.474c.214-.297.556-.474.92-.474h28.894c.92 0 1.456 1.04.92 1.788l-7.48 10.471c-1.07 1.498 0 3.579 1.842 3.579h11.377c.943 0 1.473 1.088.89 1.83L25.947 44.94z"/>
          </svg>
          <span class="owner-sidebar-logo-name">PropSpace</span>
        </RouterLink>
        <div class="owner-sidebar-badge">Owner Portal</div>
      </div>

      <div class="owner-sidebar-label">Main</div>
      <RouterLink to="/owner/dashboard" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/></svg>
        Dashboard
      </RouterLink>
      <RouterLink to="/owner/properties" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        Properties
      </RouterLink>
      <RouterLink to="/owner/units" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/><path d="M14 17.5h7M17.5 14v7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Units
      </RouterLink>

      <div class="owner-sidebar-label">Management</div>
      <RouterLink to="/owner/customers" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
        Customers
      </RouterLink>
      <RouterLink to="/owner/purchase-requests" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 9h18M9 9v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Purchase Requests
      </RouterLink>

      <div class="owner-sidebar-label">Financials</div>
      <RouterLink to="/owner/contracts" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Contracts
      </RouterLink>
      <RouterLink to="/owner/payments" class="owner-nav-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/></svg>
        Payments
      </RouterLink>

      <div class="owner-sidebar-bottom">
        <RouterLink to="/" class="owner-nav-link owner-nav-link-muted">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
          View Site
        </RouterLink>
        <a class="owner-nav-link owner-nav-link-danger" style="cursor: pointer;" @click="handleLogout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Logout
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="owner-content">
      <div class="owner-topbar">
        <div class="owner-topbar-title">Owner Portal</div>
        <div class="owner-topbar-user">
          <span class="owner-topbar-name">{{ user?.name || 'Owner' }}</span>
          <div class="owner-avatar">{{ initials }}</div>
        </div>
      </div>
      <div class="owner-page">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<style scoped>
.owner-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

/* Sidebar */
.owner-sidebar {
  width: 240px;
  flex-shrink: 0;
  background: #1a1a2e;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  overflow-y: auto;
  z-index: 100;
}

.owner-sidebar-header {
  padding: 1.25rem 1.25rem 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  margin-bottom: 0.5rem;
}

.owner-sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  margin-bottom: 0.5rem;
}

.owner-sidebar-logo-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
}

.owner-sidebar-badge {
  font-size: 0.68rem;
  font-weight: 700;
  color: #864CFF;
  background: rgba(134, 76, 255, 0.15);
  border: 1px solid rgba(134, 76, 255, 0.3);
  border-radius: 100px;
  padding: 0.2rem 0.65rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  display: inline-block;
}

.owner-sidebar-label {
  font-size: 0.68rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.3);
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 0.75rem 1.25rem 0.4rem;
}

.owner-nav-link {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.7rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  transition: all 0.18s;
  border-left: 3px solid transparent;
  cursor: pointer;
}

.owner-nav-link:hover,
.owner-nav-link.router-link-active {
  color: #fff;
  background: rgba(134, 76, 255, 0.15);
  border-left-color: #864CFF;
}

.owner-nav-link svg {
  flex-shrink: 0;
  opacity: 0.7;
}

.owner-nav-link:hover svg,
.owner-nav-link.router-link-active svg {
  opacity: 1;
}

.owner-sidebar-bottom {
  margin-top: auto;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.owner-nav-link-muted {
  color: rgba(255, 255, 255, 0.4) !important;
}

.owner-nav-link-muted:hover {
  color: rgba(255, 255, 255, 0.7) !important;
  background: rgba(255, 255, 255, 0.05) !important;
  border-left-color: rgba(255, 255, 255, 0.2) !important;
}

.owner-nav-link-danger {
  color: rgba(239, 68, 68, 0.8) !important;
}

.owner-nav-link-danger:hover {
  color: #ef4444 !important;
  background: rgba(239, 68, 68, 0.1) !important;
  border-left-color: #ef4444 !important;
}

/* Main */
.owner-content {
  flex: 1;
  margin-left: 240px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.owner-topbar {
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  padding: 0 2rem;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}

.owner-topbar-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: -0.01em;
}

.owner-topbar-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.owner-topbar-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
}

.owner-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #864CFF, #6B2FFF);
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.02em;
}

.owner-page {
  padding: 2rem;
  flex: 1;
}
</style>
