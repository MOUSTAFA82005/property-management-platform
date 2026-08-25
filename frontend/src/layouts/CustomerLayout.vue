<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()

const handleLogout = async () => {
  // TODO: Call authStore.logout() then redirect to /login
}
</script>

<template>
  <div>
    <!--
      TODO: Implement customer navbar/layout.
      Nav links:
        / (Home)
        /properties
        /purchase-requests
        /contracts
        /payments
        /profile
      Display authStore.user?.name.
      Logout button calls handleLogout().
    -->
    <RouterView />
  </div>
</template>
