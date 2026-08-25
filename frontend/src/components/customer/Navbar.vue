<script setup>
import { ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'

const route = useRoute()
const mobileOpen = ref(false)

const isActive = (name) => route.name === name

const toggleMobile = () => {
  mobileOpen.value = !mobileOpen.value
}
const closeMobile = () => {
  mobileOpen.value = false
}
</script>

<template>
  <header class="site-nav" :class="{ 'nav-scrolled': true }">
    <div class="nav-container">
      <!-- Logo -->
      <RouterLink to="/" class="nav-logo" @click="closeMobile">
        <svg width="32" height="30" viewBox="0 0 48 46" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill="#864CFF" d="M25.946 44.938c-.664.845-2.021.375-2.021-.698V33.937a2.26 2.26 0 0 0-2.262-2.262H10.287c-.92 0-1.456-1.04-.92-1.788l7.48-10.471c1.07-1.497 0-3.578-1.842-3.578H1.237c-.92 0-1.456-1.04-.92-1.788L10.013.474c.214-.297.556-.474.92-.474h28.894c.92 0 1.456 1.04.92 1.788l-7.48 10.471c-1.07 1.498 0 3.579 1.842 3.579h11.377c.943 0 1.473 1.088.89 1.83L25.947 44.94z"/>
        </svg>
        <span class="nav-brand">PropSpace</span>
      </RouterLink>

      <!-- Desktop Nav -->
      <nav class="nav-links">
        <RouterLink to="/" class="nav-link" :class="{ active: isActive('customer.home') }">Home</RouterLink>
        <RouterLink to="/properties" class="nav-link" :class="{ active: isActive('customer.properties.index') }">Properties</RouterLink>
        <a href="/#why-us" class="nav-link">About</a>
        <a href="/#footer" class="nav-link">Contact</a>
        <RouterLink to="/profile" class="nav-link" :class="{ active: isActive('customer.profile') }">Account</RouterLink>
      </nav>

      <!-- Desktop Auth + CTA -->
      <div class="nav-actions">
        <RouterLink to="/login" class="btn-ghost">Login</RouterLink>
        <RouterLink to="/register" class="btn-ghost">Register</RouterLink>
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
        <RouterLink to="/properties" class="mobile-link" @click="closeMobile">Properties</RouterLink>
        <a href="/#why-us" class="mobile-link" @click="closeMobile">About</a>
        <a href="/#footer" class="mobile-link" @click="closeMobile">Contact</a>
        <RouterLink to="/profile" class="mobile-link" @click="closeMobile">Account</RouterLink>
        <div class="mobile-divider"></div>
        <RouterLink to="/login" class="mobile-link" @click="closeMobile">Login</RouterLink>
        <RouterLink to="/register" class="mobile-link" @click="closeMobile">Register</RouterLink>
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
  border-bottom: 1px solid rgba(134, 76, 255, 0.1);
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

.nav-brand {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: -0.02em;
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
  color: #864CFF;
  background: rgba(134, 76, 255, 0.07);
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
  transition: color 0.2s, background 0.2s;
}

.btn-ghost:hover {
  color: #864CFF;
  background: rgba(134, 76, 255, 0.07);
}

.btn-primary-nav {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 1.1rem;
  background: linear-gradient(135deg, #864CFF, #6B2FFF);
  color: #fff !important;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  border-radius: 8px;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 2px 12px rgba(134, 76, 255, 0.3);
}

.btn-primary-nav:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(134, 76, 255, 0.4);
  opacity: 0.95;
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
  background: #1a1a2e;
  border-radius: 2px;
  transition: transform 0.3s, opacity 0.3s;
}

.hamburger span.open:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger span.open:nth-child(2) { opacity: 0; }
.hamburger span.open:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile menu */
.mobile-menu {
  background: #fff;
  border-top: 1px solid rgba(134, 76, 255, 0.08);
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
  background: rgba(134, 76, 255, 0.07);
  color: #864CFF;
}

.mobile-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}

.mobile-cta {
  display: block;
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #864CFF, #6B2FFF);
  color: #fff !important;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 12px rgba(134, 76, 255, 0.3);
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
