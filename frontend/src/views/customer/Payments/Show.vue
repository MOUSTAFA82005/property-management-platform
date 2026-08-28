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
            <span class="sk-detail-label">Payment ID</span>
            <span>PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Amount</span>
            <span class="amount-value">{{ formatCurrency(paymentsStore.payment.amount) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Due Date</span>
            <span>{{ formatDate(paymentsStore.payment.due_date) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Paid Date</span>
            <span>{{ formatDate(paymentsStore.payment.paid_date) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Payment Method</span>
            <span>{{ paymentsStore.payment.payment_method ? paymentsStore.payment.payment_method.charAt(0).toUpperCase() + paymentsStore.payment.payment_method.slice(1).replace('_', ' ') : '—' }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Status</span>
            <span><span class="sk-badge" :class="statusBadge(paymentsStore.payment.status)">{{ paymentsStore.payment.status }}</span></span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Reference</span>
            <span>{{ paymentsStore.payment.reference || '—' }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Created</span>
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
            <span class="sk-detail-label">Contract ID</span>
            <span>CTR-{{ String(paymentsStore.payment.contract.id).padStart(4, '0') }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Contract Status</span>
            <span><span class="sk-badge" :class="paymentsStore.payment.contract.status === 'active' ? 'sk-badge-active' : 'sk-badge-rejected'">{{ paymentsStore.payment.contract.status }}</span></span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Monthly Rent</span>
            <span>{{ formatCurrency(paymentsStore.payment.contract.monthly_rent) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Security Deposit</span>
            <span>{{ formatCurrency(paymentsStore.payment.contract.security_deposit) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Start Date</span>
            <span>{{ formatDate(paymentsStore.payment.contract.start_date) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">End Date</span>
            <span>{{ formatDate(paymentsStore.payment.contract.end_date) }}</span>
          </div>
        </div>
      </div>

      <!-- Unit Info -->
      <div v-if="paymentsStore.payment.contract?.unit" class="sk-detail" style="margin-top: 1.5rem;">
        <h3 class="section-title">Unit Information</h3>
        <div class="detail-grid">
          <div class="detail-item">
            <span class="sk-detail-label">Unit Number</span>
            <span>{{ paymentsStore.payment.contract.unit.unit_number }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Unit Type</span>
            <span>{{ paymentsStore.payment.contract.unit.unit_type }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Floor</span>
            <span>{{ paymentsStore.payment.contract.unit.floor }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Area</span>
            <span>{{ paymentsStore.payment.contract.unit.area ? paymentsStore.payment.contract.unit.area + ' m²' : '—' }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Bedrooms</span>
            <span>{{ paymentsStore.payment.contract.unit.bedrooms }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Bathrooms</span>
            <span>{{ paymentsStore.payment.contract.unit.bathrooms }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Monthly Rent</span>
            <span>{{ formatCurrency(paymentsStore.payment.contract.unit.monthly_rent) }}</span>
          </div>
          <div class="detail-item">
            <span class="sk-detail-label">Status</span>
            <span><span class="sk-badge" :class="paymentsStore.payment.contract.unit.status === 'occupied' ? 'sk-badge-active' : 'sk-badge-available'">{{ paymentsStore.payment.contract.unit.status }}</span></span>
          </div>
        </div>
      </div>
    </template>
  </CustomerDashboardLayout>
</template>
