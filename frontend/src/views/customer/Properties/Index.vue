<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { usePropertiesStore } from '../../../stores/properties'
import { formatMoney } from '../../../utils/format'

const store = usePropertiesStore()

const search = ref('')
const propertyType = ref('')
let searchTimer = null

async function load(page = 1) {
  await store.fetchPublicProperties({
    page,
    search: search.value || undefined,
    property_type: propertyType.value || undefined,
  }).catch(() => {})
}

// Search is served by the backend; debounce so typing doesn't spam the API.
watch([search, propertyType], () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(1), 350)
})

onMounted(() => load())
</script>

<template>
  <div class="sk-page">
    <div class="sk-header">
      <h1>Properties</h1>
      <p>Browse our available properties and find your next home.</p>
    </div>

    <div class="sk-toolbar">
      <input v-model="search" type="text" class="sk-search" placeholder="Search by name, city or address..." />
      <div class="sk-form-group" style="margin: 0;">
        <select v-model="propertyType" class="sk-form-select" style="width: 220px;">
          <option value="">All types</option>
          <option value="Apartment Building">Apartment Building</option>
          <option value="Residential Compound">Residential Compound</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="sk-cards">
      <div v-for="n in 3" :key="n" class="sk-card">
        <div class="sk-card-img"></div>
        <div class="skel-line" style="width: 70%;"></div>
        <div class="skel-line" style="width: 45%;"></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="store.error" class="empty-box empty-box-error">
      <h3>Could not load properties</h3>
      <p>{{ store.error }}</p>
      <button class="sk-btn sk-btn-primary" @click="load()">Try again</button>
    </div>

    <!-- Empty -->
    <div v-else-if="store.properties.length === 0" class="empty-box">
      <div class="empty-icon">🏢</div>
      <h3>No properties match your search</h3>
      <p>Try a different city or clear the filters.</p>
    </div>

    <!-- Results -->
    <template v-else>
      <div class="sk-cards">
        <div v-for="prop in store.properties" :key="prop.id" class="sk-card">
          <div class="sk-card-img">🏢</div>
          <div class="sk-card-title">{{ prop.name }}</div>
          <div class="sk-card-meta">{{ prop.city }} &bull; {{ prop.property_type }}</div>
          <div class="sk-card-price">
            <template v-if="prop.from_price">From {{ formatMoney(prop.from_price) }} / month</template>
            <template v-else>Price on request</template>
          </div>
          <div class="sk-card-meta" style="margin-bottom: 0.75rem;">
            {{ prop.available_units_count }} of {{ prop.units_count }} units available
          </div>
          <RouterLink :to="`/properties/${prop.id}`" class="sk-btn sk-btn-primary" style="width: 100%; justify-content: center;">
            View Details
          </RouterLink>
        </div>
      </div>

      <div v-if="store.meta && store.meta.last_page > 1" class="sk-pagination">
        <button class="sk-btn sk-btn-secondary" :disabled="store.meta.current_page <= 1" @click="load(store.meta.current_page - 1)">
          Previous
        </button>
        <span>Page {{ store.meta.current_page }} of {{ store.meta.last_page }} &bull; {{ store.meta.total }} properties</span>
        <button class="sk-btn sk-btn-secondary" :disabled="store.meta.current_page >= store.meta.last_page" @click="load(store.meta.current_page + 1)">
          Next
        </button>
      </div>
    </template>
  </div>
</template>
