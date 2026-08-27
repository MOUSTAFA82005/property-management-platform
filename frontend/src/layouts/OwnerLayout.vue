<!-- <script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="owner-layout">
    <aside class="owner-sidebar">
      <RouterLink to="/owner/dashboard" class="owner-sidebar-logo">
        <span class="owner-sidebar-logo-name">PropSpace Owner</span>
      </RouterLink>

      <div class="owner-sidebar-label">Main Menu</div>
      <RouterLink to="/owner/dashboard" class="owner-nav-link">Dashboard</RouterLink>
      <RouterLink to="/owner/properties" class="owner-nav-link">Properties</RouterLink>
      <RouterLink to="/owner/units" class="owner-nav-link">Units</RouterLink>

      <div class="owner-sidebar-label">Management</div>
      <RouterLink to="/owner/customers" class="owner-nav-link">Customers</RouterLink>
      <RouterLink to="/owner/purchase-requests" class="owner-nav-link">Purchase Requests</RouterLink>
      
      <div class="owner-sidebar-label">Financials</div>
      <RouterLink to="/owner/contracts" class="owner-nav-link">Contracts</RouterLink>
      <RouterLink to="/owner/payments" class="owner-nav-link">Payments</RouterLink>

      <div class="owner-sidebar-bottom">
        <RouterLink to="/" class="owner-nav-link" style="color: #ef4444; padding: 0;">Logout (Demo)</RouterLink>
      </div>
    </aside>

    <main class="owner-content">
      <div class="owner-topbar">
        <div class="owner-topbar-title">Owner Area</div>
        <div class="owner-topbar-user">
          Owner Portal
          <div class="owner-avatar">O</div>
        </div>
      </div>
      <div style="padding: 2rem;">
        <RouterView />
      </div>
    </main>
  </div>
</template> -->
<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

const open = ref(false)
const route = useRoute()

const nav = [
  { label: 'Dashboard', to: '/owner/dashboard', icon: 'fa-solid fa-house', group: 'Main Menu' },
  { label: 'Properties', to: '/owner/properties', icon: 'fa-solid fa-building', group: 'Main Menu' },
  { label: 'Units', to: '/owner/units', icon: 'fa-solid fa-door-open', group: 'Main Menu' },
  { label: 'Customers', to: '/owner/customers', icon: 'fa-solid fa-users', group: 'Management' },
  { label: 'Purchase Requests', to: '/owner/purchase-requests', icon: 'fa-solid fa-file-circle-check', group: 'Management' },
  { label: 'Contracts', to: '/owner/contracts', icon: 'fa-solid fa-file-contract', group: 'Financials' },
  { label: 'Payments', to: '/owner/payments', icon: 'fa-solid fa-money-bill-wave', group: 'Financials' },
]

const groups = computed(() => [...new Set(nav.map(item => item.group))])
const isActive = (to) => route.path === to || (to !== '/owner/dashboard' && route.path.startsWith(to + '/'))

const close = () => { open.value = false }
const toggleMenu = () => { open.value = !open.value }

watch(() => route.path, close)

const handleEscape = (event) => {
  if (event.key === 'Escape') close()
}

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleEscape)
}

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <div class="owner-shell">
    <div v-if="open" class="owner-overlay" @click="close" aria-hidden="true"></div>

    <aside class="owner-sidebar" :class="{ 'is-open': open }" aria-label="Owner navigation">
      <div class="owner-sidebar-head">
        <RouterLink to="/owner/dashboard" class="owner-brand" @click="close">
          <span class="owner-brand-mark">P</span>
          <span>
            <b>PropSpace</b>
            <small>Owner Portal</small>
          </span>
        </RouterLink>

        <button class="owner-sidebar-close" type="button" @click="close" aria-label="Close menu">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <nav class="owner-nav">
        <template v-for="group in groups" :key="group">
          <div class="owner-nav-label">{{ group }}</div>
          <RouterLink
            v-for="item in nav.filter(n => n.group === group)"
            :key="item.to"
            :to="item.to"
            class="owner-nav-item"
            :class="{ active: isActive(item.to) }"
            @click="close"
          >
            <span class="owner-nav-icon"><i :class="item.icon"></i></span>
            <span>{{ item.label }}</span>
          </RouterLink>
        </template>
      </nav>

      <div class="owner-sidebar-bottom">
        <RouterLink to="/" class="owner-nav-item owner-logout" @click="close">
          <span class="owner-nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
          <span>Logout</span>
        </RouterLink>
      </div>
    </aside>

    <main class="owner-main">
      <header class="owner-topbar">
        <button class="owner-menu-btn" type="button" @click="toggleMenu" :aria-expanded="open" aria-label="Open navigation menu">
          <i class="fa-solid fa-bars"></i>
        </button>

        <div class="owner-top-title">
          <span>Owner Area</span>
          <small>Manage your properties and business</small>
        </div>

        <div class="owner-top-actions">
          <button class="owner-icon-btn" type="button" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
          <button class="owner-icon-btn" type="button" aria-label="Notifications">
            <i class="fa-solid fa-bell"></i>
            <span class="owner-notification-dot"></span>
          </button>
          <div class="owner-user">
            <div class="owner-avatar">O</div>
            <div>
              <b>Owner Portal</b>
              <small>Administrator</small>
            </div>
            <i class="fa-solid fa-chevron-down owner-user-chevron"></i>
          </div>
        </div>
      </header>

      <section class="owner-content">
        <RouterView />
      </section>
    </main>
  </div>
</template>
