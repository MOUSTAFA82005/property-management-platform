<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import { useBuildingsStore } from '../../../stores/buildings'

const route = useRoute()
const store = useBuildingsStore()

const search = ref('')
const propertyId = ref(route.query.property_id || '')
const confirmDelete = ref(null)
const busy = ref(false)
const feedback = ref('')
const actionError = ref('')
let timer = null

async function load(page = 1) {
  await store.fetchBuildings({
    page,
    search: search.value || undefined,
    property_id: propertyId.value || undefined,
  }).catch(() => {})
}

watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 350)
})

async function remove() {
  busy.value = true
  feedback.value = ''
  actionError.value = ''
  try {
    await store.deleteBuilding(confirmDelete.value.id)
    feedback.value = `${confirmDelete.value.name} was deleted.`
    confirmDelete.value = null
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Could not delete that building.'
    confirmDelete.value = null
  } finally {
    busy.value = false
  }
}

onMounted(() => load())
</script>

<template>
  <OwnerPageHeader
    title="Buildings"
    subtitle="Buildings group the units inside each of your properties."
    action-text="Add Building"
    action-to="/owner/buildings/create"
  />

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

  <div class="owner-card">
    <div class="owner-card-head">
      <div class="owner-search-row">
        <input v-model="search" class="owner-search" placeholder="Search buildings..." />
      </div>
      <span v-if="store.meta">{{ store.meta.total }} buildings</span>
    </div>

    <div v-if="store.loading" style="padding: 1.5rem;">
      <div v-for="n in 3" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error" style="margin: 1.5rem;">
      <h3>Could not load your buildings</h3>
      <p>{{ store.error }}</p>
      <button class="owner-btn owner-btn-primary" style="margin-top: 1rem;" @click="load()">Retry</button>
    </div>

    <div v-else-if="store.buildings.length === 0" class="empty-box" style="margin: 1.5rem;">
      <div class="empty-icon">🏗️</div>
      <h3>No buildings yet</h3>
      <p>Add a building to one of your properties before creating units.</p>
      <RouterLink to="/owner/buildings/create" class="owner-btn owner-btn-primary">
        Add Building
      </RouterLink>
    </div>

    <div v-else class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Building</th>
            <th>Property</th>
            <th>City</th>
            <th>Floors</th>
            <th>Units</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in store.buildings" :key="b.id">
            <td><strong>{{ b.name }}</strong></td>
            <td>{{ b.property?.name || '—' }}</td>
            <td>{{ b.property?.city || '—' }}</td>
            <td>{{ b.floors_count }}</td>
            <td>{{ b.units_count ?? 0 }}</td>
            <td>
              <RouterLink :to="`/owner/units?property_id=${b.property_id}`" class="owner-btn owner-btn-light">Units</RouterLink>
              <RouterLink :to="`/owner/buildings/${b.id}/edit`" class="owner-btn owner-btn-light">Edit</RouterLink>
              <button class="owner-btn owner-btn-danger" @click="confirmDelete = b">Delete</button>
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
      <h3>Delete {{ confirmDelete.name }}?</h3>
      <p>Buildings whose units are under contract or have purchase requests cannot be deleted.</p>
      <div class="owner-form-actions">
        <button class="owner-btn owner-btn-danger" :disabled="busy" @click="remove">
          {{ busy ? 'Deleting...' : 'Delete building' }}
        </button>
        <button class="owner-btn owner-btn-light" @click="confirmDelete = null">Cancel</button>
      </div>
    </div>
  </div>
</template>
