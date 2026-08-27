<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import PropertyCard from './PropertyCard.vue'
import { usePropertiesStore } from '../../stores/properties'
import { formatMoney } from '../../utils/format'

const store = usePropertiesStore()

/**
 * Card artwork only — the skyline illustration needs a colour set per card.
 * This is decoration, not business data, so it cycles independently of what
 * the API returns.
 */
const PALETTE = [
  { gradient: 'linear-gradient(145deg, #1a1a3e, #2d1b69)', colorDark: '#1a1a3e', colorDarker: '#0d0826', colorGlow: 'rgba(134, 76, 255, 0.25)' },
  { gradient: 'linear-gradient(145deg, #0f2744, #1a3a6e)', colorDark: '#0f2744', colorDarker: '#071528', colorGlow: 'rgba(71, 191, 255, 0.25)' },
  { gradient: 'linear-gradient(145deg, #1a2e1a, #2d4a2d)', colorDark: '#1a2e1a', colorDarker: '#0d1a0d', colorGlow: 'rgba(60, 180, 100, 0.2)' },
  { gradient: 'linear-gradient(145deg, #2e1a1a, #4a1a2d)', colorDark: '#2e1a1a', colorDarker: '#1a0d14', colorGlow: 'rgba(220, 80, 120, 0.2)' },
  { gradient: 'linear-gradient(145deg, #1a2a3e, #0f3a4a)', colorDark: '#1a2a3e', colorDarker: '#0d1a26', colorGlow: 'rgba(30, 160, 210, 0.25)' },
  { gradient: 'linear-gradient(145deg, #1e1a2e, #2e1a4a)', colorDark: '#1e1a2e', colorDarker: '#100d1e', colorGlow: 'rgba(134, 76, 255, 0.2)' },
]

/**
 * The card shows bed/bath/area figures, which live on units rather than on
 * the property. Use the cheapest available unit — the one `from_price`
 * refers to — so the headline figure and the specs describe the same unit.
 */
function representativeUnit(property) {
  const units = property.units || []
  const available = units.filter((u) => u.status === 'available')
  const pool = available.length ? available : units

  return pool.reduce(
    (cheapest, unit) =>
      !cheapest || Number(unit.monthly_rent) < Number(cheapest.monthly_rent) ? unit : cheapest,
    null,
  )
}

const featuredProperties = computed(() =>
  store.properties.map((property, index) => {
    const unit = representativeUnit(property)

    return {
      id: property.id,
      name: property.name,
      location: `${property.city}, Egypt`,
      type: property.property_type,
      price: property.from_price ? `${formatMoney(property.from_price)}/mo` : 'On request',
      bedrooms: unit?.bedrooms ?? 0,
      bathrooms: unit?.bathrooms ?? 0,
      area: unit?.area ? Math.round(Number(unit.area)) : '—',
      ...PALETTE[index % PALETTE.length],
    }
  }),
)

onMounted(() => {
  // Public endpoint: this works for signed-out visitors too.
  store.fetchPublicProperties({ per_page: 6 }).catch(() => {})
})
</script>

<template>
  <section class="featured-section" id="featured-properties">
    <div class="featured-container">
      <!-- Section header -->
      <div class="section-header">
        <div class="section-tag">Featured Properties</div>
        <h2 class="section-title">Properties You'll Love</h2>
        <p class="section-subtitle">
          Explore some of the best properties currently available.
        </p>
      </div>

      <!-- Grid -->
      <div v-if="store.loading && featuredProperties.length === 0" class="property-grid">
        <div v-for="n in 3" :key="n" class="featured-skeleton">
          <div class="skel-line" style="height: 150px; margin-bottom: 1rem;"></div>
          <div class="skel-line" style="width: 60%;"></div>
          <div class="skel-line" style="width: 40%;"></div>
        </div>
      </div>

      <p v-else-if="featuredProperties.length === 0" class="featured-empty">
        No properties are published just yet. Please check back soon.
      </p>

      <div v-else class="property-grid">
        <PropertyCard
          v-for="property in featuredProperties"
          :key="property.id"
          :property="property"
        />
      </div>

      <!-- View All -->
      <div class="view-all-wrap">
        <RouterLink to="/properties" class="btn-view-all">
          View All Properties
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.featured-skeleton {
  background: rgba(255, 255, 255, 0.04);
  border-radius: 16px;
  padding: 1rem;
}

.featured-empty {
  text-align: center;
  color: #94a3b8;
  font-size: 0.95rem;
  padding: 2rem 0;
}

.featured-section {
  padding: 5rem 1.5rem;
  background: #f8fafc;
}

.featured-container {
  max-width: 1280px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-tag {
  display: inline-block;
  padding: 0.3rem 1rem;
  background: rgba(134, 76, 255, 0.1);
  color: #864CFF;
  font-size: 0.78rem;
  font-weight: 700;
  border-radius: 100px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-bottom: 0.85rem;
}

.section-title {
  font-size: clamp(1.6rem, 3vw, 2.25rem);
  font-weight: 800;
  color: #1a1a2e;
  letter-spacing: -0.03em;
  margin-bottom: 0.75rem;
}

.section-subtitle {
  font-size: 1rem;
  color: #64748b;
  max-width: 520px;
  margin: 0 auto;
  line-height: 1.65;
}

/* Grid */
.property-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 3rem;
}

/* View All */
.view-all-wrap {
  text-align: center;
}

.btn-view-all {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  border: 2px solid #864CFF;
  color: #864CFF;
  font-size: 0.95rem;
  font-weight: 700;
  text-decoration: none;
  border-radius: 12px;
  transition: background 0.25s, color 0.25s, transform 0.2s;
}

.btn-view-all:hover {
  background: #864CFF;
  color: #fff;
  transform: translateY(-2px);
}

@media (max-width: 1024px) {
  .property-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .property-grid {
    grid-template-columns: 1fr;
  }

  .featured-section {
    padding: 3.5rem 1rem;
  }
}
</style>
