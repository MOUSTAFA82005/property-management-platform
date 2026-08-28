<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, default: 'Pending' }
})

const statusClass = computed(() =>
  `status-${props.status.toLowerCase().trim().replace(/\s+/g, '-')}`
)
</script>

<template>
  <span class="owner-status" :class="statusClass">
    {{ status }}
  </span>
</template>
