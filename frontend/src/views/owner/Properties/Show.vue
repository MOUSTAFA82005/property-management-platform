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
const buildings = computed(() => property.value?.buildings || [])

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
            <div class="owner-field"><label>City</label><input class="owner-input" :value="property.city" readonly /></div>
            <div class="owner-field"><label>Type</label><input class="owner-input" :value="property.property_type" readonly /></div>
            <div class="owner-field full"><label>Address</label><input class="owner-input" :value="property.address" readonly /></div>
            <div class="owner-field"><label>Total units</label><input class="owner-input" :value="property.units_count" readonly /></div>
            <div class="owner-field"><label>Available units</label><input class="owner-input" :value="property.available_units_count" readonly /></div>
            <div class="owner-field"><label>Buildings</label><input class="owner-input" :value="property.buildings_count" readonly /></div>
            <div class="owner-field"><label>Listing</label><input class="owner-input" :value="property.is_published ? 'Published' : 'Draft'" readonly /></div>
            <div class="owner-field full" v-if="property.description">
              <label>Description</label>
              <textarea class="owner-textarea" readonly>{{ property.description }}</textarea>
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
          <RouterLink to="/owner/buildings/create" class="owner-btn owner-btn-primary" style="display: inline-block; margin-top: 1rem;">
            Add Building
          </RouterLink>
        </div>

        <div v-else class="owner-list">
          <div v-for="b in buildings" :key="b.id" class="owner-list-item">
            <div class="owner-list-main">
              <div class="owner-mini-avatar"><i class="fa-solid fa-building"></i></div>
              <div>
                <b>{{ b.name }}</b>
                <small>{{ b.floors_count }} floors &bull; {{ (b.units || []).length }} units</small>
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
