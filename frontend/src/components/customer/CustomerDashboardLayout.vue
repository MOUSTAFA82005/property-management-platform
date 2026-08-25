<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="account-dashboard">
    <div class="account-header-bar">
      <div class="account-header-content">
        <h1>My Account</h1>
        <p>Manage your profile and your property activity</p>
      </div>
    </div>
    
    <div class="account-container">
      <aside class="account-sidebar">
        <div class="sidebar-header">MY ACCOUNT</div>
        <RouterLink to="/profile" class="sidebar-link">👤 My Profile</RouterLink>
        <RouterLink to="/purchase-requests" class="sidebar-link">📋 Purchase Requests</RouterLink>
        <RouterLink to="/contracts" class="sidebar-link">📄 Contracts</RouterLink>
        <RouterLink to="/payments" class="sidebar-link">💳 Payments</RouterLink>
        <div class="sidebar-divider"></div>
        <a class="sidebar-link text-danger" style="cursor: pointer;" @click="handleLogout">🚪 Logout</a>
      </aside>

      <main class="account-content">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<style scoped>
.account-dashboard {
  background: #f8fafc;
  min-height: calc(100vh - 68px);
  padding-bottom: 4rem;
  padding-top: 68px; /* account for fixed navbar */
}

.account-header-bar {
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  padding: 3rem 1.5rem;
  margin-bottom: 2rem;
}

.account-header-content {
  max-width: 1280px;
  margin: 0 auto;
}

.account-header-content h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.5rem;
  letter-spacing: -0.02em;
}

.account-header-content p {
  color: #64748b;
  font-size: 1.05rem;
}

.account-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  gap: 2rem;
}

.account-sidebar {
  width: 260px;
  flex-shrink: 0;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
  padding: 1rem 0;
  height: fit-content;
}

.sidebar-header {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  margin: 0.5rem 1.5rem 1rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 0.85rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #475569;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.sidebar-link:hover, .sidebar-link.router-link-active {
  background: rgba(134, 76, 255, 0.06);
  color: #864CFF;
  border-left-color: #864CFF;
}

.sidebar-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 1rem 0;
}

.text-danger {
  color: #ef4444 !important;
}

.text-danger:hover {
  background: rgba(239, 68, 68, 0.06) !important;
  border-left-color: #ef4444 !important;
}

.account-content {
  flex: 1;
  min-width: 0;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
  padding: 2rem;
}

@media (max-width: 768px) {
  .account-container {
    flex-direction: column;
  }
  .account-sidebar {
    width: 100%;
  }
}
</style>
