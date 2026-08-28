<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useContractsStore } from '../../../stores/contracts'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
import { formatDate, formatMoney, humanStatus, statusBadgeClass } from '../../../utils/format'

const route = useRoute()
const store = useContractsStore()

const contract = computed(() => store.contract)
const payments = computed(() => contract.value?.payments || [])

onMounted(async () => {
  await store.fetchCustomerContract(route.params.id).catch(() => {})
})
</script>

<template>
  <CustomerDashboardLayout>
    <RouterLink to="/contracts" class="sk-back">&larr; Back to Contracts</RouterLink>

    <div v-if="store.loading && !contract" class="sk-detail">
      <div class="skel-line" style="width: 40%; height: 1.4rem;"></div>
      <div class="skel-line" style="width: 70%;"></div>
    </div>

    <div v-else-if="store.error && !contract" class="empty-box empty-box-error">
      <h3>Could not load this contract</h3>
      <p>{{ store.error }}</p>
    </div>

    <div v-else-if="contract">
      <div class="sk-header">
        <h1>Contract CTR-{{ String(contract.id).padStart(4, '0') }}</h1>
        <p>{{ contract.unit?.property_name || 'Property' }} &bull; Unit {{ contract.unit?.unit_number || '—' }}</p>
      </div>

      <div class="info-card">
        <div class="info-row">
          <span class="info-label">Status</span>
          <span class="info-value">
            <span class="sk-badge" :class="statusBadgeClass(contract.status)">{{ humanStatus(contract.status) }}</span>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Start date</span>
          <span class="info-value">{{ formatDate(contract.start_date) }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">End date</span>
          <span class="info-value">{{ formatDate(contract.end_date) }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Monthly rent</span>
          <span class="info-value">{{ formatMoney(contract.monthly_rent, { withDecimals: true }) }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Security deposit</span>
          <span class="info-value">{{ formatMoney(contract.security_deposit, { withDecimals: true }) }}</span>
        </div>
        <div class="info-row" v-if="contract.notes">
          <span class="info-label">Notes</span>
          <span class="info-value">{{ contract.notes }}</span>
        </div>
      </div>

      <h3 class="sk-section-title" style="margin-top: 2rem;">Payment schedule</h3>

      <div v-if="payments.length === 0" class="empty-box">
        <div class="empty-icon">💳</div>
        <h3>No payments recorded yet</h3>
        <p>Payments raised against this contract will appear here.</p>
      </div>

      <div v-else class="sk-table-wrap">
        <table class="sk-table">
          <thead>
            <tr>
              <th>Reference</th>
              <th>Amount</th>
              <th>Due</th>
              <th>Paid</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in payments" :key="payment.id">
              <td><strong>{{ payment.reference || '—' }}</strong></td>
              <td>{{ formatMoney(payment.amount, { withDecimals: true }) }}</td>
              <td>{{ formatDate(payment.due_date) }}</td>
              <td>{{ formatDate(payment.paid_date) }}</td>
              <td>
                <span class="sk-badge" :class="statusBadgeClass(payment.status)">{{ humanStatus(payment.status) }}</span>
              </td>
              <td>
                <RouterLink :to="`/payments/${payment.id}`" class="sk-btn sk-btn-secondary">View</RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CustomerDashboardLayout>
</template>
