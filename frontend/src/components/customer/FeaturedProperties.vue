<script setup>
import { RouterLink } from 'vue-router'
import PropertyCard from './PropertyCard.vue'

const featuredProperties = [
  {
    id: 1,
    name: 'Modern Family Villa',
    location: 'New Cairo, Egypt',
    type: 'Villa',
    price: 'EGP 4,500,000',
    bedrooms: 4,
    bathrooms: 3,
    area: 320,
    gradient: 'linear-gradient(145deg, #1a1a3e, #2d1b69)',
    colorDark: '#1a1a3e',
    colorDarker: '#0d0826',
    colorGlow: 'rgba(134, 76, 255, 0.25)',
  },
  {
    id: 2,
    name: 'Luxury Sky Apartment',
    location: '6th of October, Egypt',
    type: 'Apartment',
    price: 'EGP 2,200,000',
    bedrooms: 3,
    bathrooms: 2,
    area: 185,
    gradient: 'linear-gradient(145deg, #0f2744, #1a3a6e)',
    colorDark: '#0f2744',
    colorDarker: '#071528',
    colorGlow: 'rgba(71, 191, 255, 0.25)',
  },
  {
    id: 3,
    name: 'Elegant Townhouse',
    location: 'Sheikh Zayed, Egypt',
    type: 'Townhouse',
    price: 'EGP 3,800,000',
    bedrooms: 5,
    bathrooms: 4,
    area: 280,
    gradient: 'linear-gradient(145deg, #1a2e1a, #2d4a2d)',
    colorDark: '#1a2e1a',
    colorDarker: '#0d1a0d',
    colorGlow: 'rgba(60, 180, 100, 0.2)',
  },
  {
    id: 4,
    name: 'Studio in the Heart of Cairo',
    location: 'Maadi, Cairo, Egypt',
    type: 'Studio',
    price: 'EGP 950,000',
    bedrooms: 1,
    bathrooms: 1,
    area: 65,
    gradient: 'linear-gradient(145deg, #2e1a1a, #4a1a2d)',
    colorDark: '#2e1a1a',
    colorDarker: '#1a0d14',
    colorGlow: 'rgba(220, 80, 120, 0.2)',
  },
  {
    id: 5,
    name: 'Waterfront Penthouse',
    location: 'North Coast, Egypt',
    type: 'Penthouse',
    price: 'EGP 8,500,000',
    bedrooms: 6,
    bathrooms: 5,
    area: 450,
    gradient: 'linear-gradient(145deg, #1a2a3e, #0f3a4a)',
    colorDark: '#1a2a3e',
    colorDarker: '#0d1a26',
    colorGlow: 'rgba(30, 160, 210, 0.25)',
  },
  {
    id: 6,
    name: 'Garden Compound Unit',
    location: 'Heliopolis, Cairo, Egypt',
    type: 'Apartment',
    price: 'EGP 1,750,000',
    bedrooms: 2,
    bathrooms: 2,
    area: 130,
    gradient: 'linear-gradient(145deg, #1e1a2e, #2e1a4a)',
    colorDark: '#1e1a2e',
    colorDarker: '#100d1e',
    colorGlow: 'rgba(134, 76, 255, 0.2)',
  },
]
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
      <div class="property-grid">
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
