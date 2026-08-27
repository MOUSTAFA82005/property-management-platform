<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../components/owner/StatusBadge.vue'
import RevenueChart from './RevenueChart/RevenueChart.vue'
import UnitsChart from './Units/UnitsChart.vue'
import { useAuthStore } from '../../stores/auth'
import { useDashboardStore } from '../../stores/dashboard'
import { formatDate, formatMoney, humanStatus } from '../../utils/format'

const authStore = useAuthStore()
const store = useDashboardStore()

const s = computed(() => store.stats)

/** Every figure here comes from GET /api/owner/dashboard — nothing is counted client-side. */
const cards = computed(() => {
  if (!s.value) return []

  return [
    { label: 'Properties', value: s.value.properties.total, hint: `${s.value.properties.published} published`, icon: 'fa-solid fa-building' },
    { label: 'Units', value: s.value.units.total, hint: `${s.value.units.available} available`, icon: 'fa-solid fa-door-open' },
    { label: 'Occupied Units', value: s.value.units.occupied, hint: `${s.value.units.reserved} reserved`, icon: 'fa-solid fa-key' },
    { label: 'Customers', value: s.value.customers.total, hint: `${s.value.contracts.active} active contracts`, icon: 'fa-solid fa-users' },
    { label: 'Pending Requests', value: s.value.purchase_requests.pending, hint: `${s.value.purchase_requests.total} in total`, icon: 'fa-solid fa-clock' },
    { label: 'Expected Rent', value: formatMoney(s.value.monthly_expected_rent), hint: 'per month, active leases', icon: 'fa-solid fa-sack-dollar' },
    { label: 'Collected', value: formatMoney(s.value.payments.collected_amount), hint: `${s.value.payments.paid_count} payments`, icon: 'fa-solid fa-circle-check' },
    { label: 'Overdue', value: formatMoney(s.value.payments.overdue_amount), hint: `${s.value.payments.overdue_count} payments`, icon: 'fa-solid fa-triangle-exclamation' },
  ]
})

const unitBreakdown = computed(() => (s.value ? s.value.units : null))
const propertyOverview = computed(() => s.value?.property_overview || [])
const recentPayments = computed(() => s.value?.recent_payments || [])
const recentRequests = computed(() => s.value?.recent_purchase_requests || [])

onMounted(() => {
  store.fetchDashboard().catch(() => {})
})
</script>

<template>
  <OwnerPageHeader
    :title="`Welcome back, ${authStore.user?.name || 'Owner'}`"
    subtitle="Here's what's happening across your property portfolio today."
  />

  <!-- Loading -->
  <div v-if="store.loading && !s" class="owner-stats">
    <div v-for="n in 4" :key="n" class="owner-card owner-stat">
      <div class="skel-line" style="width: 50%;"></div>
      <div class="skel-line" style="height: 1.75rem; width: 70%;"></div>
    </div>
  </div>

  <!-- Error -->
  <div v-else-if="store.error" class="empty-box empty-box-error">
    <h3>Could not load your dashboard</h3>
    <p>{{ store.error }}</p>
    <button class="owner-btn owner-btn-primary" style="margin-top: 1rem;" @click="store.fetchDashboard()">Retry</button>
  </div>

  <template v-else-if="s">
    <div class="owner-stats">
      <div v-for="card in cards" :key="card.label" class="owner-card owner-stat">
        <div class="stat-icon"><i :class="card.icon"></i></div>
        <div class="owner-stat-label">{{ card.label }}</div>
        <div class="owner-stat-value">{{ card.value }}</div>
        <div class="owner-stat-trend">{{ card.hint }}</div>
      </div>
    </div>

    <div class="owner-quick">
      <RouterLink to="/owner/properties/create"><i class="fa-solid fa-plus"></i> Add Property <span><i class="fa-solid fa-arrow-right"></i></span></RouterLink>
      <RouterLink to="/owner/buildings/create"><i class="fa-solid fa-plus"></i> Add Building <span><i class="fa-solid fa-arrow-right"></i></span></RouterLink>
      <RouterLink to="/owner/units/create"><i class="fa-solid fa-plus"></i> Add Unit <span><i class="fa-solid fa-arrow-right"></i></span></RouterLink>
      <RouterLink to="/owner/contracts/create"><i class="fa-solid fa-file-circle-plus"></i> Create Contract <span><i class="fa-solid fa-arrow-right"></i></span></RouterLink>
      <RouterLink to="/owner/purchase-requests"><i class="fa-solid fa-inbox"></i> View Requests <span><i class="fa-solid fa-arrow-right"></i></span></RouterLink>
    </div>

    <div class="owner-dashboard-grid">
      <RevenueChart :payments="s.payments" />
      <UnitsChart :units="unitBreakdown" />
    </div>

    <!-- Property overview -->
    <div class="owner-card">
      <div class="owner-card-head">
        <h2>Property overview</h2>
        <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="propertyOverview.length === 0" class="owner-empty">
        <h3>No properties yet</h3>
        <p>Add your first property to see it here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th>Property</th><th>City</th><th>Units</th><th>Available</th><th>Occupied</th><th>Reserved</th><th>Listing</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in propertyOverview" :key="p.id">
              <td><RouterLink :to="`/owner/properties/${p.id}`"><strong>{{ p.name }}</strong></RouterLink></td>
              <td>{{ p.city }}</td>
              <td>{{ p.units.total }}</td>
              <td>{{ p.units.available }}</td>
              <td>{{ p.units.occupied }}</td>
              <td>{{ p.units.reserved }}</td>
              <td><StatusBadge :status="p.is_published ? 'Published' : 'Draft'" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent payments -->
    <div class="owner-card">
      <div class="owner-card-head">
        <h2>Recent payments</h2>
        <RouterLink to="/owner/payments" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="recentPayments.length === 0" class="owner-empty">
        <h3>No payments recorded</h3>
        <p>Payments against your contracts will appear here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr><th>Reference</th><th>Customer</th><th>Unit</th><th>Amount</th><th>Due</th><th>Status</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in recentPayments" :key="p.id">
              <td><strong>{{ p.reference || '—' }}</strong></td>
              <td>{{ p.contract?.user?.name || '—' }}</td>
              <td>{{ p.contract?.unit?.unit_number || '—' }}</td>
              <td>{{ formatMoney(p.amount, { withDecimals: true }) }}</td>
              <td>{{ formatDate(p.due_date) }}</td>
              <td><StatusBadge :status="humanStatus(p.status)" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent purchase requests -->
    <div class="owner-card">
      <div class="owner-card-head">
        <h2>Recent purchase requests</h2>
        <RouterLink to="/owner/purchase-requests" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="recentRequests.length === 0" class="owner-empty">
        <h3>No purchase requests</h3>
        <p>Customer interest in your units will show up here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr><th>Request</th><th>Customer</th><th>Property</th><th>Unit</th><th>Submitted</th><th>Status</th></tr>
          </thead>
          <tbody>
            <tr v-for="r in recentRequests" :key="r.id">
              <td><RouterLink :to="`/owner/purchase-requests/${r.id}`"><strong>#{{ String(r.id).padStart(4, '0') }}</strong></RouterLink></td>
              <td>{{ r.customer?.name || '—' }}</td>
              <td>{{ r.unit?.property_name || '—' }}</td>
              <td>{{ r.unit?.unit_number || '—' }}</td>
              <td>{{ formatDate(r.created_at) }}</td>
              <td><StatusBadge :status="humanStatus(r.status)" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
</template>
