<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'

const store = usePurchaseRequestsStore()

onMounted(async () => {
  await store.fetchCustomerPurchaseRequests()
})

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function badgeClass(status) {
  const s = (status || '').toLowerCase()
  if (s === 'approved') return 'sk-badge-active'
  if (s === 'pending')  return 'sk-badge-pending'
  return 'sk-badge-rejected'
}

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
      <RouterLink to="/properties" class="sk-btn sk-btn-primary" style="display: inline-block; margin-top: 1rem;">Browse Properties</RouterLink>
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
            <td>{{ req.unit?.property?.name || req.property?.name || '—' }}</td>
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

<style scoped>
.dash-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 1rem;
  margin-bottom: 1.75rem;
}

.stat-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 1.25rem;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1a1a2e;
  line-height: 1;
}

.stat-title {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

.stat-card-green .stat-value { color: #059669; }
.stat-card-amber .stat-value { color: #d97706; }
.stat-card-red   .stat-value { color: #dc2626; }

.empty-box {
  text-align: center;
  padding: 3rem 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.empty-box-error {
  background: #fef2f2;
  border-color: #fecaca;
}

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.empty-box h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}

.empty-box p {
  color: #64748b;
  font-size: 0.9rem;
  max-width: 380px;
  margin: 0 auto;
}

.btn-sm {
  padding: 0.3rem 0.7rem;
  font-size: 0.8rem;
}

.skel-line {
  height: 14px;
  width: 80%;
  background: #e2e8f0;
  border-radius: 4px;
  animation: skel-pulse 1.5s ease-in-out infinite;
}

@keyframes skel-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
