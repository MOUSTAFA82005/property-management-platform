<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="owner-layout">
    <aside class="owner-sidebar">
      <RouterLink to="/owner/dashboard" class="owner-sidebar-logo">
        <span class="owner-sidebar-logo-name">PropSpace Owner</span>
      </RouterLink>

      <div class="owner-sidebar-label">Main Menu</div>
      <RouterLink to="/owner/dashboard" class="owner-nav-link">Dashboard</RouterLink>
      <RouterLink to="/owner/properties" class="owner-nav-link">Properties</RouterLink>
      <RouterLink to="/owner/units" class="owner-nav-link">Units</RouterLink>

      <div class="owner-sidebar-label">Management</div>
      <RouterLink to="/owner/customers" class="owner-nav-link">Customers</RouterLink>
      <RouterLink to="/owner/purchase-requests" class="owner-nav-link">Purchase Requests</RouterLink>
      
      <div class="owner-sidebar-label">Financials</div>
      <RouterLink to="/owner/contracts" class="owner-nav-link">Contracts</RouterLink>
      <RouterLink to="/owner/payments" class="owner-nav-link">Payments</RouterLink>

      <div class="owner-sidebar-bottom">
        <RouterLink to="/" class="owner-nav-link" style="color: #ef4444; padding: 0;">Logout (Demo)</RouterLink>
      </div>
    </aside>

    <main class="owner-content">
      <div class="owner-topbar">
        <div class="owner-topbar-title">Owner Area</div>
        <div class="owner-topbar-user">
          Owner Portal
          <div class="owner-avatar">O</div>
        </div>
      </div>
      <div style="padding: 2rem;">
        <RouterView />
      </div>
    </main>
  </div>
</template>
