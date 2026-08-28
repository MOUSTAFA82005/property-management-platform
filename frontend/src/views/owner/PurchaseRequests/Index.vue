<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import { formatDate, humanStatus } from '../../../utils/format'

const store = usePurchaseRequestsStore()

const search = ref('')
const status = ref('')
const busyId = ref(null)
const feedback = ref('')
const actionError = ref('')
let timer = null

async function load(page = 1) {
  await store.fetchOwnerPurchaseRequests({
    page,
    search: search.value || undefined,
    status: status.value || undefined,
  }).catch(() => {})
}

watch([search, status], () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 350)
})

async function decide(request, action) {
  busyId.value = request.id
  feedback.value = ''
  actionError.value = ''

  try {
    await (action === 'approve' ? store.approveRequest(request.id) : store.rejectRequest(request.id))
    feedback.value = `Request #${request.id} ${action === 'approve' ? 'approved' : 'rejected'}.`
    // Approving reserves the unit, so re-read rather than assuming.
    await load(store.meta?.current_page || 1)
  } catch (e) {
    actionError.value = e.response?.data?.message || `Could not ${action} that request.`
  } finally {
    busyId.value = null
  }
}

onMounted(() => load())
</script>

<template>
  <OwnerPageHeader title="Purchase Requests" subtitle="Review and manage customer requests across your properties." />

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

  <div class="owner-card">
    <div class="owner-card-head">
      <div class="owner-search-row">
        <input v-model="search" class="owner-search" placeholder="Search customer or unit..." />
        <select v-model="status" class="owner-select">
          <option value="">All statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <span v-if="store.meta">{{ store.meta.total }} requests</span>
    </div>

    <div v-if="store.loading" style="padding: 1.5rem;">
      <div v-for="n in 3" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error" style="margin: 1.5rem;">
      <h3>Could not load requests</h3>
      <p>{{ store.error }}</p>
      <button class="owner-btn owner-btn-primary" style="margin-top: 1rem;" @click="load()">Retry</button>
    </div>

    <div v-else-if="store.purchaseRequests.length === 0" class="empty-box" style="margin: 1.5rem;">
      <div class="empty-icon">📋</div>
      <h3>No purchase requests</h3>
      <p>Requests customers raise against your units will appear here.</p>
    </div>

    <div v-else class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Request</th>
            <th>Customer</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in store.purchaseRequests" :key="r.id">
            <td><strong>#{{ String(r.id).padStart(4, '0') }}</strong></td>
            <td>{{ r.customer?.name || '—' }}</td>
            <td>{{ r.unit?.property_name || '—' }}</td>
            <td>{{ r.unit?.unit_number || '—' }}</td>
            <td>{{ formatDate(r.created_at) }}</td>
            <td><StatusBadge :status="humanStatus(r.status)" /></td>
            <td>
              <RouterLink :to="`/owner/purchase-requests/${r.id}`" class="owner-btn owner-btn-light">Review</RouterLink>
              <template v-if="r.status === 'pending'">
                <button class="owner-btn owner-btn-success" :disabled="busyId === r.id" @click="decide(r, 'approve')">Approve</button>
                <button class="owner-btn owner-btn-danger" :disabled="busyId === r.id" @click="decide(r, 'reject')">Reject</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="store.meta && store.meta.last_page > 1" class="sk-pagination" style="padding: 1rem 1.5rem;">
      <button class="owner-btn owner-btn-light" :disabled="store.meta.current_page <= 1" @click="load(store.meta.current_page - 1)">Previous</button>
      <span>Page {{ store.meta.current_page }} of {{ store.meta.last_page }}</span>
      <button class="owner-btn owner-btn-light" :disabled="store.meta.current_page >= store.meta.last_page" @click="load(store.meta.current_page + 1)">Next</button>
    </div>
  </div>
</template>
