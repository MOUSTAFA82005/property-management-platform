<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { useUnitsStore, UNIT_STATUSES } from '../../../stores/units'
import { formatMoney, humanStatus } from '../../../utils/format'

const route = useRoute()
const store = useUnitsStore()

const search = ref('')
const status = ref('')
const propertyId = ref(route.query.property_id || '')
const confirmDelete = ref(null)
const busy = ref(false)
const feedback = ref('')
const actionError = ref('')
let timer = null

async function load(page = 1) {
  await store.fetchOwnerUnits({
    page,
    search: search.value || undefined,
    status: status.value || undefined,
    property_id: propertyId.value || undefined,
  }).catch(() => {})
}

watch([search, status], () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 350)
})

async function remove() {
  busy.value = true
  feedback.value = ''
  actionError.value = ''
  try {
    await store.deleteUnit(confirmDelete.value.id)
    feedback.value = `Unit ${confirmDelete.value.unit_number} was deleted.`
    confirmDelete.value = null
  } catch (e) {
    // 409 when contracts or purchase requests still reference the unit.
    actionError.value = e.response?.data?.message || 'Could not delete that unit.'
    confirmDelete.value = null
  } finally {
    busy.value = false
  }
}

onMounted(() => load())
</script>

<template>
  <OwnerPageHeader
    title="Units"
    subtitle="Track availability, pricing and occupancy for every unit."
    action-text="Add Unit"
    action-to="/owner/units/create"
  />

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

  <div class="owner-card">
    <div class="owner-card-head">
      <div class="owner-search-row">
        <input v-model="search" class="owner-search" placeholder="Search unit number or type..." />
        <select v-model="status" class="owner-select">
          <option value="">All statuses</option>
          <option v-for="s in UNIT_STATUSES" :key="s" :value="s">{{ humanStatus(s) }}</option>
        </select>
      </div>
      <span v-if="store.meta">{{ store.meta.total }} units</span>
    </div>

    <div v-if="store.loading" style="padding: 1.5rem;">
      <div v-for="n in 4" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error" style="margin: 1.5rem;">
      <h3>Could not load your units</h3>
      <p>{{ store.error }}</p>
      <button class="owner-btn owner-btn-primary" style="margin-top: 1rem;" @click="load()">Retry</button>
    </div>

    <div v-else-if="store.units.length === 0" class="empty-box" style="margin: 1.5rem;">
      <div class="empty-icon">🚪</div>
      <h3>No units found</h3>
      <p>Add a unit to one of your buildings to get started.</p>
      <RouterLink to="/owner/units/create" class="owner-btn owner-btn-primary">
        Add Unit
      </RouterLink>
    </div>

    <div v-else class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Property</th>
            <th>Building</th>
            <th>Type</th>
            <th>Area</th>
            <th>Monthly rent</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in store.units" :key="u.id">
            <td><strong>{{ u.unit_number }}</strong></td>
            <td>{{ u.property_name || '—' }}</td>
            <td>{{ u.building?.name || '—' }}</td>
            <td>{{ u.unit_type }}</td>
            <td>{{ u.area ? u.area + ' m²' : '—' }}</td>
            <td>{{ formatMoney(u.monthly_rent) }}</td>
            <td><StatusBadge :status="humanStatus(u.status)" /></td>
            <td>
              <RouterLink :to="`/owner/units/${u.id}/edit`" class="owner-btn owner-btn-light">Edit</RouterLink>
              <button class="owner-btn owner-btn-danger" @click="confirmDelete = u">Delete</button>
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

  <div v-if="confirmDelete" class="owner-modal-backdrop" @click.self="confirmDelete = null">
    <div class="owner-modal">
      <h3>Delete unit {{ confirmDelete.unit_number }}?</h3>
      <p>Units with contracts or purchase requests against them cannot be deleted.</p>
      <div class="owner-form-actions">
        <button class="owner-btn owner-btn-danger" :disabled="busy" @click="remove">
          {{ busy ? 'Deleting...' : 'Delete unit' }}
        </button>
        <button class="owner-btn owner-btn-light" @click="confirmDelete = null">Cancel</button>
      </div>
    </div>
  </div>
</template>
