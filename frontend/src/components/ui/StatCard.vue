<script setup>
/**
 * A single headline figure. Every value shown here is passed in from an API
 * response — the component never derives or estimates anything.
 */
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  hint: { type: String, default: '' },
  icon: { type: String, default: '' },
  tone: { type: String, default: 'brand' }, // brand | ok | warn | bad | info
})
</script>

<template>
  <div class="owner-card owner-stat" :class="`owner-stat-${tone}`">
    <div v-if="icon" class="stat-icon" aria-hidden="true"><i :class="icon"></i></div>
    <div class="owner-stat-label">{{ label }}</div>
    <div class="owner-stat-value">{{ value }}</div>
    <div v-if="hint" class="owner-stat-trend">{{ hint }}</div>
  </div>
</template>
