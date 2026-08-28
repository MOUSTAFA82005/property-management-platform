<script setup>
import { RouterLink } from 'vue-router'
import BrandLogo from '../BrandLogo.vue'
import { HERO_IMAGE } from '../../utils/propertyImagery'

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})

const points = [
  'Browse published properties and unit-level availability',
  'Send a purchase request and follow its progress',
  'Keep contracts and payments in one place',
]
</script>

<template>
  <div class="auth-shell">
    <aside class="auth-aside">
      <!-- A real property behind the panel, so signing in already feels like
           a real-estate platform. Decorative: the copy carries the meaning. -->
      <img class="auth-aside-photo" :src="HERO_IMAGE" alt="" aria-hidden="true" decoding="async" />

      <RouterLink to="/" class="auth-brand" aria-label="PropSpace home">
        <BrandLogo :size="34" tone="light" />
      </RouterLink>

      <div class="auth-aside-body">
        <h2 class="auth-aside-title">Property management, end to end.</h2>
        <ul class="auth-points">
          <li v-for="point in points" :key="point">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ point }}
          </li>
        </ul>
      </div>

      <p class="auth-aside-foot">Owners and customers share one workspace.</p>
    </aside>

    <main class="auth-main">
      <div class="auth-card">
        <RouterLink to="/" class="auth-back">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M19 12H5M11 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back to site
        </RouterLink>

        <h1 class="auth-title">{{ title }}</h1>
        <p v-if="subtitle" class="auth-subtitle">{{ subtitle }}</p>

        <slot />
      </div>
    </main>
  </div>
</template>
