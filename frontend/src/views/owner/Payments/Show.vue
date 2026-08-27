<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { usePaymentsStore } from '../../../stores/payments'

const route = useRoute()
const paymentsStore = usePaymentsStore()

const loading = ref(true)
const error = ref(null)

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatCurrency(amount) {
  if (amount == null || isNaN(amount)) return '—'
  const num = Number(amount)
  return 'EGP ' + num.toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function statusBadgeClass(status) {
  const map = {
    pending: 'sk-badge-pending',
    paid: 'sk-badge-paid',
    overdue: 'sk-badge-sold',
    cancelled: 'sk-badge-rejected',
  }
  return map[status] || 'sk-badge-pending'
}

onMounted(async () => {
  try {
    await paymentsStore.fetchOwnerPayment(route.params.id)
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load payment.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <RouterLink to="/owner/payments" class="sk-back">&larr; Back to Payments</RouterLink>

    <div class="sk-header">
      <h1>Payment Details</h1>
      <p>View payment record details.</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="sk-detail">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div v-for="n in 6" :key="n">
          <div style="height: 12px; width: 80px; background: #e5e7eb; border-radius: 4px; margin-bottom: 0.4rem;"></div>
          <div style="height: 18px; width: 140px; background: #e5e7eb; border-radius: 4px;"></div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" style="text-align: center; padding: 2rem 1rem;">
      <div style="font-weight: 600; color: #dc2626; margin-bottom: 0.5rem;">{{ error }}</div>
      <RouterLink to="/owner/payments" class="sk-btn sk-btn-primary">Back to Payments</RouterLink>
    </div>

    <!-- Detail -->
    <div v-else-if="paymentsStore.payment" class="sk-detail">
      <div class="sk-detail-grid">
        <div class="sk-detail-item">
          <label>Payment ID</label>
          <span>PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Status</label>
          <span><span class="sk-badge" :class="statusBadgeClass(paymentsStore.payment.status)">{{ paymentsStore.payment.status }}</span></span>
        </div>
        <div class="sk-detail-item">
          <label>Amount</label>
          <span>{{ formatCurrency(paymentsStore.payment.amount) }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Due Date</label>
          <span>{{ formatDate(paymentsStore.payment.due_date) }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Paid Date</label>
          <span>{{ formatDate(paymentsStore.payment.paid_date) }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Payment Method</label>
          <span>{{ paymentsStore.payment.payment_method || '—' }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Reference</label>
          <span>{{ paymentsStore.payment.reference || '—' }}</span>
        </div>
        <div class="sk-detail-item">
          <label>Contract</label>
          <span v-if="paymentsStore.payment.contract">CTR-{{ String(paymentsStore.payment.contract.id).padStart(4, '0') }}</span>
          <span v-else>—</span>
        </div>
      </div>

      <div v-if="paymentsStore.payment.notes" class="sk-section-title">Notes</div>
      <p v-if="paymentsStore.payment.notes" style="font-size: 0.9rem; color: #374151; line-height: 1.6;">{{ paymentsStore.payment.notes }}</p>
    </div>
  </div>
</template>
