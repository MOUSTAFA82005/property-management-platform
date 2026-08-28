<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount } from 'vue'
import { useRoute, RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import BrandLogo from '../BrandLogo.vue'
import NotificationBell from '../notifications/NotificationBell.vue'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()

const mobileOpen = ref(false)

const isActive    = (name) => route.name === name
const isLoggedIn  = computed(() => !!authStore.token)
const isOwner     = computed(() => authStore.isOwner())

const user        = computed(() => authStore.user)
const displayName = computed(() => user.value?.name || 'Account')
const firstName   = computed(() => displayName.value.trim().split(/\s+/)[0])
const initial     = computed(() => (displayName.value.trim()[0] || 'A').toUpperCase())

const toggleMobile = () => { mobileOpen.value = !mobileOpen.value }
const closeMobile  = () => { mobileOpen.value = false }

async function handleLogout() {
  closeMobile()
  closeAccount()
  await authStore.logout()
  router.push('/login')
}

// ── Account menu ────────────────────────────────────────────────────
// Same behaviour as the owner portal's control, on Vue state alone.

const accountOpen = ref(false)
const accountWrap = ref(null)
const accountTrigger = ref(null)
const accountMenu = ref(null)

const menuItems = () =>
  Array.from(accountMenu.value?.querySelectorAll('[role="menuitem"]') || [])

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

const toggleAccount = () =>
  (accountOpen.value ? closeAccount({ refocus: true }) : openAccount())

function onMenuKeydown(event) {
  const items = menuItems()
  if (!items.length) return

  const current = items.indexOf(document.activeElement)

  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault()
    const step = event.key === 'ArrowDown' ? 1 : -1
    items[(current + step + items.length) % items.length].focus()
  } else if (event.key === 'Tab') {
    closeAccount()
  }
}

function onDocumentPointerDown(event) {
  if (accountOpen.value && !accountWrap.value?.contains(event.target)) closeAccount()
}

function onDocumentKeydown(event) {
  if (event.key === 'Escape') closeAccount({ refocus: true })
}

if (typeof window !== 'undefined') {
  document.addEventListener('pointerdown', onDocumentPointerDown)
  document.addEventListener('keydown', onDocumentKeydown)
}

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    document.removeEventListener('pointerdown', onDocumentPointerDown)
    document.removeEventListener('keydown', onDocumentKeydown)
  }
})

watch(() => route.path, () => {
  closeMobile()
  closeAccount()
})
</script>

