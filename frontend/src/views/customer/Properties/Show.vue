<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'
import { usePropertiesStore } from '../../../stores/properties'
import { usePurchaseRequestsStore } from '../../../stores/purchaseRequests'
import PropertyCover from '../../../components/property/PropertyCover.vue'
import { formatMoney, humanStatus, statusBadgeClass } from '../../../utils/format'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const store = usePropertiesStore()
const requestsStore = usePurchaseRequestsStore()

const requesting = ref(null)
const feedback = ref('')
const requestError = ref('')

const property = computed(() => store.property)
const units = computed(() => property.value?.units || [])

async function load() {
  await store.fetchPublicProperty(route.params.id).catch(() => {})
}

async function requestUnit(unit) {
  requesting.value = unit.id
  feedback.value = ''
  requestError.value = ''

  try {
    await requestsStore.submitPurchaseRequest({ unit_id: unit.id })
    feedback.value = `Request submitted for unit ${unit.unit_number}.`
    // The unit list is part of the property payload, so re-read it rather
    // than guessing what the backend did.
    await load()
  } catch (e) {
    requestError.value = e.response?.data?.message || 'Could not submit that request.'
  } finally {
    requesting.value = null
  }
}

onMounted(load)
</script>

<template>
  <div class="sk-page">
    <RouterLink to="/properties" class="sk-back">&larr; Back to Properties</RouterLink>

    <div v-if="store.loading" class="sk-detail">
      <div class="skel-line" style="width: 45%; height: 1.5rem;"></div>
      <div class="skel-line" style="width: 30%;"></div>
      <div class="skel-line" style="width: 80%;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error">
      <h3>Could not load this property</h3>
      <p>{{ store.error }}</p>
      <RouterLink to="/properties" class="sk-btn sk-btn-primary">
        Back to Properties
      </RouterLink>
    </div>

    <div v-else-if="property" class="sk-detail">
      <!-- Hero: the photograph leads, with the identity sitting on top of it
           so the page opens like a listing rather than a record. -->
      <div class="sk-hero">
        <PropertyCover
          variant="hero"
          :name="property.name"
          :type="property.property_type"
          :seed="property.id"
          :show-type="false"
        />

        <div class="sk-hero-overlay">
          <span class="sk-hero-type">{{ property.property_type }}</span>
          <h1 class="sk-hero-title">{{ property.name }}</h1>
          <p class="sk-hero-loc">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/>
              <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
            </svg>
            {{ property.address }}, {{ property.city }}
          </p>
        </div>
      </div>

      <div class="sk-detail-grid">
        <div class="sk-detail-item">
          <span class="sk-detail-label">Starting Rent</span>
          <span>{{ property.from_price ? formatMoney(property.from_price) + ' / month' : '—' }}</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Availability</span>
          <span>{{ property.available_units_count }} of {{ property.units_count }} units</span>
        </div>
        <div class="sk-detail-item">
          <span class="sk-detail-label">Buildings</span>
          <span>{{ property.buildings_count }}</span>
        </div>
      </div>

      <template v-if="property.description">
        <h3 class="sk-section-title">Description</h3>
        <p class="notes-text">{{ property.description }}</p>
      </template>

      <div class="sk-toolbar" style="margin-top: 2rem;">
        <h3 class="sk-section-title" style="margin: 0; border: none; padding: 0;">Units</h3>
      </div>

      <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
      <div v-if="requestError" class="sk-alert-error">{{ requestError }}</div>

      <div v-if="units.length === 0" class="empty-box">
        <div class="empty-icon">🚪</div>
        <h3>No units listed yet</h3>
        <p>This property has no units published at the moment.</p>
      </div>

      <div v-else class="sk-table-wrap">
        <table class="sk-table">
          <thead>
            <tr>
              <th>Unit</th>
              <th>Type</th>
              <th>Area</th>
              <th>Beds</th>
              <th>Rent</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="unit in units" :key="unit.id">
              <td><strong>{{ unit.unit_number }}</strong></td>
              <td>{{ unit.unit_type }}</td>
              <td>{{ unit.area ? unit.area + ' m²' : '—' }}</td>
              <td>{{ unit.bedrooms }}</td>
              <td>{{ formatMoney(unit.monthly_rent) }}</td>
              <td>
                <span class="sk-badge" :class="statusBadgeClass(unit.status)">{{ humanStatus(unit.status) }}</span>
              </td>
              <td class="sk-row-actions">
                <RouterLink :to="`/units/${unit.id}`" class="sk-btn sk-btn-secondary">View</RouterLink>
                <button
                  v-if="authStore.isCustomer() && unit.status === 'available'"
                  class="sk-btn sk-btn-primary"
                  :disabled="requesting === unit.id"
                  @click="requestUnit(unit)"
                >
                  {{ requesting === unit.id ? 'Sending...' : 'Request' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!authStore.isAuthenticated" style="margin-top: 2rem;">
        <RouterLink to="/login" class="sk-btn sk-btn-primary">Sign in to request a unit</RouterLink>
      </div>

      <div v-else-if="authStore.isOwner()" style="margin-top: 2rem;">
        <RouterLink to="/owner/properties" class="sk-btn sk-btn-secondary">Manage in Owner Portal</RouterLink>
      </div>
    </div>
  </div>
</template>
