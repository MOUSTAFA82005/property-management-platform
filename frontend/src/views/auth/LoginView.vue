<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()

// TODO: Implement login for both owner and customer
// POST /api/auth/login → authStore.setAuth(user, token)
// Redirect owner to /owner/dashboard, customer to /
</script>

<template>
  <div><!-- TODO: Login form --></div>
</template>
