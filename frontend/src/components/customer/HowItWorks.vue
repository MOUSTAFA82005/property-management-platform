<script setup>
const steps = [
  {
    number: '01',
    title: 'Find a Property',
    description: 'Browse available properties and discover the one that fits your needs, budget, and lifestyle.',
    icon: `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/>
      <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>`,
  },
  {
    number: '02',
    title: 'Choose Your Unit',
    description: 'Explore available units within properties and review their details, pricing, and specifications.',
    icon: `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
      <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
      <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
      <path d="M14 17.5h7M17.5 14v7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>`,
  },
  {
    number: '03',
    title: 'Submit Your Request',
    description: 'Send your purchase request and follow the entire process from your personal account dashboard.',
    icon: `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M22 2L11 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M22 2L15 22l-4-9-9-4 20-7z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
  },
]
</script>

<template>
  <section class="how-section" id="how-it-works">
    <div class="how-container">
      <div class="section-header">
        <div class="section-tag">How It Works</div>
        <h2 class="section-title">Simple Steps to Your New Home</h2>
        <p class="section-subtitle">
          Getting started is easy. Follow these three steps to find and request your next property.
        </p>
      </div>

      <div class="steps-grid">
        <div v-for="(step, index) in steps" :key="step.number" class="step-item">
          <!-- Connector line -->
          <div v-if="index < steps.length - 1" class="step-connector"></div>

          <div class="step-top">
            <div class="step-number-wrap">
              <span class="step-num">{{ step.number }}</span>
            </div>
            <div class="step-icon-wrap" v-html="step.icon"></div>
          </div>

          <h3 class="step-title">{{ step.title }}</h3>
          <p class="step-desc">{{ step.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.how-section {
  padding: 5rem 1.5rem;
  background: linear-gradient(160deg, #14141F 0%, #2A1A6E 50%, #14141F 100%);
  position: relative;
  overflow: hidden;
}

.how-section::before {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(91, 63, 224, 0.2), transparent);
  top: -100px;
  right: -100px;
  pointer-events: none;
}

.how-section::after {
  content: '';
  position: absolute;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(71, 191, 255, 0.15), transparent);
  bottom: -80px;
  left: -60px;
  pointer-events: none;
}

.how-container {
  position: relative;
  z-index: 1;
  max-width: 1100px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 4rem;
}

.section-tag {
  display: inline-block;
  padding: 0.3rem 1rem;
  background: rgba(91, 63, 224, 0.25);
  color: #BEADFA;
  font-size: 0.78rem;
  font-weight: 700;
  border-radius: 100px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-bottom: 0.85rem;
  border: 1px solid rgba(91, 63, 224, 0.3);
}

.section-title {
  font-size: clamp(1.6rem, 3vw, 2.25rem);
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.03em;
  margin-bottom: 0.75rem;
}

.section-subtitle {
  font-size: 1rem;
  color: rgba(255,255,255,0.6);
  max-width: 500px;
  margin: 0 auto;
  line-height: 1.65;
}

/* Steps */
.steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  position: relative;
}

.step-item {
  position: relative;
  text-align: center;
  padding: 2rem 1.5rem 2.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  transition: background 0.3s, transform 0.3s, border-color 0.3s;
}

.step-item:hover {
  background: rgba(255, 255, 255, 0.09);
  transform: translateY(-6px);
  border-color: rgba(91, 63, 224, 0.4);
}

/* Connector line */
.step-connector {
  display: none; /* Handled via pseudo in parent for cleaner look */
}

.step-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.step-number-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #5B3FE0, #3D279E);
  box-shadow: 0 4px 16px rgba(91, 63, 224, 0.4);
}

.step-num {
  font-size: 0.9rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: 0.02em;
}

.step-icon-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.08);
  color: #BEADFA;
  transition: background 0.2s, color 0.2s;
}

.step-item:hover .step-icon-wrap {
  background: rgba(91, 63, 224, 0.2);
  color: #fff;
}

.step-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 0.75rem;
  letter-spacing: -0.01em;
}

.step-desc {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.58);
  line-height: 1.7;
  margin: 0;
}

@media (max-width: 768px) {
  .steps-grid {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }

  .how-section {
    padding: 3.5rem 1rem;
  }
}
</style>
