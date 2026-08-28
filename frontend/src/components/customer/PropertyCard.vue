<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  property: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div class="property-card">
    <!-- Image -->
    <div class="card-image">
      <div class="card-visual" :style="{ background: property.gradient }">
        <div class="card-skyline">
          <svg viewBox="0 0 280 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient :id="`bg-${property.id}`" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="property.colorDark"/>
                <stop offset="100%" :stop-color="property.colorDarker"/>
              </linearGradient>
            </defs>
            <!-- Mini skyline for the card -->
            <rect width="280" height="120" :fill="`url(#bg-${property.id})`"/>
            <!-- Buildings -->
            <rect x="10" y="55" width="30" height="68" fill="rgba(255,255,255,0.08)" rx="2"/>
            <rect x="14" y="62" width="7" height="8" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="24" y="62" width="7" height="8" fill="rgba(255,255,255,0.15)" rx="1"/>
            <rect x="14" y="76" width="7" height="8" fill="rgba(255,255,255,0.15)" rx="1"/>
            <rect x="24" y="76" width="7" height="8" fill="rgba(255,255,255,0.25)" rx="1"/>

            <rect x="48" y="30" width="50" height="93" fill="rgba(255,255,255,0.12)" rx="3"/>
            <rect x="54" y="40" width="9" height="11" fill="rgba(255,255,255,0.25)" rx="1"/>
            <rect x="67" y="40" width="9" height="11" fill="rgba(255,220,100,0.5)" rx="1"/>
            <rect x="80" y="40" width="9" height="11" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="54" y="57" width="9" height="11" fill="rgba(100,200,255,0.4)" rx="1"/>
            <rect x="80" y="57" width="9" height="11" fill="rgba(255,220,100,0.35)" rx="1"/>
            <rect x="54" y="74" width="9" height="11" fill="rgba(255,255,255,0.15)" rx="1"/>
            <rect x="67" y="74" width="9" height="11" fill="rgba(100,200,255,0.3)" rx="1"/>
            <rect x="61" y="98" width="14" height="25" fill="rgba(255,255,255,0.08)" rx="2"/>

            <rect x="106" y="15" width="70" height="108" fill="rgba(255,255,255,0.18)" rx="4"/>
            <rect x="113" y="26" width="11" height="13" fill="rgba(255,255,255,0.3)" rx="1"/>
            <rect x="129" y="26" width="11" height="13" fill="rgba(255,220,100,0.6)" rx="1"/>
            <rect x="145" y="26" width="11" height="13" fill="rgba(100,200,255,0.5)" rx="1"/>
            <rect x="161" y="26" width="11" height="13" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="113" y="46" width="11" height="13" fill="rgba(100,200,255,0.4)" rx="1"/>
            <rect x="145" y="46" width="11" height="13" fill="rgba(255,255,255,0.25)" rx="1"/>
            <rect x="161" y="46" width="11" height="13" fill="rgba(255,220,100,0.45)" rx="1"/>
            <rect x="113" y="66" width="11" height="13" fill="rgba(255,220,100,0.35)" rx="1"/>
            <rect x="129" y="66" width="11" height="13" fill="rgba(100,200,255,0.35)" rx="1"/>
            <rect x="161" y="66" width="11" height="13" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="127" y="90" width="26" height="33" fill="rgba(255,255,255,0.12)" rx="2"/>

            <rect x="185" y="40" width="45" height="83" fill="rgba(255,255,255,0.1)" rx="3"/>
            <rect x="192" y="49" width="8" height="10" fill="rgba(255,220,100,0.45)" rx="1"/>
            <rect x="204" y="49" width="8" height="10" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="216" y="49" width="8" height="10" fill="rgba(100,200,255,0.4)" rx="1"/>
            <rect x="192" y="65" width="8" height="10" fill="rgba(100,200,255,0.3)" rx="1"/>
            <rect x="216" y="65" width="8" height="10" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="192" y="81" width="8" height="10" fill="rgba(255,220,100,0.35)" rx="1"/>
            <rect x="204" y="81" width="8" height="10" fill="rgba(100,200,255,0.35)" rx="1"/>

            <rect x="238" y="60" width="32" height="63" fill="rgba(255,255,255,0.08)" rx="2"/>
            <rect x="244" y="68" width="8" height="9" fill="rgba(255,255,255,0.2)" rx="1"/>
            <rect x="255" y="68" width="8" height="9" fill="rgba(255,220,100,0.4)" rx="1"/>
            <rect x="244" y="82" width="8" height="9" fill="rgba(100,200,255,0.3)" rx="1"/>

            <!-- Ground glow -->
            <ellipse cx="140" cy="120" rx="140" ry="10" :fill="property.colorGlow"/>
          </svg>
        </div>
      </div>
      <div class="card-badge">{{ property.type }}</div>
    </div>

    <!-- Content -->
    <div class="card-body">
      <div class="card-location">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
          <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/>
          <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
        </svg>
        {{ property.location }}
      </div>

      <h3 class="card-name">{{ property.name }}</h3>

      <div class="card-meta">
        <span class="meta-item">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
          {{ property.bedrooms }} Beds
        </span>
        <span class="meta-dot"></span>
        <span class="meta-item">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 6a2 2 0 0 0-2 2v3h4V8a2 2 0 0 0-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M20 6a2 2 0 0 1 2 2v3h-4V8a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M2 11h20v2a8 8 0 0 1-8 8H10a8 8 0 0 1-8-8v-2z" stroke="currentColor" stroke-width="2"/></svg>
          {{ property.bathrooms }} Baths
        </span>
        <span class="meta-dot"></span>
        <span class="meta-item">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/></svg>
          {{ property.area }} m²
        </span>
      </div>

      <div class="card-footer">
        <div class="card-price">
          <span class="price-from">Starting from</span>
          <span class="price-value">{{ property.price }}</span>
        </div>
        <RouterLink :to="`/properties/${property.id}`" class="card-btn">
          View Details
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.property-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #f0f2f8;
  box-shadow: 0 2px 16px rgba(26, 26, 62, 0.06);
  transition: transform 0.28s ease, box-shadow 0.28s ease;
  display: flex;
  flex-direction: column;
}

