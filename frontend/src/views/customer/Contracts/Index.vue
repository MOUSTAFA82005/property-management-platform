<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useContractsStore } from '../../../stores/contracts'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
import { formatDate, statusBadgeClass } from '../../../utils/format'

const contractsStore = useContractsStore()

onMounted(async () => {
  await contractsStore.fetchCustomerContracts()
})

const activeCount    = computed(() => contractsStore.contracts.filter((c) => ['active', 'signed'].includes((c.status || '').toLowerCase())).length)
const pendingCount   = computed(() => contractsStore.contracts.filter((c) => (c.status || '').toLowerCase() === 'pending').length)
const expiredCount   = computed(() => contractsStore.contracts.filter((c) => ['expired', 'cancelled', 'terminated'].includes((c.status || '').toLowerCase())).length)
</script>

<template>
  <CustomerDashboardLayout>
    <div class="sk-header">
      <h1>My Contracts</h1>
      <p>View and manage your signed property contracts.</p>
    </div>

    <!-- Summary Cards -->
    <div v-if="!contractsStore.loading && !contractsStore.error && contractsStore.contracts.length > 0" class="dash-stats">
      <div class="stat-card">
        <div class="stat-value">{{ contractsStore.contracts.length }}</div>
        <div class="stat-title">Total Contracts</div>
      </div>
      <div class="stat-card stat-card-green">
        <div class="stat-value">{{ activeCount }}</div>
        <div class="stat-title">Active</div>
      </div>
      <div class="stat-card stat-card-amber">
        <div class="stat-value">{{ pendingCount }}</div>
        <div class="stat-title">Pending</div>
      </div>
      <div class="stat-card stat-card-red">
        <div class="stat-value">{{ expiredCount }}</div>
        <div class="stat-title">Expired / Closed</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="contractsStore.loading" class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr><th>Contract ID</th><th>Property</th><th>Unit</th><th>Start Date</th><th>End Date</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="n in 4" :key="n">
            <td v-for="c in 7" :key="c"><div class="skel-line"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Error -->
    <div v-else-if="contractsStore.error" class="empty-box empty-box-error">
      <h3>Failed to load contracts</h3>
      <p>{{ contractsStore.error }}</p>
      <button class="sk-btn sk-btn-primary" @click="contractsStore.fetchCustomerContracts()">Retry</button>
    </div>

    <!-- Empty -->
    <div v-else-if="contractsStore.contracts.length === 0" class="empty-box">
      <div class="empty-icon">📄</div>
      <h3>No contracts yet</h3>
      <p>Your contracts will appear here once a purchase request is approved and a contract is created.</p>
      <RouterLink to="/purchase-requests" class="sk-btn sk-btn-primary">View Purchase Requests</RouterLink>
    </div>

    <!-- Table -->
    <div v-else class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Contract ID</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in contractsStore.contracts" :key="c.id">
            <td><strong>CTR-{{ String(c.id).padStart(4, '0') }}</strong></td>
            <td>{{ c.unit?.property_name || '—' }}</td>
            <td>{{ c.unit?.unit_number || '—' }}</td>
            <td>{{ formatDate(c.start_date) }}</td>
            <td>{{ formatDate(c.end_date) }}</td>
            <td>
              <span class="sk-badge" :class="statusBadgeClass(c.status)">{{ c.status || 'N/A' }}</span>
            </td>
            <td>
              <RouterLink :to="`/contracts/${c.id}`" class="sk-btn sk-btn-secondary btn-sm">View</RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </CustomerDashboardLayout>
</template>
