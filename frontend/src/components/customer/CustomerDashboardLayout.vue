<script setup>
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router    = useRouter()
const authStore = useAuthStore()

const user  = computed(() => authStore.user)
const initials = computed(() => {
  const name = user.value?.name || ''
  return name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() || '')
    .join('')
  || '?'
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="account-dashboard">
    <!-- Profile Header -->
    <div class="profile-header-bar">
      <div class="profile-header-inner">
        <div class="profile-avatar">{{ initials }}</div>
        <div class="profile-info">
          <h2 class="profile-name">{{ user?.name || 'My Account' }}</h2>
          <p class="profile-email">{{ user?.email || '' }}</p>
          <span v-if="user?.phone" class="profile-phone">{{ user.phone }}</span>
        </div>
        <div class="profile-header-right">
          <span class="profile-badge">Customer</span>
        </div>
      </div>
    </div>

    <div class="account-container">
      <!-- Sidebar Navigation -->
      <aside class="account-sidebar">
        <div class="sidebar-label">MY ACCOUNT</div>
        <RouterLink to="/profile" class="sidebar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          My Profile
        </RouterLink>
        <RouterLink to="/purchase-requests" class="sidebar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 9h18M9 9v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          Purchase Requests
        </RouterLink>
        <RouterLink to="/contracts" class="sidebar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          Contracts
        </RouterLink>
        <RouterLink to="/payments" class="sidebar-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/></svg>
          Payments
        </RouterLink>

        <div class="sidebar-divider"></div>

        <RouterLink to="/" class="sidebar-link sidebar-link-muted">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
          Back to Home
        </RouterLink>
        <a class="sidebar-link sidebar-link-danger" data-testid="logout-sidebar" style="cursor: pointer;" @click="handleLogout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Logout
        </a>
      </aside>

      <!-- Main Content -->
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
  padding-top: 68px;
  padding-bottom: 3rem;
}

/* Profile Header */
.profile-header-bar {
  background: linear-gradient(135deg, #1a1a2e 0%, #2d1b69 100%);
  padding: 1.75rem 1.5rem;
  margin-bottom: 2rem;
}

.profile-header-inner {
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #864CFF, #6B2FFF);
  color: #fff;
  font-size: 1.2rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  letter-spacing: 0.02em;
  box-shadow: 0 4px 16px rgba(134, 76, 255, 0.4);
}

.profile-info {
  flex: 1;
  min-width: 0;
}

.profile-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 0.2rem;
  letter-spacing: -0.01em;
}

.profile-email {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.65);
  margin: 0;
}

.profile-phone {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
  display: block;
  margin-top: 0.15rem;
}

.profile-header-right {
  flex-shrink: 0;
}

.profile-badge {
  display: inline-block;
  padding: 0.3rem 0.85rem;
  background: rgba(134, 76, 255, 0.3);
  color: #c4a0ff;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 100px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border: 1px solid rgba(134, 76, 255, 0.4);
}

/* Layout */
.account-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  gap: 1.75rem;
  align-items: flex-start;
}

/* Sidebar */
.account-sidebar {
  width: 240px;
  flex-shrink: 0;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
  padding: 1rem 0;
  border: 1px solid #f0f2f8;
  position: sticky;
  top: 88px;
}

.sidebar-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #94a3b8;
  margin: 0.5rem 1.25rem 0.75rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  text-decoration: none;
  transition: all 0.18s;
  border-left: 3px solid transparent;
}

.sidebar-link:hover,
.sidebar-link.router-link-active {
  background: rgba(134, 76, 255, 0.06);
  color: #864CFF;
  border-left-color: #864CFF;
}

.sidebar-link svg {
  flex-shrink: 0;
  opacity: 0.7;
}

.sidebar-link:hover svg,
.sidebar-link.router-link-active svg {
  opacity: 1;
}

.sidebar-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 0.75rem 0;
}

.sidebar-link-muted {
  color: #64748b;
  font-weight: 500;
}

.sidebar-link-muted:hover {
  color: #475569 !important;
  background: rgba(100, 116, 139, 0.06) !important;
  border-left-color: #94a3b8 !important;
}

.sidebar-link-danger {
  color: #ef4444 !important;
}

.sidebar-link-danger:hover {
  background: rgba(239, 68, 68, 0.06) !important;
  border-left-color: #ef4444 !important;
}

/* Main Content */
.account-content {
  flex: 1;
  min-width: 0;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
  border: 1px solid #f0f2f8;
  padding: 2rem;
}

/* Responsive */
@media (max-width: 900px) {
  .account-container {
    flex-direction: column;
    gap: 1.25rem;
  }

  .account-sidebar {
    width: 100%;
    position: static;
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    padding: 0.75rem;
    gap: 0.25rem;
  }

  .sidebar-label {
    width: 100%;
    margin: 0.25rem 0.5rem 0.5rem;
  }

  .sidebar-link {
    flex: 1 1 auto;
    min-width: 130px;
    border-left: none;
    border-radius: 8px;
    padding: 0.6rem 0.85rem;
    font-size: 0.85rem;
    border-bottom: 2px solid transparent;
  }

  .sidebar-link:hover,
  .sidebar-link.router-link-active {
    border-left-color: transparent;
    border-bottom-color: #864CFF;
  }

  .sidebar-link-danger:hover {
    border-bottom-color: #ef4444 !important;
    border-left-color: transparent !important;
  }

  .sidebar-divider {
    display: none;
  }

  .account-content {
    padding: 1.5rem;
  }

  .profile-header-inner {
    gap: 1rem;
  }

  .profile-avatar {
    width: 48px;
    height: 48px;
    font-size: 1rem;
  }

  .profile-name {
    font-size: 1.05rem;
  }
}

@media (max-width: 480px) {
  .profile-header-right {
    display: none;
  }

  .account-content {
    padding: 1.25rem;
  }
}
</style>
