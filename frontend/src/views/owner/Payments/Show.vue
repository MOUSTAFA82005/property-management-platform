<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { usePaymentsStore } from '../../../stores/payments'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import EmptyState from '../../../components/ui/EmptyState.vue'
import { formatDate, formatMoney } from '../../../utils/format'

const route = useRoute()
const paymentsStore = usePaymentsStore()

const loading = ref(true)
const error = ref(null)

const formatCurrency = (amount) => formatMoney(amount, { withDecimals: true })

const METHOD_LABELS = {
  cash: 'Cash',
  bank_transfer: 'Bank Transfer',
  cheque: 'Cheque',
  credit_card: 'Credit Card',
  instapay: 'InstaPay',
  other: 'Other',
}

function methodLabel(method) {
  if (!method) return '—'
  return METHOD_LABELS[method] || method
}

function humanStatus(status) {
  if (!status) return 'Pending'
  return status.charAt(0).toUpperCase() + status.slice(1)
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
  <RouterLink to="/owner/payments" class="owner-back">
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Payments
  </RouterLink>

  <OwnerPageHeader title="Payment Details" subtitle="A single payment record and the contract behind it." />

  <!-- Loading -->
  <div v-if="loading" class="owner-card owner-loading" aria-busy="true">
    <div v-for="n in 5" :key="n" class="skel-line" style="height: 1.15rem; margin-bottom: .85rem;"></div>
  </div>

  <!-- Error -->
  <EmptyState v-else-if="error" tone="error" title="Could not load this payment" :message="error">
    <RouterLink to="/owner/payments" class="owner-btn owner-btn-primary">Back to Payments</RouterLink>
  </EmptyState>

  <!-- Detail -->
  <div v-else-if="paymentsStore.payment" class="owner-card owner-form-card">
    <div class="owner-card-head">
      <div>
        <h2>PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</h2>
        <p>{{ formatCurrency(paymentsStore.payment.amount) }} due {{ formatDate(paymentsStore.payment.due_date) }}</p>
      </div>
      <StatusBadge :status="humanStatus(paymentsStore.payment.status)" />
    </div>

    <div class="owner-card-body">
      <dl class="owner-facts">
        <div>
          <dt>Payment ID</dt>
          <dd>PAY-{{ String(paymentsStore.payment.id).padStart(4, '0') }}</dd>
        </div>
        <div>
          <dt>Amount</dt>
          <dd>{{ formatCurrency(paymentsStore.payment.amount) }}</dd>
        </div>
        <div>
          <dt>Due Date</dt>
          <dd>{{ formatDate(paymentsStore.payment.due_date) }}</dd>
        </div>
        <div>
          <dt>Paid Date</dt>
          <dd>{{ formatDate(paymentsStore.payment.paid_date) }}</dd>
        </div>
        <div>
          <dt>Payment Method</dt>
          <dd>{{ methodLabel(paymentsStore.payment.payment_method) }}</dd>
        </div>
        <div>
          <dt>Reference</dt>
          <dd>{{ paymentsStore.payment.reference || '—' }}</dd>
        </div>
        <div>
          <dt>Contract</dt>
          <dd v-if="paymentsStore.payment.contract">
            CTR-{{ String(paymentsStore.payment.contract.id).padStart(4, '0') }}
          </dd>
          <dd v-else>—</dd>
        </div>
        <div>
          <dt>Status</dt>
          <dd><StatusBadge :status="humanStatus(paymentsStore.payment.status)" /></dd>
        </div>
      </dl>

      <template v-if="paymentsStore.payment.notes">
        <h3 class="owner-subsection">Notes</h3>
        <p class="owner-note">{{ paymentsStore.payment.notes }}</p>
      </template>
    </div>
  </div>
</template>
