<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
import { formatDate, formatMoney, humanStatus, statusBadgeClass } from '../../../utils/format'

const route = useRoute()
const store = usePurchaseRequestsStore()

const cancelling = ref(false)
const actionError = ref('')
const feedback = ref('')

const request = computed(() => store.purchaseRequest)
const canCancel = computed(() => ['pending', 'approved'].includes(request.value?.status))

async function load() {
  await store.fetchCustomerPurchaseRequest(route.params.id).catch(() => {})
}

async function cancel() {
  cancelling.value = true
  actionError.value = ''
  feedback.value = ''

  try {
    await store.cancelPurchaseRequest(request.value.id)
    feedback.value = 'Request cancelled.'
    // The backend may also have released the unit reservation, so re-read.
    await load()
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Could not cancel that request.'
  } finally {
    cancelling.value = false
  }
}

onMounted(load)
</script>

<template>
  <CustomerDashboardLayout>
    <RouterLink to="/purchase-requests" class="sk-back">&larr; Back to Purchase Requests</RouterLink>

    <div v-if="store.loading && !request" class="sk-detail">
      <div class="skel-line" style="width: 40%; height: 1.4rem;"></div>
      <div class="skel-line" style="width: 65%;"></div>
    </div>

    <div v-else-if="store.error && !request" class="empty-box empty-box-error">
      <h3>Could not load this request</h3>
      <p>{{ store.error }}</p>
    </div>

    <div v-else-if="request">
      <div class="sk-header">
        <h1>Request #{{ String(request.id).padStart(4, '0') }}</h1>
        <p>Submitted {{ formatDate(request.created_at) }}</p>
      </div>

      <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
      <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

      <div class="info-card">
        <div class="info-row">
          <span class="info-label">Status</span>
          <span class="info-value">
            <span class="sk-badge" :class="statusBadgeClass(request.status)">{{ humanStatus(request.status) }}</span>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Property</span>
          <span class="info-value">{{ request.unit?.property_name || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Unit</span>
          <span class="info-value">{{ request.unit?.unit_number || '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Unit status</span>
          <span class="info-value">
            <span v-if="request.unit" class="sk-badge" :class="statusBadgeClass(request.unit.status)">
              {{ humanStatus(request.unit.status) }}
            </span>
            <template v-else>—</template>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Monthly rent</span>
          <span class="info-value">{{ formatMoney(request.unit?.monthly_rent) }}</span>
        </div>
        <div class="info-row" v-if="request.notes">
          <span class="info-label">Notes</span>
          <span class="info-value">{{ request.notes }}</span>
        </div>
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
        <RouterLink v-if="request.unit_id" :to="`/units/${request.unit_id}`" class="sk-btn sk-btn-secondary">
          View unit
        </RouterLink>
        <button v-if="canCancel" class="sk-btn sk-btn-danger" :disabled="cancelling" @click="cancel">
          {{ cancelling ? 'Cancelling...' : 'Cancel request' }}
        </button>
      </div>
    </div>
  </CustomerDashboardLayout>
</template>
