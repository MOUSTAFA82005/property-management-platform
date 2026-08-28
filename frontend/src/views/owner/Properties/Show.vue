<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../../components/owner/StatusBadge.vue'
import { usePropertiesStore } from '../../../stores/properties'
import { formatMoney, humanStatus } from '../../../utils/format'

const route = useRoute()
const store = usePropertiesStore()

const property = computed(() => store.property)
/**
 * The owner property endpoint returns each unit with the building it sits in.
 * Deriving the building list from that avoids a second request for data the
 * response already carries.
 */
const buildings = computed(() => {
  const embedded = property.value?.buildings
  if (Array.isArray(embedded) && embedded.length) return embedded

  const byId = new Map()

  for (const unit of property.value?.units || []) {
    const building = unit.building
    if (!building) continue
    if (!byId.has(building.id)) byId.set(building.id, { ...building, units_count: 0 })
    byId.get(building.id).units_count += 1
  }

  return [...byId.values()].sort((a, b) => a.name.localeCompare(b.name))
})

onMounted(() => {
  store.fetchOwnerProperty(route.params.id).catch(() => {})
})
</script>

<template>
  <RouterLink to="/owner/properties" class="owner-back">
    <i class="fa-solid fa-arrow-left"></i> Back to Properties
  </RouterLink>

  <div v-if="store.loading && !property" class="owner-card" style="padding: 2rem;">
    <div v-for="n in 4" :key="n" class="skel-line" style="height: 1.25rem; margin-bottom: .75rem;"></div>
  </div>

  <div v-else-if="store.error && !property" class="empty-box empty-box-error">
    <h3>Could not load this property</h3>
    <p>{{ store.error }}</p>
  </div>

  <template v-else-if="property">
    <OwnerPageHeader
      :title="property.name"
      subtitle="Property details and unit overview."
      action-text="Edit Property"
      action-icon="fa-solid fa-pen"
      :action-to="`/owner/properties/${property.id}/edit`"
    />

    <div class="owner-dashboard-grid">
      <div class="owner-card">
        <div class="owner-card-head">
          <h2>Property information</h2>
          <StatusBadge :status="humanStatus(property.status)" />
        </div>
        <div class="owner-form">
          <div class="owner-form-grid">
            <div class="owner-field"><label for="ro-properties-city">City</label><input id="ro-properties-city" class="owner-input" :value="property.city" readonly /></div>
            <div class="owner-field"><label for="ro-properties-type">Type</label><input id="ro-properties-type" class="owner-input" :value="property.property_type" readonly /></div>
            <div class="owner-field full"><label for="ro-properties-address">Address</label><input id="ro-properties-address" class="owner-input" :value="property.address" readonly /></div>
            <div class="owner-field"><label for="ro-properties-total-units">Total units</label><input id="ro-properties-total-units" class="owner-input" :value="property.units_count" readonly /></div>
            <div class="owner-field"><label for="ro-properties-available-units">Available units</label><input id="ro-properties-available-units" class="owner-input" :value="property.available_units_count" readonly /></div>
            <div class="owner-field"><label for="ro-properties-buildings">Buildings</label><input id="ro-properties-buildings" class="owner-input" :value="property.buildings_count" readonly /></div>
            <div class="owner-field"><label for="ro-properties-listing">Listing</label><input id="ro-properties-listing" class="owner-input" :value="property.is_published ? 'Published' : 'Draft'" readonly /></div>
            <div class="owner-field full" v-if="property.description">
              <label for="ro-properties-description">Description</label>
              <textarea id="ro-properties-description" class="owner-textarea" readonly>{{ property.description }}</textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="owner-card">
        <div class="owner-card-head">
          <h2>Buildings</h2>
          <RouterLink :to="`/owner/buildings?property_id=${property.id}`" class="owner-btn owner-btn-light">Manage</RouterLink>
        </div>

        <div v-if="buildings.length === 0" class="owner-empty">
          <h3>No buildings yet</h3>
          <p>Add a building before you can create units here.</p>
          <RouterLink to="/owner/buildings/create" class="owner-btn owner-btn-primary">
            Add Building
          </RouterLink>
        </div>

        <div v-else class="owner-list">
          <div v-for="b in buildings" :key="b.id" class="owner-list-item">
            <div class="owner-list-main">
              <div class="owner-mini-avatar"><i class="fa-solid fa-building"></i></div>
              <div>
                <b>{{ b.name }}</b>
                <small>{{ b.floors_count }} floors &bull; {{ b.units_count ?? (b.units || []).length }} units</small>
              </div>
            </div>
            <RouterLink :to="`/owner/buildings/${b.id}/edit`" class="owner-btn owner-btn-light">Edit</RouterLink>
          </div>
        </div>
      </div>
    </div>

    <div class="owner-card">
      <div class="owner-card-head">
        <h2>Units</h2>
        <RouterLink :to="`/owner/units?property_id=${property.id}`" class="owner-btn owner-btn-light">View all units</RouterLink>
      </div>

      <div v-if="!property.units || property.units.length === 0" class="owner-empty">
        <h3>No units yet</h3>
        <p>Units added to this property's buildings will appear here.</p>
      </div>

      <div v-else class="owner-table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th>Unit</th>
              <th>Type</th>
              <th>Beds</th>
              <th>Area</th>
              <th>Monthly rent</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in property.units" :key="u.id">
              <td><strong>{{ u.unit_number }}</strong></td>
              <td>{{ u.unit_type }}</td>
              <td>{{ u.bedrooms }}</td>
              <td>{{ u.area ? u.area + ' m²' : '—' }}</td>
              <td>{{ formatMoney(u.monthly_rent) }}</td>
              <td><StatusBadge :status="humanStatus(u.status)" /></td>
              <td><RouterLink :to="`/owner/units/${u.id}/edit`" class="owner-btn owner-btn-light">Edit</RouterLink></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
</template>
