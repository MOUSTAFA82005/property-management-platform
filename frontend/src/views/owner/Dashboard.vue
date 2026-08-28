<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../components/owner/StatusBadge.vue'
import StatCard from '../../components/ui/StatCard.vue'
import EmptyState from '../../components/ui/EmptyState.vue'
import RevenueChart from './RevenueChart/RevenueChart.vue'
import UnitsChart from './Units/UnitsChart.vue'
import { useAuthStore } from '../../stores/auth'
import { useDashboardStore } from '../../stores/dashboard'
import { formatDate, formatMoney, humanStatus } from '../../utils/format'

const authStore = useAuthStore()
const store = useDashboardStore()

const s = computed(() => store.stats)

/**
 * Every figure here comes from GET /api/owner/dashboard — nothing is counted
 * client-side. The cards are split into what the owner *has* (portfolio) and
 * what the owner is *owed* (money), because those are two different questions.
 */
const portfolioCards = computed(() => {
  if (!s.value) return []

  return [
    { label: 'Properties', value: s.value.properties.total, hint: `${s.value.properties.published} published`, icon: 'fa-solid fa-building' },
    { label: 'Units', value: s.value.units.total, hint: `${s.value.units.available} available`, icon: 'fa-solid fa-door-open' },
    { label: 'Occupied Units', value: s.value.units.occupied, hint: `${s.value.units.reserved} reserved`, icon: 'fa-solid fa-key', tone: 'info' },
    { label: 'Customers', value: s.value.customers.total, hint: `${s.value.contracts.active} active contracts`, icon: 'fa-solid fa-users' },
    { label: 'Pending Requests', value: s.value.purchase_requests.pending, hint: `${s.value.purchase_requests.total} in total`, icon: 'fa-solid fa-inbox', tone: 'warn' },
  ]
})

const financeCards = computed(() => {
  if (!s.value) return []

  return [
    { label: 'Expected Rent', value: formatMoney(s.value.monthly_expected_rent), hint: 'per month, active leases', icon: 'fa-solid fa-sack-dollar' },
    { label: 'Collected', value: formatMoney(s.value.payments.collected_amount), hint: `${s.value.payments.paid_count} payments`, icon: 'fa-solid fa-circle-check', tone: 'ok' },
    { label: 'Overdue', value: formatMoney(s.value.payments.overdue_amount), hint: `${s.value.payments.overdue_count} payments`, icon: 'fa-solid fa-triangle-exclamation', tone: s.value.payments.overdue_count > 0 ? 'bad' : 'brand' },
  ]
})

const unitBreakdown = computed(() => (s.value ? s.value.units : null))
const revenueSeries = computed(() => s.value?.revenue_by_month || [])
const propertyOverview = computed(() => s.value?.property_overview || [])
const recentPayments = computed(() => s.value?.recent_payments || [])
const recentRequests = computed(() => s.value?.recent_purchase_requests || [])

const quickActions = [
  { to: '/owner/properties/create', label: 'Add Property', icon: 'fa-solid fa-plus' },
  { to: '/owner/buildings/create', label: 'Add Building', icon: 'fa-solid fa-plus' },
  { to: '/owner/units/create', label: 'Add Unit', icon: 'fa-solid fa-plus' },
  { to: '/owner/contracts/create', label: 'Create Contract', icon: 'fa-solid fa-file-circle-plus' },
  { to: '/owner/purchase-requests', label: 'View Requests', icon: 'fa-solid fa-inbox' },
]

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
  <div v-if="store.loading && !s" class="owner-stats" aria-busy="true">
    <div v-for="n in 4" :key="n" class="owner-card owner-stat">
      <div class="skel-line" style="width: 50%;"></div>
      <div class="skel-line" style="height: 1.75rem; width: 70%;"></div>
      <div class="skel-line" style="width: 35%;"></div>
    </div>
  </div>

  <!-- Error -->
  <EmptyState
    v-else-if="store.error"
    tone="error"
    title="Could not load your dashboard"
    :message="store.error"
  >
    <button class="owner-btn owner-btn-primary" @click="store.fetchDashboard()">Retry</button>
  </EmptyState>

  <template v-else-if="s">
    <h2 class="owner-section-label">Portfolio</h2>

    <div class="owner-stats owner-stats-5">
      <StatCard
        v-for="card in portfolioCards"
        :key="card.label"
        :label="card.label"
        :value="card.value"
        :hint="card.hint"
        :icon="card.icon"
        :tone="card.tone || 'brand'"
      />
    </div>

    <h2 class="owner-section-label">Money</h2>

    <div class="owner-stats owner-stats-3">
      <StatCard
        v-for="card in financeCards"
        :key="card.label"
        :label="card.label"
        :value="card.value"
        :hint="card.hint"
        :icon="card.icon"
        :tone="card.tone || 'brand'"
      />
    </div>

    <div class="owner-dashboard-grid">
      <RevenueChart :payments="s.payments" :series="revenueSeries" />
      <UnitsChart :units="unitBreakdown" />
    </div>

    <h2 class="owner-section-label">Quick actions</h2>

    <div class="owner-quick">
      <RouterLink v-for="action in quickActions" :key="action.to" :to="action.to">
        <i :class="action.icon" aria-hidden="true"></i>
        {{ action.label }}
        <span aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
      </RouterLink>
    </div>

    <!-- Property overview -->
    <div class="owner-card">
      <div class="owner-card-head">
        <div>
          <h2>Property overview</h2>
          <p>Unit mix and listing state for each property</p>
        </div>
        <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="propertyOverview.length === 0" class="owner-empty">
        <h3>No properties yet</h3>
        <p>Add your first property to see it here.</p>
        <RouterLink to="/owner/properties/create" class="owner-btn owner-btn-primary">Create your first property</RouterLink>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th scope="col">Property</th>
              <th scope="col">City</th>
              <th scope="col">Units</th>
              <th scope="col">Available</th>
              <th scope="col">Occupied</th>
              <th scope="col">Reserved</th>
              <th scope="col">Listing</th>
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
        <div>
          <h2>Recent payments</h2>
          <p>The five most recent payments due</p>
        </div>
        <RouterLink to="/owner/payments" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="recentPayments.length === 0" class="owner-empty">
        <h3>No payments recorded</h3>
        <p>Payments against your contracts will appear here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th scope="col">Reference</th>
              <th scope="col">Customer</th>
              <th scope="col">Unit</th>
              <th scope="col">Amount</th>
              <th scope="col">Due</th>
              <th scope="col">Status</th>
            </tr>
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
        <div>
          <h2>Recent purchase requests</h2>
          <p>Latest customer interest in your units</p>
        </div>
        <RouterLink to="/owner/purchase-requests" class="owner-btn owner-btn-light">View all</RouterLink>
      </div>

      <div v-if="recentRequests.length === 0" class="owner-empty">
        <h3>No purchase requests</h3>
        <p>Customer interest in your units will show up here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th scope="col">Request</th>
              <th scope="col">Customer</th>
              <th scope="col">Property</th>
              <th scope="col">Unit</th>
              <th scope="col">Submitted</th>
              <th scope="col">Status</th>
            </tr>
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
