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

/* The site navigation is fixed; reserve its height once, here, so every
   customer page starts below it instead of each view compensating. */
.customer-main {
  flex: 1;
  padding-top: 68px;
}
</style>
