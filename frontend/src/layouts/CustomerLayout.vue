<script setup>
import Navbar from '../components/customer/Navbar.vue'
import SiteFooter from '../components/customer/SiteFooter.vue'
</script>

<template>
  <div class="customer-layout">
    <Navbar />
    <main class="customer-main">
      <RouterView />
    </main>
    <SiteFooter />
  </div>
</template>

<style scoped>
.customer-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.customer-main {
  flex: 1;
}
</style>
