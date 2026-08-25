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
      TODO: Implement owner sidebar layout.
      Sidebar links:
        /owner/dashboard
        /owner/properties
        /owner/buildings
        /owner/units
        /owner/customers
        /owner/purchase-requests
        /owner/contracts
        /owner/payments
      Display authStore.user?.name in header.
      Logout button calls handleLogout().
    -->
    <RouterView />
  </div>
</template>
