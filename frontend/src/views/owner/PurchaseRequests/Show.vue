<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import { formatDate, formatMoney, humanStatus } from '../../../utils/format'

const route = useRoute()
const store = usePurchaseRequestsStore()

const busy = ref(false)
const feedback = ref('')
const actionError = ref('')

const request = computed(() => store.purchaseRequest)
const isPending = computed(() => request.value?.status === 'pending')

async function load() {
  await store.fetchOwnerPurchaseRequest(route.params.id).catch(() => {})
}

async function decide(action) {
  busy.value = true
  feedback.value = ''
  actionError.value = ''

  try {
    await (action === 'approve' ? store.approveRequest(request.value.id) : store.rejectRequest(request.value.id))
    feedback.value = action === 'approve'
      ? 'Request approved. The unit is now reserved.'
      : 'Request rejected.'
    await load()
  } catch (e) {
    actionError.value = e.response?.data?.message || `Could not ${action} this request.`
  } finally {
    busy.value = false
  }
}

onMounted(load)
</script>

<template>
  <RouterLink to="/owner/purchase-requests" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Purchase Requests
  </RouterLink>

  <div v-if="store.loading && !request" class="owner-card" style="padding: 2rem;">
    <div v-for="n in 3" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
  </div>

  <div v-else-if="store.error && !request" class="empty-box empty-box-error">
    <h3>Could not load this request</h3>
    <p>{{ store.error }}</p>
  </div>

  <template v-else-if="request">
    <OwnerPageHeader
      :title="`Request #${String(request.id).padStart(4, '0')}`"
      subtitle="Review customer interest and take action."
    />

    <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
    <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

    <div class="owner-card owner-form-card">
      <div class="owner-card-head">
        <h2>Request details</h2>
        <StatusBadge :status="humanStatus(request.status)" />
      </div>

      <div class="owner-form">
        <div class="owner-form-grid">
          <div class="owner-field"><label>Customer</label><input class="owner-input" :value="request.customer?.name || '—'" readonly /></div>
          <div class="owner-field"><label>Email</label><input class="owner-input" :value="request.customer?.email || '—'" readonly /></div>
          <div class="owner-field"><label>Phone</label><input class="owner-input" :value="request.customer?.phone || '—'" readonly /></div>
          <div class="owner-field"><label>Submitted</label><input class="owner-input" :value="formatDate(request.created_at)" readonly /></div>
          <div class="owner-field"><label>Property</label><input class="owner-input" :value="request.unit?.property_name || '—'" readonly /></div>
          <div class="owner-field"><label>Unit</label><input class="owner-input" :value="request.unit?.unit_number || '—'" readonly /></div>
          <div class="owner-field"><label>Monthly rent</label><input class="owner-input" :value="formatMoney(request.unit?.monthly_rent)" readonly /></div>
          <div class="owner-field"><label>Unit status</label><input class="owner-input" :value="humanStatus(request.unit?.status)" readonly /></div>
          <div class="owner-field full" v-if="request.notes">
            <label>Customer message</label>
            <textarea class="owner-textarea" readonly>{{ request.notes }}</textarea>
          </div>
        </div>

        <div v-if="isPending" class="owner-form-actions">
          <button class="owner-btn owner-btn-success" :disabled="busy" @click="decide('approve')">
            <i class="fa-solid fa-check"></i> {{ busy ? 'Working...' : 'Approve Request' }}
          </button>
          <button class="owner-btn owner-btn-danger" :disabled="busy" @click="decide('reject')">
            <i class="fa-solid fa-xmark"></i> Reject
          </button>
        </div>

        <p v-else style="color: #64748b; font-size: .9rem; margin-top: 1rem;">
          This request is {{ humanStatus(request.status).toLowerCase() }} and can no longer be actioned.
        </p>
      </div>
    </div>
  </template>
</template>
