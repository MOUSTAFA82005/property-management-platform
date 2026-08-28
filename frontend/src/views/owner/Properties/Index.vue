<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { usePropertiesStore } from '../../../stores/properties'
import { formatMoney, humanStatus } from '../../../utils/format'

const store = usePropertiesStore()

const search = ref('')
const status = ref('')
const busyId = ref(null)
const feedback = ref('')
const actionError = ref('')
const confirmDelete = ref(null)
let timer = null

async function load(page = 1) {
  await store.fetchOwnerProperties({
    page,
    search: search.value || undefined,
    status: status.value || undefined,
  }).catch(() => {})
}

watch([search, status], () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 350)
})

async function togglePublished(property) {
  busyId.value = property.id
  feedback.value = ''
  actionError.value = ''

  // Work out the target state up front. Reading property.is_published after
  // the call reports the old value — the store swaps in a fresh record, so
  // this local reference still points at the pre-update object.
  const shouldPublish = !property.is_published

  try {
    await store.setPublished(property.id, shouldPublish)
    feedback.value = `${property.name} is now ${shouldPublish ? 'published' : 'unpublished'}.`
  } catch (e) {
    actionError.value = e.response?.data?.message || 'Could not change the publication status.'
  } finally {
    busyId.value = null
  }
}

async function remove() {
  const property = confirmDelete.value
  busyId.value = property.id
  feedback.value = ''
  actionError.value = ''
  try {
    await store.deleteProperty(property.id)
    feedback.value = `${property.name} was deleted.`
    confirmDelete.value = null
  } catch (e) {
    // The API refuses with 409 when contracts or requests still reference it.
    actionError.value = e.response?.data?.message || 'Could not delete that property.'
    confirmDelete.value = null
  } finally {
    busyId.value = null
  }
}

onMounted(() => load())
</script>

<template>
  <OwnerPageHeader
    title="Properties"
    subtitle="Manage your portfolio, listings and property information."
    action-text="Add Property"
    action-to="/owner/properties/create"
  />

  <div v-if="feedback" class="sk-alert-success">{{ feedback }}</div>
  <div v-if="actionError" class="sk-alert-error">{{ actionError }}</div>

  <div class="owner-card">
    <div class="owner-card-head">
      <div class="owner-search-row">
        <input v-model="search" class="owner-search" placeholder="Search name, city or address..." />
        <select v-model="status" class="owner-select">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <span v-if="store.meta">{{ store.meta.total }} properties</span>
    </div>

    <div v-if="store.loading" style="padding: 1.5rem;">
      <div v-for="n in 3" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: 0.75rem;"></div>
    </div>

    <div v-else-if="store.error" class="empty-box empty-box-error" style="margin: 1.5rem;">
      <h3>Could not load your properties</h3>
      <p>{{ store.error }}</p>
      <button class="owner-btn owner-btn-primary" style="margin-top: 1rem;" @click="load()">Retry</button>
    </div>

    <div v-else-if="store.properties.length === 0" class="empty-box" style="margin: 1.5rem;">
      <div class="empty-icon">🏢</div>
      <h3>No properties yet</h3>
      <p>Add your first property to start building your portfolio.</p>
      <RouterLink to="/owner/properties/create" class="owner-btn owner-btn-primary">
        Add Property
      </RouterLink>
    </div>

    <div v-else class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Property</th>
            <th>City</th>
            <th>Type</th>
            <th>Units</th>
            <th>From</th>
            <th>Status</th>
            <th>Listing</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in store.properties" :key="p.id">
            <td><strong>{{ p.name }}</strong></td>
            <td>{{ p.city }}</td>
            <td>{{ p.property_type }}</td>
            <td>{{ p.available_units_count }} / {{ p.units_count }}</td>
            <td>{{ p.from_price ? formatMoney(p.from_price) : '—' }}</td>
            <td><StatusBadge :status="humanStatus(p.status)" /></td>
            <td>
              <StatusBadge :status="p.is_published ? 'Published' : 'Draft'" />
            </td>
            <td>
              <RouterLink :to="`/owner/properties/${p.id}`" class="owner-btn owner-btn-light">View</RouterLink>
              <RouterLink :to="`/owner/properties/${p.id}/edit`" class="owner-btn owner-btn-light">Edit</RouterLink>
              <button class="owner-btn owner-btn-light" :disabled="busyId === p.id" @click="togglePublished(p)">
                {{ p.is_published ? 'Unpublish' : 'Publish' }}
              </button>
              <button class="owner-btn owner-btn-danger" :disabled="busyId === p.id" @click="confirmDelete = p">
                Delete
              </button>
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

  <!-- Delete confirmation -->
  <div v-if="confirmDelete" class="owner-modal-backdrop" @click.self="confirmDelete = null">
    <div class="owner-modal">
      <h3>Delete {{ confirmDelete.name }}?</h3>
      <p>This removes the property and its buildings. It cannot be undone.</p>
      <div class="owner-form-actions">
        <button class="owner-btn owner-btn-danger" :disabled="busyId" @click="remove">
          {{ busyId ? 'Deleting...' : 'Delete property' }}
        </button>
        <button class="owner-btn owner-btn-light" @click="confirmDelete = null">Cancel</button>
      </div>
    </div>
  </div>
</template>
