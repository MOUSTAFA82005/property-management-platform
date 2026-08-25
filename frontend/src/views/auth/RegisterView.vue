<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()

// TODO: Implement customer registration
// POST /api/auth/register → authStore.setAuth(user, token) → redirect to /
</script>

<template>
  <div><!-- TODO: Registration form (customer only) --></div>
</template>
