<script setup>
/**
 * One empty / error treatment for every API-driven surface, so a page with
 * no rows looks deliberate rather than broken.
 */
defineProps({
  icon: { type: String, default: '' },
  title: { type: String, required: true },
  message: { type: String, default: '' },
  tone: { type: String, default: 'empty' }, // 'empty' | 'error'
})
</script>

<template>
  <div class="empty-box" :class="{ 'empty-box-error': tone === 'error' }" role="status">
    <div v-if="icon" class="empty-icon" aria-hidden="true">{{ icon }}</div>
    <h3>{{ title }}</h3>
    <p v-if="message">{{ message }}</p>
    <slot />
  </div>
</template>