<template>
  <header class="site-nav">
    <div class="nav-container">
      <!-- Logo -->
      <RouterLink to="/" class="nav-logo" @click="closeMobile" aria-label="PropSpace home">
        <BrandLogo :size="32" />
      </RouterLink>

      <!-- Desktop Nav -->
      <nav class="nav-links">
        <RouterLink to="/" class="nav-link" :class="{ active: isActive('customer.home') }">Home</RouterLink>
        <a href="/#why-us" class="nav-link">About</a>
        <a href="/#footer" class="nav-link">Contact</a>

        <RouterLink to="/properties" class="nav-link" :class="{ active: isActive('customer.properties.index') }">Properties</RouterLink>

        <!-- Owner Portal link (owners only) -->
        <RouterLink v-if="isLoggedIn && isOwner" to="/owner/dashboard" class="nav-link nav-link-owner">
          Owner Portal
        </RouterLink>

        <!-- Account link (authenticated customers) -->
        <RouterLink v-if="isLoggedIn && !isOwner" to="/profile" class="nav-link" :class="{ active: isActive('customer.profile') }">
          Account
        </RouterLink>
      </nav>

      <!-- Desktop Auth Actions -->
      <div class="nav-actions">
        <template v-if="!isLoggedIn">
          <RouterLink to="/login" class="btn-ghost">Login</RouterLink>
          <RouterLink to="/register" class="btn-ghost btn-ghost-primary">Register</RouterLink>
        </template>
        <template v-else>
          <NotificationBell :portal="isOwner ? 'owner' : 'customer'" />

          <div ref="accountWrap" class="nav-account">
            <button
              ref="accountTrigger"
              type="button"
              class="nav-account-trigger"
              data-testid="account-menu"
              :aria-expanded="accountOpen"
              aria-haspopup="menu"
              aria-controls="nav-account-menu"
              @click="toggleAccount"
            >
              <span class="nav-avatar" aria-hidden="true">{{ initial }}</span>
              <span class="nav-account-name">
                <small>Welcome,</small>
                <b>{{ firstName }}</b>
              </span>
              <svg class="nav-chevron" :class="{ 'is-open': accountOpen }" width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <Transition name="ps-pop">
            <div
              v-if="accountOpen"
              id="nav-account-menu"
              ref="accountMenu"
              class="nav-account-menu"
              role="menu"
              :aria-label="`Account menu for ${displayName}`"
              @keydown="onMenuKeydown"
            >
              <div class="nav-account-head">
                <span class="nav-avatar nav-avatar-lg" aria-hidden="true">{{ initial }}</span>
                <span class="nav-account-id">
                  <b>{{ displayName }}</b>
                  <small>{{ user?.email }}</small>
                </span>
              </div>

              <RouterLink
                :to="isOwner ? '/owner/profile' : '/profile'"
                class="nav-account-item"
                role="menuitem"
                @click="closeAccount()"
              >
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <span>Profile</span>
              </RouterLink>

              <button
                type="button"
                class="nav-account-item nav-account-item-danger"
                role="menuitem"
                data-testid="logout"
                @click="handleLogout"
              >
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span>Logout</span>
              </button>
            </div>
            </Transition>
          </div>
        </template>
      </div>

      <!-- Hamburger -->
      <button class="hamburger" @click="toggleMobile" :aria-expanded="mobileOpen" aria-label="Toggle menu">
        <span :class="{ open: mobileOpen }"></span>
        <span :class="{ open: mobileOpen }"></span>
        <span :class="{ open: mobileOpen }"></span>
      </button>
    </div>

    <!-- Mobile Menu -->
    <Transition name="slide-down">
      <div v-if="mobileOpen" class="mobile-menu">
        <RouterLink to="/" class="mobile-link" @click="closeMobile">Home</RouterLink>
        <a href="/#why-us" class="mobile-link" @click="closeMobile">About</a>
        <a href="/#footer" class="mobile-link" @click="closeMobile">Contact</a>

        <RouterLink to="/properties" class="mobile-link" @click="closeMobile">Properties</RouterLink>

        <template v-if="isLoggedIn && isOwner">
          <RouterLink to="/owner/dashboard" class="mobile-link mobile-link-owner" @click="closeMobile">Owner Portal</RouterLink>
        </template>

        <template v-if="isLoggedIn && !isOwner">
          <RouterLink to="/profile" class="mobile-link" @click="closeMobile">Account</RouterLink>
        </template>

        <div class="mobile-divider"></div>

        <template v-if="!isLoggedIn">
          <RouterLink to="/login" class="mobile-link" @click="closeMobile">Login</RouterLink>
          <RouterLink to="/register" class="mobile-link" @click="closeMobile">Register</RouterLink>
        </template>
        <template v-else>
          <a class="mobile-link mobile-link-logout" @click="handleLogout" style="cursor: pointer;">Logout</a>
        </template>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.site-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.97);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(91, 63, 224, 0.1);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
}

.nav-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 68px;
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  flex-shrink: 0;
}

/* ── Account control ─────────────────────────────────────────────── */

.nav-account { position: relative; }