.property-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(26, 26, 62, 0.13);
}

/* Card Image */
.card-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.card-visual {
  width: 100%;
  height: 100%;
  transition: transform 0.4s ease;
}

.property-card:hover .card-visual {
  transform: scale(1.04);
}

.card-skyline {
  width: 100%;
  height: 100%;
}

.card-skyline svg {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 0.3rem 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  color: #5B3FE0;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 100px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Card Body */
.card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-location {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 0.4rem;
}

.card-location svg {
  color: #5B3FE0;
  flex-shrink: 0;
}

.card-name {
  font-size: 1.05rem;
  font-weight: 700;
  color: #14141F;
  line-height: 1.3;
  margin: 0 0 0.75rem 0;
  letter-spacing: -0.01em;
}

.card-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.78rem;
  color: #64748b;
  font-weight: 500;
}

.meta-item svg {
  color: #94a3b8;
}

.meta-dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: #cbd5e1;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-top: auto;
}

.card-price {
  display: flex;
  flex-direction: column;
}

.price-from {
  font-size: 0.7rem;
  color: #94a3b8;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.price-value {
  font-size: 1rem;
  font-weight: 800;
  color: #14141F;
  letter-spacing: -0.01em;
}

.card-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.5rem 1rem;
  background: rgba(91, 63, 224, 0.08);
  color: #5B3FE0;
  font-size: 0.825rem;
  font-weight: 700;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s, transform 0.2s;
  white-space: nowrap;
}

.card-btn:hover {
  background: #5B3FE0;
  color: #fff;
  transform: translateX(2px);
}
</style>
