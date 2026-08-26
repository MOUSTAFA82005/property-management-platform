<script setup>
const benefits = [
  {
    id: 1,
    icon: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-.981-.103-1.94-.282-2.876l-.1.516z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Verified Properties',
    description: 'Browse properties listed and managed by verified owners with full confidence.',
    color: '#864CFF',
    bg: 'rgba(134, 76, 255, 0.08)',
  },
  {
    id: 2,
    icon: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Easy Process',
    description: 'Find a property and submit your request through a simple, streamlined process.',
    color: '#47BFFF',
    bg: 'rgba(71, 191, 255, 0.08)',
  },
  {
    id: 3,
    icon: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
      <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
    </svg>`,
    title: 'Secure Transactions',
    description: 'Keep your requests, contracts and payments organized safely in one place.',
    color: '#10B981',
    bg: 'rgba(16, 185, 129, 0.08)',
  },
  {
    id: 4,
    icon: `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Trusted Platform',
    description: 'A simple, transparent platform connecting property owners with customers.',
    color: '#F59E0B',
    bg: 'rgba(245, 158, 11, 0.08)',
  },
]
</script>

<template>
  <section class="why-section" id="why-us">
    <div class="why-container">
      <div class="section-header">
        <div class="section-tag">Why Choose Us</div>
        <h2 class="section-title">Everything You Need in One Place</h2>
        <p class="section-subtitle">
          We make finding and acquiring your dream property simple, safe, and transparent.
        </p>
      </div>

      <div class="benefits-grid">
        <div
          v-for="benefit in benefits"
          :key="benefit.id"
          class="benefit-card"
        >
          <div class="benefit-icon" :style="{ background: benefit.bg, color: benefit.color }" v-html="benefit.icon"></div>
          <h3 class="benefit-title">{{ benefit.title }}</h3>
          <p class="benefit-desc">{{ benefit.description }}</p>
          <div class="benefit-line" :style="{ background: benefit.color }"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.why-section {
  padding: 5rem 1.5rem;
  background: #fff;
}

.why-container {
  max-width: 1280px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 3.5rem;
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
.benefits-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

/* Card */
.benefit-card {
  position: relative;
  padding: 2rem 1.5rem 2.5rem;
  background: #f8fafc;
  border-radius: 16px;
  border: 1px solid #f0f2f8;
  transition: transform 0.28s ease, box-shadow 0.28s ease, background 0.28s ease;
  overflow: hidden;
}

.benefit-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(26, 26, 62, 0.09);
  background: #fff;
}

.benefit-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 14px;
  margin-bottom: 1.25rem;
  transition: transform 0.2s;
}

.benefit-card:hover .benefit-icon {
  transform: scale(1.08);
}

.benefit-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.6rem;
  letter-spacing: -0.01em;
}

.benefit-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.65;
  margin: 0;
}

.benefit-line {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  opacity: 0;
  transition: opacity 0.3s;
  border-radius: 0 0 16px 16px;
}

.benefit-card:hover .benefit-line {
  opacity: 0.7;
}

@media (max-width: 1024px) {
  .benefits-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .benefits-grid {
    grid-template-columns: 1fr;
  }

  .why-section {
    padding: 3.5rem 1rem;
  }
}
</style>
