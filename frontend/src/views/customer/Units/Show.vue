<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'

const route     = useRoute()
const id        = route.params.id
const authStore = useAuthStore()
</script>

<template>
  <div class="sk-page">
    <RouterLink to="/properties/1" class="sk-back">&larr; Back to Property</RouterLink>

    <div class="sk-detail">
      <div class="sk-header">
        <h1>Unit #{{ id }}</h1>
        <p>Luxury Property</p>
      </div>

      <div class="sk-detail-img" style="height: 160px; font-size: 2rem;">🚪</div>

      <div class="sk-detail-grid">
        <div class="sk-detail-item">
          <label>Price</label>
          <span>EGP 2,500,000</span>
        </div>
        <div class="sk-detail-item">
          <label>Area</label>
          <span>150 m&sup2;</span>
        </div>
        <div class="sk-detail-item">
          <label>Bedrooms</label>
          <span>3</span>
        </div>
        <div class="sk-detail-item">
          <label>Bathrooms</label>
          <span>2</span>
        </div>
        <div class="sk-detail-item">
          <label>Availability</label>
          <span class="sk-badge sk-badge-available">Available</span>
        </div>
      </div>

      <!-- Request Purchase: visible only to authenticated customers -->
      <div v-if="authStore.isCustomer()" style="margin-top: 2rem;">
        <RouterLink to="/purchase-requests" class="sk-btn sk-btn-primary">Request Purchase</RouterLink>
      </div>

      <!-- Owner view note -->
      <div v-else-if="authStore.isOwner()" style="margin-top: 2rem;">
        <RouterLink to="/owner/units" class="sk-btn sk-btn-secondary">
          Manage in Owner Portal
        </RouterLink>
      </div>
    </div>
  </div>
</template>
