<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'
import { useUnitsStore } from '../../../stores/units'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import { formatMoney, humanStatus, statusBadgeClass } from '../../../utils/format'

const route = useRoute()
const authStore = useAuthStore()
const store = useUnitsStore()
const requestsStore = usePurchaseRequestsStore()

const submitting = ref(false)
const feedback = ref('')
const requestError = ref('')

const unit = computed(() => store.unit)

async function load() {
  await store.fetchPublicUnit(route.params.id).catch(() => {})
}

async function requestUnit() {
  submitting.value = true
  feedback.value = ''
  requestError.value = ''

  try {
    await requestsStore.submitPurchaseRequest({ unit_id: unit.value.id })
    feedback.value = 'Your request has been submitted. You can track it under Purchase Requests.'
    await load()
  } catch (e) {
    requestError.value = e.response?.data?.message || 'Could not submit that request.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="sk-page">
    <RouterLink :to="unit?.property_id ? `/properties/${unit.property_id}` : '/properties'" class="sk-back">
      &larr; Back to Property
    </RouterLink>

    <div v-if="store.loading" class="sk-detail">
      <div class="skel-line" style="width: 40%; height: 1.5rem;"></div>
      <div class="skel-line" style="width: 60%;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error">
      <h3>Could not load this unit</h3>
      <p>{{ store.error }}</p>
      <RouterLink to="/properties" class="sk-btn sk-btn-primary">
        Browse Properties
      </RouterLink>
    </div>

    <div v-else-if="unit" class="sk-detail">
      <div class="sk-header">
        <h1>Unit {{ unit.unit_number }}</h1>
        <p>{{ unit.property_name }} &bull; {{ unit.building?.name }}</p>
      </div>

      <div class="sk-detail-img" style="height: 160px; font-size: 2rem;">🚪</div>

      <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
      <div v-if="requestError" class="sk-alert-error">{{ requestError }}</div>

      <div class="sk-detail-grid">
        <div class="sk-detail-item">
          <span class="sk-detail-label">Monthly Rent</span>
          <span>{{ formatMoney(unit.monthly_rent) }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Type</span>
          <span>{{ unit.unit_type }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Area</span>
          <span>{{ unit.area ? unit.area + ' m²' : '—' }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Floor</span>
          <span>{{ unit.floor }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Bedrooms</span>
          <span>{{ unit.bedrooms }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Bathrooms</span>
          <span>{{ unit.bathrooms }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Availability</span>
          <span class="sk-badge" :class="statusBadgeClass(unit.status)">{{ humanStatus(unit.status) }}</span>
        </div>
      </div>

      <div v-if="authStore.isCustomer() && unit.status === 'available'" style="margin-top: 2rem;">
        <button class="sk-btn sk-btn-primary" :disabled="submitting" @click="requestUnit">
          {{ submitting ? 'Sending request...' : 'Request this unit' }}
        </button>
      </div>

      <p v-else-if="authStore.isCustomer()" class="notes-text" style="margin-top: 2rem;">
        This unit is currently {{ humanStatus(unit.status).toLowerCase() }} and cannot be requested.
      </p>

      <div v-else-if="!authStore.isAuthenticated" style="margin-top: 2rem;">
        <RouterLink to="/login" class="sk-btn sk-btn-primary">Sign in to request this unit</RouterLink>
      </div>

      <div v-else-if="authStore.isOwner()" style="margin-top: 2rem;">
        <RouterLink to="/owner/units" class="sk-btn sk-btn-secondary">Manage in Owner Portal</RouterLink>
      </div>
    </div>
  </div>
</template>
