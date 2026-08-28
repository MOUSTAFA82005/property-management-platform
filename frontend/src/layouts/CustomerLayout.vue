<script setup>
import Navbar from '../components/customer/Navbar.vue'
import SiteFooter from '../components/customer/SiteFooter.vue'
</script>

<template>
  <div class="customer-layout">
    <Navbar />
    <main class="customer-main">
      <!--
        A keyed wrapper with a CSS entrance animation, deliberately not a
        <Transition>: any Vue transition keeps the outgoing page mounted
        alongside the incoming one, so for a moment the document contains two
        pages' worth of content. Re-keying swaps them in a single patch and
        replays the animation, which looks the same and never doubles the DOM.
      -->
      <RouterView v-slot="{ Component, route: current }">
        <div :key="current.path" class="ps-route">
          <component :is="Component" />
        </div>
      </RouterView>
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
