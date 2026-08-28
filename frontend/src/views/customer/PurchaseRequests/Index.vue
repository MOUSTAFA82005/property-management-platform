<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
import { formatDate, statusBadgeClass } from '../../../utils/format'

const store = usePurchaseRequestsStore()

onMounted(async () => {
  await store.fetchCustomerPurchaseRequests()
})

const badgeClass = (status) => statusBadgeClass(status)

const pendingCount  = computed(() => store.purchaseRequests.filter((r) => (r.status || '').toLowerCase() === 'pending').length)
const approvedCount = computed(() => store.purchaseRequests.filter((r) => (r.status || '').toLowerCase() === 'approved').length)
const rejectedCount = computed(() => store.purchaseRequests.filter((r) => ['rejected', 'cancelled'].includes((r.status || '').toLowerCase())).length)
</script>

<template>
  <CustomerDashboardLayout>
    <div class="sk-header">
      <h1>My Purchase Requests</h1>
      <p>Track the status of your property purchase inquiries.</p>
    </div>

    <!-- Summary -->
    <div v-if="!store.loading && !store.error && store.purchaseRequests.length > 0" class="dash-stats">
      <div class="stat-card">
        <div class="stat-value">{{ store.purchaseRequests.length }}</div>
        <div class="stat-title">Total Requests</div>
      </div>
      <div class="stat-card stat-card-amber">
        <div class="stat-value">{{ pendingCount }}</div>
        <div class="stat-title">Pending</div>
      </div>
      <div class="stat-card stat-card-green">
        <div class="stat-value">{{ approvedCount }}</div>
        <div class="stat-title">Approved</div>
      </div>
      <div class="stat-card stat-card-red">
        <div class="stat-value">{{ rejectedCount }}</div>
        <div class="stat-title">Rejected</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr><th>ID</th><th>Property</th><th>Unit</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr v-for="n in 4" :key="n">
            <td v-for="c in 5" :key="c"><div class="skel-line"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Error -->
    <div v-else-if="store.error" class="empty-box empty-box-error">
      <h3>Failed to load requests</h3>
      <p>{{ store.error }}</p>
      <button class="sk-btn sk-btn-primary" @click="store.fetchCustomerPurchaseRequests()">Retry</button>
    </div>

    <!-- Empty -->
    <div v-else-if="store.purchaseRequests.length === 0" class="empty-box">
      <div class="empty-icon">📋</div>
      <h3>No purchase requests yet</h3>
      <p>Browse available properties and submit a purchase request to get started.</p>
      <RouterLink to="/properties" class="sk-btn sk-btn-primary">Browse Properties</RouterLink>
    </div>

    <!-- Table -->
    <div v-else class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="req in store.purchaseRequests" :key="req.id">
            <td><strong>REQ-{{ String(req.id).padStart(4, '0') }}</strong></td>
            <td>{{ req.unit?.property_name || '—' }}</td>
            <td>{{ req.unit?.unit_number || '—' }}</td>
            <td>{{ formatDate(req.created_at) }}</td>
            <td>
              <span class="sk-badge" :class="badgeClass(req.status)">{{ req.status || 'N/A' }}</span>
            </td>
            <td>
              <RouterLink :to="`/purchase-requests/${req.id}`" class="sk-btn sk-btn-secondary btn-sm">View</RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </CustomerDashboardLayout>
</template>
