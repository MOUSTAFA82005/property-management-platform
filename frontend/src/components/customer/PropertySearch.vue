<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const search = () => {
  router.push('/properties')
}
</script>

<template>
  <section class="search-section">
    <div class="search-container">
      <div class="search-panel">
        <div class="search-panel-header">
          <span class="search-panel-icon">🔍</span>
          <span>Find Your Perfect Property</span>
        </div>

        <div class="search-fields">
          <div class="search-field">
            <label class="field-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
              </svg>
              Location
            </label>
            <div class="field-input-wrap">
              <input type="text" class="field-input" placeholder="Search location…" />
            </div>
          </div>

          <div class="search-field">
            <label class="field-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              Property Type
            </label>
            <div class="field-input-wrap">
              <select class="field-input">
                <option value="">All Types</option>
                <option value="apartment">Apartment</option>
                <option value="villa">Villa</option>
                <option value="townhouse">Townhouse</option>
                <option value="commercial">Commercial</option>
                <option value="studio">Studio</option>
              </select>
            </div>
          </div>

          <div class="search-field">
            <label class="field-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Price Range
            </label>
            <div class="field-input-wrap">
              <select class="field-input">
                <option value="">Any Price</option>
                <option value="0-1000000">Up to EGP 1,000,000</option>
                <option value="1000000-2500000">EGP 1M – 2.5M</option>
                <option value="2500000-5000000">EGP 2.5M – 5M</option>
                <option value="5000000-10000000">EGP 5M – 10M</option>
                <option value="10000000+">EGP 10M+</option>
              </select>
            </div>
          </div>

          <button class="search-btn" @click="search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
              <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Search Properties
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.search-section {
  position: relative;
  z-index: 10;
  margin-top: -48px;
  padding: 0 1.5rem 3rem;
}

.search-container {
  max-width: 1100px;
  margin: 0 auto;
}

.search-panel {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 16px 60px rgba(26, 26, 62, 0.12), 0 4px 16px rgba(134, 76, 255, 0.08);
  border: 1px solid rgba(134, 76, 255, 0.08);
  padding: 1.5rem 2rem;
}

.search-panel-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #864CFF;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.search-panel-icon {
  font-size: 1rem;
}

.search-fields {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 1rem;
  align-items: end;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.field-input-wrap {
  position: relative;
}

.field-input {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.925rem;
  color: #1a1a2e;
  background: #f8fafc;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  appearance: none;
  -webkit-appearance: none;
}

select.field-input {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.25em 1.25em;
  padding-right: 2.5rem;
}

.field-input:focus {
  border-color: #864CFF;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(134, 76, 255, 0.1);
}

.field-input::placeholder {
  color: #94a3b8;
}

.search-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #864CFF, #6B2FFF);
  color: #fff;
  font-size: 0.9rem;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  white-space: nowrap;
  box-shadow: 0 4px 16px rgba(134, 76, 255, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(134, 76, 255, 0.4);
}

.search-btn:active {
  transform: translateY(0);
}

@media (max-width: 900px) {
  .search-fields {
    grid-template-columns: 1fr 1fr;
  }

  .search-btn {
    grid-column: 1 / -1;
    width: 100%;
    padding: 0.875rem;
  }
}

@media (max-width: 560px) {
  .search-panel {
    padding: 1.25rem 1.25rem;
  }

  .search-fields {
    grid-template-columns: 1fr;
  }
}
</style>