.nav-account-trigger {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.3rem 0.55rem 0.3rem 0.35rem;
  border: 1px solid var(--line, #e8e9f0);
  border-radius: 100px;
  background: #fff;
  font: inherit;
  cursor: pointer;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

.nav-account-trigger:hover {
  border-color: var(--brand-edge, #dbd2fd);
  background: var(--brand-50, #f5f3ff);
}

.nav-account-trigger:focus-visible {
  outline: 2px solid var(--brand-600, #5B3FE0);
  outline-offset: 2px;
}

.nav-avatar {
  width: 30px;
  height: 30px;
  flex-shrink: 0;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: var(--brand-100, #ece7fe);
  color: var(--brand-700, #4a2fc2);
  font-size: 0.8rem;
  font-weight: 700;
}

.nav-avatar-lg { width: 38px; height: 38px; font-size: 0.95rem; }

.nav-account-name {
  display: flex;
  flex-direction: column;
  line-height: 1.15;
  text-align: left;
  min-width: 0;
}

.nav-account-name small {
  font-size: 0.62rem;
  color: var(--muted-2, #8b8da3);
  letter-spacing: 0.03em;
}

.nav-account-name b {
  font-size: 0.82rem;
  font-weight: 650;
  color: var(--ink, #14141F);
  max-width: 12ch;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.nav-chevron {
  color: var(--muted-2, #8b8da3);
  flex-shrink: 0;
  transition: transform 0.18s ease;
}

.nav-chevron.is-open { transform: rotate(180deg); }

.nav-account-menu {
  position: absolute;
  top: calc(100% + 0.6rem);
  right: 0;
  z-index: 200;
  min-width: 244px;
  max-width: min(280px, calc(100vw - 2rem));
  padding: 0.4rem;
  background: #fff;
  border: 1px solid var(--line, #e8e9f0);
  border-radius: 12px;
  box-shadow: 0 14px 38px rgba(20, 20, 31, 0.12), 0 3px 10px rgba(20, 20, 31, 0.05);
}

.nav-account-head {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.7rem 0.75rem;
  border-bottom: 1px solid var(--line-2, #f1f2f7);
  margin-bottom: 0.35rem;
}

.nav-account-id { min-width: 0; }

.nav-account-id b {
  display: block;
  font-size: 0.85rem;
  font-weight: 650;
  color: var(--ink, #14141F);
}

.nav-account-id small {
  display: block;
  margin-top: 1px;
  font-size: 0.7rem;
  color: var(--muted, #6b6d84);
  overflow-wrap: anywhere;
}

.nav-account-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  width: 100%;
  padding: 0.55rem 0.7rem;
  border: 0;
  border-radius: 8px;
  background: none;
  font: inherit;
  font-size: 0.85rem;
  color: var(--ink-2, #33344a);
  text-align: left;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.18s ease, color 0.18s ease;
}

/* Same icon treatment as the owner portal's account menu. */
.nav-account-item > i {
  width: 1rem;
  font-size: 0.78rem;
  color: var(--muted-2, #8b8da3);
  flex-shrink: 0;
  transition: color 0.18s ease;
}

.nav-account-item:hover {
  background: var(--brand-50, #f5f3ff);
  color: var(--brand-700, #4a2fc2);
}

.nav-account-item:hover > i { color: var(--brand-600, #5B3FE0); }
.nav-account-item-danger:hover > i { color: var(--bad-fg, #b8354a); }

.nav-account-item:focus-visible {
  outline: 2px solid var(--brand-600, #5B3FE0);
  outline-offset: -2px;
}

.nav-account-item-danger:hover {
  background: var(--bad-bg, #fdeef0);
  color: var(--bad-fg, #b8354a);
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex: 1;
}

.nav-link {
  display: inline-block;
  padding: 0.4rem 0.85rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  text-decoration: none;
  border-radius: 6px;
  transition: color 0.2s, background 0.2s;
}

.nav-link:hover,
.nav-link.active {
  color: #5B3FE0;
  background: rgba(91, 63, 224, 0.07);
}

.nav-link-owner {
  color: #5B3FE0;
  font-weight: 600;
  background: rgba(91, 63, 224, 0.06);
}

.nav-link-owner:hover {
  background: rgba(91, 63, 224, 0.14);
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.btn-ghost {
  padding: 0.4rem 0.85rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
  text-decoration: none;
  border-radius: 6px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: color 0.2s, background 0.2s;
  font-family: inherit;
}

.btn-ghost:hover {
  color: #5B3FE0;
  background: rgba(91, 63, 224, 0.07);
}

.btn-ghost-primary {
  background: linear-gradient(135deg, #5B3FE0, #3D279E);
  color: #fff !important;
  padding: 0.45rem 1.1rem;
  border-radius: 8px;
  font-weight: 600;
  box-shadow: 0 2px 12px rgba(91, 63, 224, 0.3);
}

.btn-ghost-primary:hover {
  opacity: 0.92;
  background: linear-gradient(135deg, #5B3FE0, #3D279E) !important;
  color: #fff !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 18px rgba(91, 63, 224, 0.4);
}

.btn-logout {
  color: #ef4444;
}

.btn-logout:hover {
  color: #dc2626;
  background: rgba(239, 68, 68, 0.07);
}

/* Hamburger */
.hamburger {
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;
  width: 36px;
  height: 36px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  margin-left: auto;
}

.hamburger span {
  display: block;
  width: 22px;
  height: 2px;
  background: #14141F;
  border-radius: 2px;
  transition: transform 0.3s, opacity 0.3s;
}

.hamburger span.open:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger span.open:nth-child(2) { opacity: 0; }
.hamburger span.open:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile menu */
.mobile-menu {
  background: #fff;
  border-top: 1px solid rgba(91, 63, 224, 0.08);
  padding: 0.75rem 1.5rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.mobile-link {
  display: block;
  padding: 0.65rem 0.75rem;
  font-size: 0.95rem;
  font-weight: 500;
  color: #334155;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s;
}

.mobile-link:hover {
  background: rgba(91, 63, 224, 0.07);
  color: #5B3FE0;
}

.mobile-link-owner {
  color: #5B3FE0;
  font-weight: 600;
}

.mobile-link-logout {
  color: #ef4444;
}

.mobile-link-logout:hover {
  background: rgba(239, 68, 68, 0.07) !important;
  color: #dc2626 !important;
}

.mobile-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}

/* Slide transition */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 900px) {
  .nav-links,
  .nav-actions {
    display: none;
  }
  .hamburger {
    display: flex;
  }
}
</style>
