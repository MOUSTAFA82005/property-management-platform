<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="cta-section">
    <div class="cta-blob cta-blob-1"></div>
    <div class="cta-blob cta-blob-2"></div>

    <div class="cta-container">
      <div class="cta-icon">🏠</div>
      <h2 class="cta-title">Ready to Find Your Next Property?</h2>
      <p class="cta-desc">
        Explore available properties and find the right place for you.
      </p>
      <div class="cta-actions">
        <RouterLink to="/properties" class="btn-cta-primary">
          Explore Properties
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </RouterLink>
        <RouterLink to="/register" class="btn-cta-ghost">
          Create an Account
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cta-section {
  position: relative;
  padding: 6rem 1.5rem;
  background: linear-gradient(135deg, #864CFF 0%, #6B2FFF 50%, #47BFFF 100%);
  overflow: hidden;
  text-align: center;
}

.cta-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  pointer-events: none;
}

.cta-blob-1 {
  width: 400px;
  height: 400px;
  background: rgba(255, 255, 255, 0.12);
  top: -100px;
  left: -100px;
}

.cta-blob-2 {
  width: 350px;
  height: 350px;
  background: rgba(0, 0, 0, 0.1);
  bottom: -80px;
  right: -80px;
}

.cta-container {
  position: relative;
  z-index: 1;
  max-width: 680px;
  margin: 0 auto;
}

.cta-icon {
  font-size: 3rem;
  margin-bottom: 1.5rem;
  display: block;
  animation: float-icon 3s ease-in-out infinite;
}

@keyframes float-icon {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.cta-title {
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.03em;
  line-height: 1.15;
  margin-bottom: 1rem;
}

.cta-desc {
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.82);
  line-height: 1.65;
  margin-bottom: 2.5rem;
  max-width: 520px;
  margin-left: auto;
  margin-right: auto;
}

.cta-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-cta-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.9rem 2rem;
  background: #fff;
  color: #864CFF;
  font-size: 1rem;
  font-weight: 800;
  text-decoration: none;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
  transition: transform 0.25s, box-shadow 0.25s;
}

.btn-cta-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 36px rgba(0, 0, 0, 0.25);
  color: #6B2FFF;
}

.btn-cta-ghost {
  display: inline-flex;
  align-items: center;
  padding: 0.9rem 1.75rem;
  border: 2px solid rgba(255, 255, 255, 0.5);
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  border-radius: 12px;
  transition: background 0.25s, border-color 0.25s;
}

.btn-cta-ghost:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.8);
  color: #fff;
}

@media (max-width: 480px) {
  .cta-section {
    padding: 4rem 1rem;
  }

  .cta-actions {
    flex-direction: column;
  }

  .btn-cta-primary,
  .btn-cta-ghost {
    width: 100%;
    justify-content: center;
  }
}
</style>
