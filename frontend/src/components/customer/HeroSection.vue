<script setup>
import { RouterLink } from 'vue-router'
import { HERO_IMAGE } from '../../utils/propertyImagery'
</script>

<template>
  <section class="hero-section">
    <!-- Background gradient blobs -->
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>

    <div class="hero-container">
      <!-- Left content -->
      <div class="hero-content ps-fade-up">
        <div class="hero-badge">
          <span class="badge-dot"></span>
          Premium Real Estate Platform
        </div>

        <h1 class="hero-headline">
          Find a Place You'll Love to
          <span class="hero-highlight">Call Home</span>
        </h1>

        <p class="hero-subtext">
          Discover properties, explore available units, and find the right place for your next move.
        </p>

        <div class="hero-ctas">
          <RouterLink to="/properties" class="btn-hero-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
            Explore Properties
          </RouterLink>
          <a href="#how-it-works" class="btn-hero-secondary">
            Learn More
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>

        <ul class="hero-points">
          <li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Listings published by verified owners
          </li>
          <li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Unit-level availability and pricing
          </li>
          <li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Requests, contracts and payments in one place
          </li>
        </ul>
      </div>

      <!-- Right visual -->
      <!-- Right visual: a real architectural photograph rather than an
           illustration, so the page reads as a property platform. -->
      <div class="hero-visual ps-fade-in" style="animation-delay: .12s;">
        <figure class="hero-frame">
          <img
            class="hero-photo"
            :src="HERO_IMAGE"
            alt="Contemporary two-storey villa lit at dusk, seen from its garden"
            width="1600"
            height="1067"
            fetchpriority="high"
            decoding="async"
          />
          <figcaption class="hero-caption">
            <span class="hero-caption-label">Now listing</span>
            <span class="hero-caption-text">Villas, apartments and compounds across Egypt</span>
          </figcaption>
        </figure>
      </div>
    </div>
  </section>
</template>
<style scoped>
.hero-section {
  position: relative;
  min-height: calc(100vh - 68px);
  display: flex;
  align-items: center;
  overflow: hidden;
  background: linear-gradient(160deg, #fafbff 0%, #f0eeff 40%, #fafbff 100%);
}

.hero-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  z-index: 0;
}

.hero-blob-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(91, 63, 224, 0.15), transparent);
  top: -100px;
  right: -100px;
}

.hero-blob-2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(71, 191, 255, 0.12), transparent);
  bottom: -80px;
  left: 0;
}

.hero-container {
  position: relative;
  z-index: 1;
  max-width: 1280px;
  margin: 0 auto;
  padding: 3rem 1.5rem 4rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3.5rem;
  align-items: center;
}

/* Badge */
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 1rem;
  background: rgba(91, 63, 224, 0.1);
  border: 1px solid rgba(91, 63, 224, 0.2);
  border-radius: 100px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #5B3FE0;
  margin-bottom: 1.5rem;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.badge-dot {
  width: 7px;
  height: 7px;
  background: #5B3FE0;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.85); }
}

/* Headline */
.hero-headline {
  font-size: clamp(2rem, 4vw, 3.25rem);
  font-weight: 800;
  line-height: 1.15;
  color: #14141F;
  letter-spacing: -0.03em;
  margin-bottom: 1.25rem;
}

.hero-highlight {
  background: linear-gradient(135deg, #5B3FE0, #47BFFF);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtext {
  font-size: 1.1rem;
  color: #64748b;
  line-height: 1.7;
  margin-bottom: 2rem;
  max-width: 480px;
}

/* CTA buttons */
.hero-ctas {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 2.5rem;
}

.btn-hero-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: linear-gradient(135deg, #5B3FE0, #3D279E);
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  text-decoration: none;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(91, 63, 224, 0.35);
  transition: transform 0.25s, box-shadow 0.25s;
}

.btn-hero-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(91, 63, 224, 0.45);
  color: #fff;
}

.btn-hero-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: #fff;
  color: #14141F;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  transition: border-color 0.2s, color 0.2s, box-shadow 0.2s;
}

.btn-hero-secondary:hover {
  border-color: #5B3FE0;
  color: #5B3FE0;
  box-shadow: 0 4px 16px rgba(91, 63, 224, 0.12);
}

/* Value points */
.hero-points {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.hero-points li {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.925rem;
  font-weight: 500;
  color: #4c4e66;
}

.hero-points svg {
  color: #5B3FE0;
  flex-shrink: 0;
}

/* Hero Visual */
.hero-visual {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-frame {
  position: relative;
  width: 100%;
  max-width: 560px;
  margin: 0;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 80px rgba(26, 26, 62, 0.34), 0 4px 20px rgba(91, 63, 224, 0.18);
}

.hero-photo {
  display: block;
  width: 100%;
  height: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
}

.hero-caption {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 2.5rem 1.25rem 1.1rem;
  background: linear-gradient(to top, rgba(14, 12, 30, 0.82), rgba(14, 12, 30, 0));
  color: #fff;
  text-align: left;
}

.hero-caption-label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #c9b8ff;
}

.hero-caption-text {
  font-size: 0.9rem;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 900px) {
  .hero-container {
    grid-template-columns: 1fr;
    gap: 3rem;
    padding: 3rem 1.5rem 2rem;
    text-align: center;
  }

  .hero-badge {
    justify-content: center;
  }

  .hero-subtext {
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
  }

  .hero-ctas {
    justify-content: center;
  }

  .hero-points {
    align-items: center;
    text-align: left;
  }

  .hero-visual {
    order: -1;
    padding: 0 1rem;
  }
}

@media (max-width: 480px) {
  .hero-points li {
    font-size: 0.85rem;
  }
}
</style>
