<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const open = ref(false)
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const displayName = computed(() => user.value?.name || 'Owner')
const initial = computed(() => (displayName.value.trim()[0] || 'O').toUpperCase())

async function handleLogout() {
  close()
  await authStore.logout()
  router.push('/login')
}

/**
 * Grouped navigation. The groups describe what the owner is trying to do
 * rather than which table the data lives in, so the sidebar reads as a
 * workflow instead of a schema dump.
 */
const nav = [
  { label: 'Dashboard', to: '/owner/dashboard', icon: 'fa-solid fa-chart-line', group: 'Overview' },
  { label: 'Properties', to: '/owner/properties', icon: 'fa-solid fa-building', group: 'Property management' },
  { label: 'Buildings', to: '/owner/buildings', icon: 'fa-solid fa-city', group: 'Property management' },
  { label: 'Units', to: '/owner/units', icon: 'fa-solid fa-door-open', group: 'Property management' },
  { label: 'Customers', to: '/owner/customers', icon: 'fa-solid fa-users', group: 'People & agreements' },
  { label: 'Purchase Requests', to: '/owner/purchase-requests', icon: 'fa-solid fa-inbox', group: 'People & agreements' },
  { label: 'Contracts', to: '/owner/contracts', icon: 'fa-solid fa-file-contract', group: 'People & agreements' },
  { label: 'Payments', to: '/owner/payments', icon: 'fa-solid fa-money-bill-wave', group: 'Finance' },
  { label: 'My Profile', to: '/owner/profile', icon: 'fa-solid fa-user', group: 'Account' },
]

const groups = computed(() => [...new Set(nav.map(item => item.group))])

const isActive = (to) => route.path === to || (to !== '/owner/dashboard' && route.path.startsWith(to + '/'))

/** The topbar names the section the owner is in — no invented status text. */
const currentSection = computed(() => {
  const match = [...nav].sort((a, b) => b.to.length - a.to.length).find(item => isActive(item.to))
  return match?.label || 'Owner workspace'
})

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
    <a class="skip-link" href="#owner-content">Skip to content</a>

    <div v-if="open" class="owner-overlay" @click="close" aria-hidden="true"></div>

    <aside class="owner-sidebar" :class="{ 'is-open': open }" aria-label="Owner navigation">
      <div class="owner-sidebar-head">
        <RouterLink to="/owner/dashboard" class="owner-brand" @click="close">
          <span class="owner-brand-mark" aria-hidden="true">P</span>
          <span>
            <b>PropSpace</b>
            <small>Owner Portal</small>
          </span>
        </RouterLink>

        <button class="owner-sidebar-close" type="button" @click="close" aria-label="Close menu">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
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
            :aria-current="isActive(item.to) ? 'page' : undefined"
            @click="close"
          >
            <span class="owner-nav-icon" aria-hidden="true"><i :class="item.icon"></i></span>
            <span>{{ item.label }}</span>
          </RouterLink>
        </template>
      </nav>

      <div class="owner-sidebar-bottom">
        <RouterLink to="/" class="owner-nav-item" @click="close">
          <span class="owner-nav-icon" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
          <span>View public site</span>
        </RouterLink>
        <button type="button" data-testid="logout" class="owner-nav-item owner-logout" @click="handleLogout">
          <span class="owner-nav-icon" aria-hidden="true"><i class="fa-solid fa-right-from-bracket"></i></span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <div class="owner-main">
      <header class="owner-topbar">
        <button
          class="owner-menu-btn"
          type="button"
          @click="toggleMenu"
          :aria-expanded="open"
          aria-controls="owner-nav"
          aria-label="Open navigation menu"
        >
          <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <div class="owner-top-title">
          <span>{{ currentSection }}</span>
          <small>Owner workspace</small>
        </div>

        <div class="owner-top-actions">
          <div class="owner-user">
            <div class="owner-avatar" aria-hidden="true">{{ initial }}</div>
            <div>
              <b>{{ displayName }}</b>
              <small>{{ user?.email || 'Owner' }}</small>
            </div>
          </div>
        </div>
      </header>

      <main id="owner-content" class="owner-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>
