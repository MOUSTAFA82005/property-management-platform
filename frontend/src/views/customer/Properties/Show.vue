<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'

const route     = useRoute()
const id        = route.params.id
const authStore = useAuthStore()

const placeholderUnits = [
  { id: 101, name: 'Unit A1', area: '120 sqm', beds: 2, status: 'Available' },
  { id: 102, name: 'Unit B2', area: '150 sqm', beds: 3, status: 'Sold' },
]
</script>

<template>
  <div class="sk-page">
    <RouterLink to="/properties" class="sk-back">&larr; Back to Properties</RouterLink>

    <div class="sk-detail">
      <div class="sk-detail-img">🏢</div>

      <div class="sk-header" style="border: none; padding: 0;">
        <h1>Luxury Property #{{ id }}</h1>
        <p>New Cairo, Egypt &bull; Villa</p>
      </div>

      <div class="sk-detail-grid">
        <div class="sk-detail-item">
          <label>Starting Price</label>
          <span>EGP 4,500,000</span>
        </div>
        <div class="sk-detail-item">
          <label>Status</label>
          <span class="sk-badge sk-badge-active">Active Listing</span>
        </div>
      </div>

      <h3 class="sk-section-title">Description</h3>
      <p style="color: #6b7280; font-size: 0.925rem; line-height: 1.6;">
        This property features state-of-the-art amenities, a wonderful view, and is located in a prime
        neighborhood with excellent schools and shopping centers nearby.
      </p>

      <div class="sk-toolbar" style="margin-top: 2rem;">
        <h3 class="sk-section-title" style="margin: 0; border: none; padding: 0;">Available Units</h3>
      </div>

      <!-- Units Table -->
      <div class="sk-table-wrap">
        <table class="sk-table">
          <thead>
            <tr>
              <th>Unit</th>
              <th>Area</th>
              <th>Beds</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="unit in placeholderUnits" :key="unit.id">
              <td><strong>{{ unit.name }}</strong></td>
              <td>{{ unit.area }}</td>
              <td>{{ unit.beds }}</td>
              <td>
                <span class="sk-badge" :class="unit.status === 'Available' ? 'sk-badge-available' : 'sk-badge-sold'">
                  {{ unit.status }}
                </span>
              </td>
              <td>
                <RouterLink :to="`/units/${unit.id}`" class="sk-btn sk-btn-secondary">View Unit</RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Request Purchase: visible only to authenticated customers -->
      <div v-if="authStore.isCustomer()" style="margin-top: 2rem;">
        <RouterLink to="/purchase-requests" class="sk-btn sk-btn-primary">Request Purchase</RouterLink>
      </div>

      <!-- Owner view note: owners browse only, management is in the Owner Portal -->
      <div v-else-if="authStore.isOwner()" style="margin-top: 2rem;">
        <RouterLink to="/owner/properties" class="sk-btn sk-btn-secondary">
          Manage in Owner Portal
        </RouterLink>
      </div>
    </div>
  </div>
</template>
