<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { usePropertiesStore } from '../../../stores/properties'
import { formatMoney } from '../../../utils/format'

const store = usePropertiesStore()
const route = useRoute()
const router = useRouter()

// Seeded from the URL so a search started on the home page arrives intact
// and the filtered catalog stays shareable.
const search = ref(typeof route.query.search === 'string' ? route.query.search : '')
const propertyType = ref(typeof route.query.property_type === 'string' ? route.query.property_type : '')
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
  searchTimer = setTimeout(() => {
    const query = {}
    if (search.value) query.search = search.value
    if (propertyType.value) query.property_type = propertyType.value
    router.replace({ query })
    load(1)
  }, 350)
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
      <label class="sr-only" for="catalog-search">Search properties</label>
      <input id="catalog-search" v-model="search" type="search" class="sk-search" placeholder="Search by name, city or address..." />
      <label class="sr-only" for="catalog-type">Filter by property type</label>
      <select id="catalog-type" v-model="propertyType" class="sk-form-select sk-toolbar-select">
        <option value="">All types</option>
        <option value="Apartment Building">Apartment Building</option>
        <option value="Residential Compound">Residential Compound</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="sk-cards" aria-busy="true">
      <div v-for="n in 6" :key="n" class="sk-card">
        <div class="sk-card-img"></div>
        <div class="sk-card-body">
          <div class="skel-line" style="width: 70%; height: 1.1rem;"></div>
          <div class="skel-line" style="width: 45%;"></div>
          <div class="skel-line" style="width: 60%; margin-top: 1.25rem;"></div>
        </div>
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
        <article v-for="prop in store.properties" :key="prop.id" class="sk-card">
          <div class="sk-card-img" aria-hidden="true">🏢</div>

          <div class="sk-card-body">
            <h2 class="sk-card-title">{{ prop.name }}</h2>
            <p class="sk-card-meta">{{ prop.city }} &bull; {{ prop.property_type }}</p>

            <p class="sk-card-availability">
              <strong>{{ prop.available_units_count }}</strong> of {{ prop.units_count }} units available
            </p>

            <p class="sk-card-price">
              <small>{{ prop.from_price ? 'Starting from' : 'Pricing' }}</small>
              <template v-if="prop.from_price">{{ formatMoney(prop.from_price) }} / month</template>
              <template v-else>On request</template>
            </p>

            <RouterLink :to="`/properties/${prop.id}`" class="sk-btn sk-btn-primary sk-card-cta">
              View Details
            </RouterLink>
          </div>
        </article>
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
