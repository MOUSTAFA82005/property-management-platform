<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

/**
 * A real search: both fields are carried into the catalog as query
 * parameters, and the property types are the ones the API actually stores.
 */
const query = ref('')
const propertyType = ref('')

function search() {
  const params = {}
  if (query.value.trim()) params.search = query.value.trim()
  if (propertyType.value) params.property_type = propertyType.value

  router.push({ path: '/properties', query: params })
}
</script>

<template>
  <section class="search-section" aria-labelledby="search-panel-title">
    <div class="search-container">
      <form class="search-panel" @submit.prevent="search">
        <div class="search-panel-header" id="search-panel-title">
          <span class="search-panel-icon" aria-hidden="true">🔍</span>
          <span>Find a property</span>
        </div>

        <div class="search-fields">
          <div class="search-field">
            <label class="field-label" for="home-search-query">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
              </svg>
              Name, city or address
            </label>
            <div class="field-input-wrap">
              <input
                id="home-search-query"
                v-model="query"
                type="search"
                class="field-input"
                placeholder="e.g. Cairo"
              />
            </div>
          </div>

          <div class="search-field">
            <label class="field-label" for="home-search-type">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              Property type
            </label>
            <div class="field-input-wrap">
              <select id="home-search-type" v-model="propertyType" class="field-input">
                <option value="">All types</option>
                <option value="Apartment Building">Apartment Building</option>
                <option value="Residential Compound">Residential Compound</option>
              </select>
            </div>
          </div>

          <button class="search-btn" type="submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
              <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Search Properties
          </button>
        </div>
      </form>
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
  box-shadow: 0 16px 60px rgba(26, 26, 62, 0.12), 0 4px 16px rgba(91, 63, 224, 0.08);
  border: 1px solid rgba(91, 63, 224, 0.08);
  padding: 1.5rem 2rem;
}

.search-panel-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #5B3FE0;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.search-panel-icon {
  font-size: 1rem;
}

.search-fields {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(0, 1.2fr) auto;
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
  color: #14141F;
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
  border-color: #5B3FE0;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(91, 63, 224, 0.1);
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
  background: linear-gradient(135deg, #5B3FE0, #3D279E);
  color: #fff;
  font-size: 0.9rem;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  white-space: nowrap;
  box-shadow: 0 4px 16px rgba(91, 63, 224, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(91, 63, 224, 0.4);
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
