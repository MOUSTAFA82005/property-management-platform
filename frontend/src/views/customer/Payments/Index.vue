<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { usePaymentsStore } from '../../../stores/payments'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'

const paymentsStore = usePaymentsStore()

const searchQuery = ref('')

const statusFilter = ref('')

onMounted(async () => {
  await paymentsStore.fetchCustomerPayments()
})

const filteredPayments = computed(() => {
  let list = paymentsStore.payments
  if (statusFilter.value) {
    list = list.filter((p) => p.status === statusFilter.value)
  }
  if (!searchQuery.value.trim()) return list
  const q = searchQuery.value.toLowerCase()
  return list.filter((p) => {
    const ref = (p.reference || '').toLowerCase()
    const unit = (p.contract?.unit?.unit_number || '').toLowerCase()
    const method = (p.payment_method || '').toLowerCase()
    const status = (p.status || '').toLowerCase()
    return ref.includes(q) || unit.includes(q) || method.includes(q) || status.includes(q)
  })
})

const totalPayments = computed(() => paymentsStore.payments.length)
const paidCount = computed(() => paymentsStore.payments.filter((p) => p.status === 'paid').length)
const pendingCount = computed(() => paymentsStore.payments.filter((p) => p.status === 'pending').length)
const overdueCount = computed(() => paymentsStore.payments.filter((p) => p.status === 'overdue').length)

function formatCurrency(amount) {
  if (amount == null || isNaN(Number(amount))) return '—'
  return 'EGP ' + Number(amount).toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function statusBadge(status) {
  const map = {
    pending: 'sk-badge-pending',
    paid: 'sk-badge-paid',
    overdue: 'sk-badge-rejected',
    cancelled: 'sk-badge-rejected',
  }
  return map[status] || 'sk-badge-pending'
}
</script>

<template>
  <CustomerDashboardLayout>
    <div class="sk-header">
      <h1>My Payments</h1>
      <p>Review your payment history and upcoming installments.</p>
    </div>

    <!-- Summary Cards -->
    <div class="dash-stats">
      <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-value">{{ totalPayments }}</div>
        <div class="stat-title">Total Payments</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ paidCount }}</div>
        <div class="stat-title">Paid</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ pendingCount }}</div>
        <div class="stat-title">Pending</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value">{{ overdueCount }}</div>
        <div class="stat-title">Overdue</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="paymentsStore.loading" class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Contract</th>
            <th>Unit</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Paid Date</th>
            <th>Method</th>
            <th>Status</th>
            <th>Reference</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in 5" :key="n">
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
            <td><div class="skel-line"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Error -->
    <div v-else-if="paymentsStore.error" class="error-box">
      <p>Failed to load payments.</p>
      <p class="error-msg">{{ paymentsStore.error }}</p>
      <button class="sk-btn sk-btn-primary" @click="paymentsStore.fetchCustomerPayments()">Retry</button>
    </div>

    <!-- Empty -->
    <div v-else-if="paymentsStore.payments.length === 0" class="empty-box">
      <div class="empty-icon">💳</div>
      <h3>No payments yet</h3>
      <p>Your payment history will appear here once you have payments associated with your contracts.</p>
    </div>

    <!-- Table -->
    <template v-else>
      <!-- Toolbar -->
      <div class="dash-toolbar">
        <input
          v-model="searchQuery"
          type="text"
          class="sk-search"
          placeholder="Search reference, unit, method..."
        />
        <select v-model="statusFilter" class="sk-form-select toolbar-select">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="overdue">Overdue</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div v-if="filteredPayments.length === 0" class="empty-box">
        <h3>No payments match your search</h3>
        <p>Try adjusting your search or filter criteria.</p>
      </div>

      <div v-else class="sk-table-wrap">
        <table class="sk-table">
          <thead>
            <tr>
              <th>Payment ID</th>
              <th>Contract</th>
              <th>Unit</th>
              <th>Amount</th>
              <th>Due Date</th>
              <th>Paid Date</th>
              <th>Method</th>
              <th>Status</th>
              <th>Reference</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pay in filteredPayments" :key="pay.id">
              <td><strong>PAY-{{ String(pay.id).padStart(4, '0') }}</strong></td>
              <td>CTR-{{ String(pay.contract?.id ?? pay.contract_id).padStart(4, '0') }}</td>
              <td>{{ pay.contract?.unit?.unit_number || '—' }}</td>
              <td>{{ formatCurrency(pay.amount) }}</td>
              <td>{{ formatDate(pay.due_date) }}</td>
              <td>{{ formatDate(pay.paid_date) }}</td>
              <td>{{ pay.payment_method ? pay.payment_method.charAt(0).toUpperCase() + pay.payment_method.slice(1).replace('_', ' ') : '—' }}</td>
              <td>
                <span class="sk-badge" :class="statusBadge(pay.status)">{{ pay.status }}</span>
              </td>
              <td>{{ pay.reference || '—' }}</td>
              <td>
                <RouterLink :to="`/payments/${pay.id}`" class="sk-btn sk-btn-secondary btn-sm">View</RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </CustomerDashboardLayout>
</template>

<style scoped>
.dash-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  font-size: 1.5rem;
  margin-bottom: 0.75rem;
  background: #f8fafc;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
  line-height: 1;
}

.stat-title {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.dash-toolbar {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.toolbar-select {
  width: auto;
  min-width: 150px;
}

.sk-form-select.toolbar-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  background: #fff;
  color: #374151;
  outline: none;
}

.sk-form-select.toolbar-select:focus {
  border-color: #864CFF;
}

.btn-sm {
  padding: 0.3rem 0.7rem;
  font-size: 0.8rem;
}

.error-box {
  text-align: center;
  padding: 3rem 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
}

.error-box p {
  font-size: 1rem;
  color: #991b1b;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.error-box .error-msg {
  font-size: 0.85rem;
  color: #b91c1c;
  font-weight: 400;
  margin-bottom: 1rem;
}

.empty-box {
  text-align: center;
  padding: 3rem 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}

.empty-box h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
}

.empty-box p {
  color: #64748b;
  font-size: 0.95rem;
  max-width: 400px;
  margin: 0 auto;
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
