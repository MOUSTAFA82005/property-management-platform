<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BrandLogo from '../components/BrandLogo.vue'
import NotificationBell from '../components/notifications/NotificationBell.vue'

const open = ref(false)
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const displayName = computed(() => user.value?.name || 'Owner')
const initial = computed(() => (displayName.value.trim()[0] || 'O').toUpperCase())
const firstName = computed(() => displayName.value.trim().split(/\s+/)[0])
const roleLabel = computed(() => (user.value?.role === 'owner' ? 'Owner' : 'Account'))

async function handleLogout() {
  close()
  closeAccount()
  await authStore.logout()
  router.push('/login')
}

// ── Account menu ────────────────────────────────────────────────────
// A real menu, not a decorative block: click to open, click-away or Escape
// to close, arrow keys to move between items, focus returned to the trigger
// on close. Built on Vue state — no dropdown library is installed.

const accountOpen = ref(false)
const accountWrap = ref(null)
const accountTrigger = ref(null)
const accountMenu = ref(null)

function menuItems() {
  return Array.from(accountMenu.value?.querySelectorAll('[role="menuitem"]') || [])
}

async function openAccount() {
  accountOpen.value = true
  await nextTick()
  menuItems()[0]?.focus()
}

function closeAccount({ refocus = false } = {}) {
  if (!accountOpen.value) return
  accountOpen.value = false
  if (refocus) accountTrigger.value?.focus()
}

function toggleAccount() {
  accountOpen.value ? closeAccount({ refocus: true }) : openAccount()
}

/** Roving focus inside the open menu. */
function onMenuKeydown(event) {
  const items = menuItems()
  if (!items.length) return

  const current = items.indexOf(document.activeElement)

  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault()
    const step = event.key === 'ArrowDown' ? 1 : -1
    const next = (current + step + items.length) % items.length
    items[next].focus()
  } else if (event.key === 'Home') {
    event.preventDefault()
    items[0].focus()
  } else if (event.key === 'End') {
    event.preventDefault()
    items[items.length - 1].focus()
  } else if (event.key === 'Tab') {
    // Tabbing out of the menu is a dismissal, not a trap.
    closeAccount()
  }
}

function onDocumentPointerDown(event) {
  if (accountOpen.value && !accountWrap.value?.contains(event.target)) {
    closeAccount()
  }
}

if (typeof window !== 'undefined') {
  document.addEventListener('pointerdown', onDocumentPointerDown)
}

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    document.removeEventListener('pointerdown', onDocumentPointerDown)
  }
})

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

  // "Customers" rather than "Tenants": the API, the tables and the seed data
  // all use customer, and renaming only the label would put the UI back out
  // of step with the domain.
  { label: 'Customers', to: '/owner/customers', icon: 'fa-solid fa-users', group: 'Management' },
  { label: 'Purchase Requests', to: '/owner/purchase-requests', icon: 'fa-solid fa-inbox', group: 'Management' },
  { label: 'Contracts', to: '/owner/contracts', icon: 'fa-solid fa-file-contract', group: 'Management' },
  { label: 'Payments', to: '/owner/payments', icon: 'fa-solid fa-money-bill-wave', group: 'Management' },

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

// Navigating anywhere dismisses both the mobile nav and the account menu.
watch(() => route.path, () => {
  close()
  closeAccount()
})

const handleEscape = (event) => {
  if (event.key !== 'Escape') return
  if (accountOpen.value) closeAccount({ refocus: true })
  close()
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
        <RouterLink to="/owner/dashboard" class="owner-brand" @click="close" aria-label="PropSpace owner portal">
          <BrandLogo :size="30" tone="light" subtitle="Owner Portal" />
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
          <NotificationBell portal="owner" />

          <div ref="accountWrap" class="owner-account">
            <button
              ref="accountTrigger"
              type="button"
              class="owner-user owner-account-trigger"
              data-testid="account-menu"
              :aria-expanded="accountOpen"
              aria-haspopup="menu"
              aria-controls="owner-account-menu"
              @click="toggleAccount"
            >
              <span class="owner-avatar" aria-hidden="true">{{ initial }}</span>
              <span class="owner-account-id">
                <small>Welcome,</small>
                <b>{{ firstName }}</b>
              </span>
              <i
                class="fa-solid fa-chevron-down owner-user-chevron"
                :class="{ 'is-open': accountOpen }"
                aria-hidden="true"
              ></i>
            </button>

            <Transition name="ps-pop">
              <div
                v-if="accountOpen"
                id="owner-account-menu"
                ref="accountMenu"
                class="owner-account-menu"
                role="menu"
                :aria-label="`Account menu for ${displayName}`"
                @keydown="onMenuKeydown"
              >
                <div class="owner-account-head">
                  <b>{{ displayName }}</b>
                  <small>{{ user?.email || roleLabel }}</small>
                </div>

                <RouterLink
                  to="/owner/profile"
                  class="owner-account-item"
                  role="menuitem"
                  @click="closeAccount()"
                >
                  <i class="fa-solid fa-user" aria-hidden="true"></i>
                  <span>Profile</span>
                </RouterLink>

                <button
                  type="button"
                  class="owner-account-item owner-account-item-danger"
                  role="menuitem"
                  data-testid="account-logout"
                  @click="handleLogout"
                >
                  <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                  <span>Logout</span>
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <main id="owner-content" class="owner-content">
        <!--
          Route change → new key → the wrapper remounts and its CSS entrance
          animation replays. A <Transition> was tried first and rejected: it
          keeps the outgoing page in the DOM next to the incoming one, so the
          document briefly holds two pages of content. This swaps in a single
          patch, and works with the multi-root templates most owner views use.
        -->
        <RouterView v-slot="{ Component, route: current }">
          <div :key="current.path" class="ps-route">
            <component :is="Component" />
          </div>
        </RouterView>
      </main>
    </div>
  </div>
</template>
