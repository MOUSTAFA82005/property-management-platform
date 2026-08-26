<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="sk-account-tabs">
    <RouterLink to="/profile" class="sk-account-tab">My Profile</RouterLink>
    <RouterLink to="/purchase-requests" class="sk-account-tab">Purchase Requests</RouterLink>
    <RouterLink to="/contracts" class="sk-account-tab">Contracts</RouterLink>
    <RouterLink to="/payments" class="sk-account-tab">Payments</RouterLink>
    <a class="sk-account-tab sk-account-logout" style="cursor: pointer;" @click="handleLogout">Logout</a>
  </div>
</template>

<style scoped>
.sk-account-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 0.5rem;
}

.sk-account-tab {
  display: inline-block;
  padding: 0.6rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s;
}

.sk-account-tab:hover,
.sk-account-tab.router-link-exact-active {
  background: rgba(134, 76, 255, 0.08);
  color: #864CFF;
}

.sk-account-logout {
  margin-left: auto;
  color: #ef4444;
}
.sk-account-logout:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}
</style>
