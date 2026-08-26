<script setup>
import { onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { usePaymentsStore } from '../../../stores/payments'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'

const route = useRoute()
const paymentsStore = usePaymentsStore()

onMounted(async () => {
  await paymentsStore.fetchCustomerPayment(route.params.id)
})

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
    <RouterLink to="/payments" class="sk-back">&larr; Back to Payments</RouterLink>

    <!-- Loading -->
    <div v-if="paymentsStore.loading" class="sk-detail">
      <div class="detail-grid">
        <div class="detail-item" v-for="n in 8" :key="n">
          <div class="skel-label"></div>
          <div class="skel-value"></div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="paymentsStore.error" class="error-box">
      <p>Failed to load payment details.</p>
      <p class="error-msg">{{ paymentsStore.error }}</p>
      <RouterLink to="/payments" class="sk-btn sk-btn-primary">Back to Payments</RouterLink>
    </div>

    <!-- Not found -->
    <div v-else-if="paymentsStore.payment === null && !paymentsStore.loading" class="empty-box">
      <div class="empty-icon">❌</div>
      <h3>Payment not found</h3>
      <p>The payment you're looking for doesn't exist or you don't have access.</p>
      <RouterLink to="/payments" class="sk-btn sk-btn-primary" style="margin-top: 1rem;">Back to Payments</RouterLink>
    </div>

    <!-- Detail -->
    <template v-else-if="paymentsStore.payment">
      <div class="sk-header">
        <h1>Payment PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</h1>
        <p>Payment details and related contract information.</p>
      </div>

      <div class="sk-detail">
        <h3 class="section-title">Payment Information</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Payment ID</label>
            <span>PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</span>
          </div>
          <div class="detail-item">
            <label>Amount</label>
            <span class="amount-value">{{ formatCurrency(paymentsStore.payment.amount) }}</span>
          </div>
          <div class="detail-item">
            <label>Due Date</label>
            <span>{{ formatDate(paymentsStore.payment.due_date) }}</span>
          </div>
          <div class="detail-item">
            <label>Paid Date</label>
            <span>{{ formatDate(paymentsStore.payment.paid_date) }}</span>
          </div>
          <div class="detail-item">
            <label>Payment Method</label>
            <span>{{ paymentsStore.payment.payment_method ? paymentsStore.payment.payment_method.charAt(0).toUpperCase() + paymentsStore.payment.payment_method.slice(1).replace('_', ' ') : '—' }}</span>
          </div>
          <div class="detail-item">
            <label>Status</label>
            <span><span class="sk-badge" :class="statusBadge(paymentsStore.payment.status)">{{ paymentsStore.payment.status }}</span></span>
          </div>
          <div class="detail-item">
            <label>Reference</label>
            <span>{{ paymentsStore.payment.reference || '—' }}</span>
          </div>
          <div class="detail-item">
            <label>Created</label>
            <span>{{ formatDate(paymentsStore.payment.created_at) }}</span>
          </div>
        </div>

        <div v-if="paymentsStore.payment.notes" class="detail-section">
          <h3 class="section-title">Notes</h3>
          <p class="notes-text">{{ paymentsStore.payment.notes }}</p>
        </div>
      </div>

      <!-- Contract Info -->
      <div v-if="paymentsStore.payment.contract" class="sk-detail" style="margin-top: 1.5rem;">
        <h3 class="section-title">Contract Information</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Contract ID</label>
            <span>CTR-{{ String(paymentsStore.payment.contract.id).padStart(4, '0') }}</span>
          </div>
          <div class="detail-item">
            <label>Contract Status</label>
            <span><span class="sk-badge" :class="paymentsStore.payment.contract.status === 'active' ? 'sk-badge-active' : 'sk-badge-rejected'">{{ paymentsStore.payment.contract.status }}</span></span>
          </div>
          <div class="detail-item">
            <label>Monthly Rent</label>
            <span>{{ formatCurrency(paymentsStore.payment.contract.monthly_rent) }}</span>
          </div>
          <div class="detail-item">
            <label>Security Deposit</label>
            <span>{{ formatCurrency(paymentsStore.payment.contract.security_deposit) }}</span>
          </div>
          <div class="detail-item">
            <label>Start Date</label>
            <span>{{ formatDate(paymentsStore.payment.contract.start_date) }}</span>
          </div>
          <div class="detail-item">
            <label>End Date</label>
            <span>{{ formatDate(paymentsStore.payment.contract.end_date) }}</span>
          </div>
        </div>
      </div>

      <!-- Unit Info -->
      <div v-if="paymentsStore.payment.contract?.unit" class="sk-detail" style="margin-top: 1.5rem;">
        <h3 class="section-title">Unit Information</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Unit Number</label>
            <span>{{ paymentsStore.payment.contract.unit.unit_number }}</span>
          </div>
          <div class="detail-item">
            <label>Unit Type</label>
            <span>{{ paymentsStore.payment.contract.unit.unit_type }}</span>
          </div>
          <div class="detail-item">
            <label>Floor</label>
            <span>{{ paymentsStore.payment.contract.unit.floor }}</span>
          </div>
          <div class="detail-item">
            <label>Area</label>
            <span>{{ paymentsStore.payment.contract.unit.area ? paymentsStore.payment.contract.unit.area + ' m²' : '—' }}</span>
          </div>
          <div class="detail-item">
            <label>Bedrooms</label>
            <span>{{ paymentsStore.payment.contract.unit.bedrooms }}</span>
          </div>
          <div class="detail-item">
            <label>Bathrooms</label>
            <span>{{ paymentsStore.payment.contract.unit.bathrooms }}</span>
          </div>
          <div class="detail-item">
            <label>Monthly Rent</label>
            <span>{{ formatCurrency(paymentsStore.payment.contract.unit.monthly_rent) }}</span>
          </div>
          <div class="detail-item">
            <label>Status</label>
            <span><span class="sk-badge" :class="paymentsStore.payment.contract.unit.status === 'occupied' ? 'sk-badge-active' : 'sk-badge-available'">{{ paymentsStore.payment.contract.unit.status }}</span></span>
          </div>
        </div>
      </div>
    </template>
  </CustomerDashboardLayout>
</template>

<style scoped>
.sk-back {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #864CFF;
  text-decoration: none;
  margin-bottom: 1.5rem;
}

.sk-back:hover {
  text-decoration: underline;
}

.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 1rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.detail-item label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item span {
  font-size: 0.95rem;
  font-weight: 600;
  color: #0f172a;
}

.amount-value {
  font-size: 1.15rem !important;
  font-weight: 800 !important;
  color: #864CFF !important;
}

.detail-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
}

.notes-text {
  font-size: 0.95rem;
  color: #475569;
  line-height: 1.6;
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
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

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
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
}

.skel-label {
  height: 12px;
  width: 60px;
  background: #e2e8f0;
  border-radius: 4px;
  margin-bottom: 0.5rem;
  animation: skel-pulse 1.5s ease-in-out infinite;
}

.skel-value {
  height: 18px;
  width: 120px;
  background: #e2e8f0;
  border-radius: 4px;
  animation: skel-pulse 1.5s ease-in-out infinite;
}

@keyframes skel-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
