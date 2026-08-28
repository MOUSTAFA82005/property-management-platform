<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePropertiesStore } from '../../stores/properties'
import { PROPERTY_CATEGORIES } from '../../utils/propertyImagery'

/**
 * Browse-by-type entry points.
 *
 * Each tile is backed by the real catalogue: the count comes from the
 * published properties the API returned, and a category with nothing behind
 * it is not shown at all. Clicking one runs the existing catalogue filter —
 * no new endpoint, no invented category.
 */
const router = useRouter()
const store = usePropertiesStore()

const categories = computed(() =>
  PROPERTY_CATEGORIES.map((category) => ({
    ...category,
    count: store.properties.filter((property) =>
      category.match.test(property.property_type || ''),
    ).length,
  })).filter((category) => category.count > 0),
)

function browse(category) {
  router.push({ path: '/properties', query: { property_type: matchedType(category) } })
}

/** The catalogue filters on the exact `property_type` string the API uses. */
function matchedType(category) {
  const property = store.properties.find((item) => category.match.test(item.property_type || ''))
  return property?.property_type
}

onMounted(() => {
  if (store.properties.length === 0) {
    store.fetchPublicProperties({ per_page: 12 }).catch(() => {})
  }
})
</script>

<template>
  <section v-if="categories.length" class="cat-section" aria-labelledby="browse-by-type">
    <div class="cat-container">
      <div class="cat-head">
        <div class="cat-tag">Browse</div>
        <h2 id="browse-by-type" class="cat-title">Find the right kind of home</h2>
        <p class="cat-sub">Every category below is drawn from what is actually listed today.</p>
      </div>

      <ul class="cat-grid ps-stagger">
        <li v-for="category in categories" :key="category.key">
          <button type="button" class="cat-tile" @click="browse(category)">
            <img
              class="cat-img"
              :src="category.image"
              alt=""
              aria-hidden="true"
              loading="lazy"
              decoding="async"
            />
            <span class="cat-body">
              <span class="cat-label">{{ category.label }}</span>
              <span class="cat-blurb">{{ category.blurb }}</span>
              <span class="cat-count">
                {{ category.count }} {{ category.count === 1 ? 'property' : 'properties' }}
              </span>
            </span>
          </button>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.cat-section {
  padding: 4.5rem 0;
  background: var(--surface);
}

.cat-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.cat-head { margin-bottom: 2.25rem; }

.cat-tag {
  display: inline-block;
  padding: 0.3rem 0.75rem;
  border-radius: var(--r-full);
  background: var(--brand-50);
  color: var(--brand-700);
  font-size: var(--fs-2xs);
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 0.75rem;
}

.cat-title {
  margin: 0 0 0.4rem;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 750;
  letter-spacing: -0.02em;
  color: var(--ink);
}

.cat-sub {
  margin: 0;
  color: var(--muted);
  font-size: var(--fs-md);
}

.cat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: var(--sp-5);
  list-style: none;
  margin: 0;
  padding: 0;
}

.cat-tile {
  position: relative;
  display: block;
  width: 100%;
  padding: 0;
  border: 1px solid var(--line);
  border-radius: var(--r-lg);
  overflow: hidden;
  background: var(--surface);
  text-align: left;
  cursor: pointer;
  box-shadow: var(--sh-xs);
  transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease),
              border-color var(--dur) var(--ease);
}

.cat-tile:hover {
  transform: translateY(-4px);
  box-shadow: var(--sh-md);
  border-color: var(--brand-edge);
}

.cat-tile:focus-visible {
  outline: 2px solid var(--brand-600);
  outline-offset: 2px;
}

.cat-img {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
}

.cat-body {
  display: block;
  padding: var(--sp-4);
}

.cat-label {
  display: block;
  font-size: var(--fs-lg);
  font-weight: 700;
  color: var(--ink);
  letter-spacing: -0.01em;
}

.cat-blurb {
  display: block;
  margin-top: 2px;
  font-size: var(--fs-sm);
  color: var(--muted);
}

.cat-count {
  display: block;
  margin-top: var(--sp-3);
  font-size: var(--fs-xs);
  font-weight: 650;
  color: var(--brand-700);
}

@media (prefers-reduced-motion: reduce) {
  .cat-tile, .cat-tile:hover { transition: none; transform: none; }
}
</style>
