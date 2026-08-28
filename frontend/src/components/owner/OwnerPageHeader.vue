<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  title: String,
  subtitle: String,
  eyebrow: String,
  actionText: String,
  actionTo: String,
  actionIcon: { type: String, default: 'fa-solid fa-plus' },
})
</script>

<template>
  <div class="owner-page-head">
    <div class="owner-page-head-text">
      <div v-if="eyebrow" class="owner-eyebrow">{{ eyebrow }}</div>
      <h1>{{ title }}</h1>
      <p v-if="subtitle">{{ subtitle }}</p>
    </div>

    <div v-if="actionTo || $slots.actions" class="owner-page-head-actions">
      <slot name="actions" />
      <RouterLink v-if="actionTo" :to="actionTo" class="owner-btn owner-btn-primary">
        <i :class="actionIcon" aria-hidden="true"></i> {{ actionText }}
      </RouterLink>
    </div>
  </div>
</template>
