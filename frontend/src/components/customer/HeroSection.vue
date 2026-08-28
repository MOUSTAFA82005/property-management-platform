<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="hero-section">
    <!-- Background gradient blobs -->
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>

    <div class="hero-container">
      <!-- Left content -->
      <div class="hero-content">
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
      <div class="hero-visual">
        <div class="hero-illustration">
          <svg viewBox="0 0 520 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="hero-svg">
            <!-- Sky gradient background -->
            <defs>
              <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#1A1A34"/>
                <stop offset="60%" stop-color="#2A1A6E"/>
                <stop offset="100%" stop-color="#3D279E"/>
              </linearGradient>
              <linearGradient id="buildingGrad1" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#4A2FC2"/>
                <stop offset="100%" stop-color="#2A1A6E"/>
              </linearGradient>
              <linearGradient id="buildingGrad2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#5B3FE0"/>
                <stop offset="100%" stop-color="#4A2FC2"/>
              </linearGradient>
              <linearGradient id="buildingGrad3" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#3D279E"/>
                <stop offset="100%" stop-color="#191140"/>
              </linearGradient>
              <linearGradient id="groundGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#191140"/>
                <stop offset="100%" stop-color="#0E0A22"/>
              </linearGradient>
              <linearGradient id="glowGrad" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#5B3FE0" stop-opacity="0"/>
                <stop offset="50%" stop-color="#5B3FE0" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="#5B3FE0" stop-opacity="0"/>
              </linearGradient>
              <radialGradient id="moonGlow" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#FFE0A3" stop-opacity="0.6"/>
                <stop offset="100%" stop-color="#FFE0A3" stop-opacity="0"/>
              </radialGradient>
              <filter id="blur">
                <feGaussianBlur stdDeviation="3"/>
              </filter>
            </defs>

            <!-- Sky -->
            <rect width="520" height="400" fill="url(#skyGrad)" rx="20"/>

            <!-- Stars -->
            <circle cx="50" cy="30" r="1.2" fill="white" opacity="0.8"/>
            <circle cx="130" cy="50" r="0.8" fill="white" opacity="0.6"/>
            <circle cx="200" cy="20" r="1" fill="white" opacity="0.9"/>
            <circle cx="300" cy="40" r="0.7" fill="white" opacity="0.5"/>
            <circle cx="370" cy="25" r="1.2" fill="white" opacity="0.8"/>
            <circle cx="450" cy="45" r="0.9" fill="white" opacity="0.7"/>
            <circle cx="490" cy="15" r="0.6" fill="white" opacity="0.6"/>
            <circle cx="80" cy="65" r="0.7" fill="white" opacity="0.5"/>
            <circle cx="420" cy="70" r="0.8" fill="white" opacity="0.6"/>

            <!-- Moon glow -->
            <ellipse cx="430" cy="55" rx="35" ry="35" fill="url(#moonGlow)"/>
            <circle cx="430" cy="55" r="16" fill="#FFE9C2"/>
            <circle cx="425" cy="50" r="12" fill="#FFFAED"/>

            <!-- Ground -->
            <rect x="0" y="320" width="520" height="80" fill="url(#groundGrad)" rx="0"/>

            <!-- Ambient glow on ground -->
            <ellipse cx="260" cy="325" rx="260" ry="18" fill="url(#glowGrad)"/>

            <!-- Building 4 (far left, small) -->
            <rect x="20" y="200" width="55" height="125" fill="url(#buildingGrad3)" rx="4"/>
            <rect x="28" y="210" width="8" height="10" fill="#47BFFF" opacity="0.5" rx="1"/>
            <rect x="40" y="210" width="8" height="10" fill="#FFD700" opacity="0.4" rx="1"/>
            <rect x="52" y="210" width="8" height="10" fill="#47BFFF" opacity="0.3" rx="1"/>
            <rect x="28" y="228" width="8" height="10" fill="#FFD700" opacity="0.5" rx="1"/>
            <rect x="52" y="228" width="8" height="10" fill="#5B3FE0" opacity="0.6" rx="1"/>
            <rect x="28" y="246" width="8" height="10" fill="#47BFFF" opacity="0.4" rx="1"/>
            <rect x="40" y="246" width="8" height="10" fill="#5B3FE0" opacity="0.5" rx="1"/>

            <!-- Building 5 (far right, small) -->
            <rect x="440" y="230" width="65" height="95" fill="url(#buildingGrad3)" rx="4"/>
            <rect x="450" y="240" width="9" height="11" fill="#47BFFF" opacity="0.5" rx="1"/>
            <rect x="464" y="240" width="9" height="11" fill="#FFD700" opacity="0.4" rx="1"/>
            <rect x="478" y="240" width="9" height="11" fill="#47BFFF" opacity="0.3" rx="1"/>
            <rect x="450" y="258" width="9" height="11" fill="#5B3FE0" opacity="0.6" rx="1"/>
            <rect x="478" y="258" width="9" height="11" fill="#FFD700" opacity="0.5" rx="1"/>

            <!-- Building 2 (left mid) -->
            <rect x="65" y="145" width="80" height="180" fill="url(#buildingGrad1)" rx="5"/>
            <!-- Windows grid -->
            <rect x="76" y="158" width="12" height="14" fill="#47BFFF" opacity="0.6" rx="2"/>
            <rect x="94" y="158" width="12" height="14" fill="#FFD700" opacity="0.5" rx="2"/>
            <rect x="112" y="158" width="12" height="14" fill="#47BFFF" opacity="0.4" rx="2"/>
            <rect x="130" y="158" width="12" height="14" fill="#5B3FE0" opacity="0.7" rx="2"/>
            <rect x="76" y="180" width="12" height="14" fill="#5B3FE0" opacity="0.5" rx="2"/>
            <rect x="94" y="180" width="12" height="14" fill="#47BFFF" opacity="0.6" rx="2"/>
            <rect x="112" y="180" width="12" height="14" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="130" y="180" width="12" height="14" fill="#47BFFF" opacity="0.4" rx="2"/>
            <rect x="76" y="202" width="12" height="14" fill="#FFD700" opacity="0.4" rx="2"/>
            <rect x="112" y="202" width="12" height="14" fill="#5B3FE0" opacity="0.6" rx="2"/>
            <rect x="76" y="224" width="12" height="14" fill="#47BFFF" opacity="0.5" rx="2"/>
            <rect x="94" y="224" width="12" height="14" fill="#5B3FE0" opacity="0.4" rx="2"/>
            <rect x="130" y="224" width="12" height="14" fill="#FFD700" opacity="0.5" rx="2"/>
            <!-- Door -->
            <rect x="95" y="290" width="20" height="35" fill="#5B3FE0" opacity="0.4" rx="3"/>

            <!-- Building 3 (right mid) -->
            <rect x="365" y="155" width="85" height="170" fill="url(#buildingGrad1)" rx="5"/>
            <rect x="375" y="168" width="12" height="14" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="393" y="168" width="12" height="14" fill="#47BFFF" opacity="0.5" rx="2"/>
            <rect x="411" y="168" width="12" height="14" fill="#5B3FE0" opacity="0.6" rx="2"/>
            <rect x="429" y="168" width="12" height="14" fill="#47BFFF" opacity="0.4" rx="2"/>
            <rect x="375" y="190" width="12" height="14" fill="#47BFFF" opacity="0.5" rx="2"/>
            <rect x="411" y="190" width="12" height="14" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="429" y="190" width="12" height="14" fill="#5B3FE0" opacity="0.5" rx="2"/>
            <rect x="375" y="212" width="12" height="14" fill="#5B3FE0" opacity="0.7" rx="2"/>
            <rect x="393" y="212" width="12" height="14" fill="#FFD700" opacity="0.4" rx="2"/>
            <rect x="429" y="212" width="12" height="14" fill="#47BFFF" opacity="0.6" rx="2"/>
            <rect x="393" y="234" width="12" height="14" fill="#47BFFF" opacity="0.5" rx="2"/>
            <rect x="411" y="234" width="12" height="14" fill="#5B3FE0" opacity="0.4" rx="2"/>
            <!-- Door -->
            <rect x="397" y="289" width="20" height="36" fill="#5B3FE0" opacity="0.4" rx="3"/>

            <!-- Main Center Building (tallest) -->
            <rect x="150" y="90" width="220" height="235" fill="url(#buildingGrad2)" rx="8"/>
            <!-- Roof detail -->
            <rect x="165" y="82" width="190" height="15" fill="#7A57EC" rx="4"/>
            <rect x="190" y="72" width="140" height="15" fill="#5B3FE0" rx="4"/>
            <!-- Window grid - main building (more bright) -->
            <rect x="165" y="108" width="18" height="20" fill="#fff" opacity="0.15" rx="2"/>
            <rect x="190" y="108" width="18" height="20" fill="#FFD700" opacity="0.7" rx="2"/>
            <rect x="215" y="108" width="18" height="20" fill="#fff" opacity="0.2" rx="2"/>
            <rect x="240" y="108" width="18" height="20" fill="#47BFFF" opacity="0.8" rx="2"/>
            <rect x="265" y="108" width="18" height="20" fill="#fff" opacity="0.15" rx="2"/>
            <rect x="290" y="108" width="18" height="20" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="315" y="108" width="18" height="20" fill="#fff" opacity="0.2" rx="2"/>
            <rect x="340" y="108" width="18" height="20" fill="#47BFFF" opacity="0.5" rx="2"/>

            <rect x="165" y="136" width="18" height="20" fill="#47BFFF" opacity="0.7" rx="2"/>
            <rect x="190" y="136" width="18" height="20" fill="#fff" opacity="0.15" rx="2"/>
            <rect x="215" y="136" width="18" height="20" fill="#FFD700" opacity="0.8" rx="2"/>
            <rect x="240" y="136" width="18" height="20" fill="#fff" opacity="0.2" rx="2"/>
            <rect x="265" y="136" width="18" height="20" fill="#5B3FE0" opacity="0.9" rx="2"/>
            <rect x="290" y="136" width="18" height="20" fill="#47BFFF" opacity="0.6" rx="2"/>
            <rect x="315" y="136" width="18" height="20" fill="#FFD700" opacity="0.7" rx="2"/>
            <rect x="340" y="136" width="18" height="20" fill="#fff" opacity="0.2" rx="2"/>

            <rect x="165" y="164" width="18" height="20" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="215" y="164" width="18" height="20" fill="#47BFFF" opacity="0.7" rx="2"/>
            <rect x="240" y="164" width="18" height="20" fill="#5B3FE0" opacity="0.8" rx="2"/>
            <rect x="290" y="164" width="18" height="20" fill="#fff" opacity="0.2" rx="2"/>
            <rect x="315" y="164" width="18" height="20" fill="#FFD700" opacity="0.5" rx="2"/>
            <rect x="340" y="164" width="18" height="20" fill="#47BFFF" opacity="0.6" rx="2"/>

            <rect x="165" y="192" width="18" height="20" fill="#47BFFF" opacity="0.5" rx="2"/>
            <rect x="190" y="192" width="18" height="20" fill="#5B3FE0" opacity="0.7" rx="2"/>
            <rect x="240" y="192" width="18" height="20" fill="#FFD700" opacity="0.6" rx="2"/>
            <rect x="265" y="192" width="18" height="20" fill="#47BFFF" opacity="0.4" rx="2"/>
            <rect x="315" y="192" width="18" height="20" fill="#5B3FE0" opacity="0.6" rx="2"/>
            <rect x="340" y="192" width="18" height="20" fill="#FFD700" opacity="0.5" rx="2"/>

            <rect x="165" y="220" width="18" height="20" fill="#FFD700" opacity="0.5" rx="2"/>
            <rect x="215" y="220" width="18" height="20" fill="#47BFFF" opacity="0.6" rx="2"/>
            <rect x="265" y="220" width="18" height="20" fill="#5B3FE0" opacity="0.7" rx="2"/>
            <rect x="290" y="220" width="18" height="20" fill="#FFD700" opacity="0.4" rx="2"/>
            <rect x="340" y="220" width="18" height="20" fill="#47BFFF" opacity="0.5" rx="2"/>

            <!-- Main entrance -->
            <rect x="230" y="265" width="60" height="60" fill="#2A1A6E" rx="5"/>
            <rect x="237" y="265" width="23" height="55" fill="#5B3FE0" opacity="0.35" rx="3"/>
            <rect x="260" y="265" width="23" height="55" fill="#47BFFF" opacity="0.2" rx="3"/>

            <!-- Ground light effects -->
            <ellipse cx="260" cy="322" rx="80" ry="8" fill="#5B3FE0" opacity="0.2" filter="url(#blur)"/>
            <ellipse cx="110" cy="322" rx="45" ry="5" fill="#47BFFF" opacity="0.15" filter="url(#blur)"/>
            <ellipse cx="408" cy="322" rx="45" ry="5" fill="#47BFFF" opacity="0.15" filter="url(#blur)"/>

            <!-- Road lines -->
            <rect x="240" y="340" width="40" height="4" fill="#ffffff" opacity="0.08" rx="2"/>
            <rect x="240" y="355" width="40" height="4" fill="#ffffff" opacity="0.08" rx="2"/>
            <rect x="240" y="370" width="40" height="4" fill="#ffffff" opacity="0.08" rx="2"/>

            <!-- Trees -->
            <rect x="145" y="295" width="6" height="28" fill="#2A1A6E"/>
            <ellipse cx="148" cy="290" rx="14" ry="18" fill="#1a3a2e" opacity="0.9"/>
            <ellipse cx="148" cy="285" rx="10" ry="14" fill="#234d3d" opacity="0.8"/>

            <rect x="367" y="295" width="6" height="28" fill="#2A1A6E"/>
            <ellipse cx="370" cy="290" rx="14" ry="18" fill="#1a3a2e" opacity="0.9"/>
            <ellipse cx="370" cy="285" rx="10" ry="14" fill="#234d3d" opacity="0.8"/>
          </svg>
        </div>
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

.hero-illustration {
  width: 100%;
  max-width: 520px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 80px rgba(26, 26, 62, 0.3), 0 4px 20px rgba(91, 63, 224, 0.2);
}

.hero-svg {
  display: block;
  width: 100%;
  height: auto;
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
