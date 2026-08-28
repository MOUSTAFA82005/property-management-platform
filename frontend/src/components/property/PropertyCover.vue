<script setup>
import { computed, ref, watch } from 'vue'
import { propertyImage, propertyImageAlt, seedBucket } from '../../utils/propertyImagery'

/**
 * The cover image for a property or unit.
 *
 * It shows a curated architectural photograph from `public/property/`, chosen
 * deterministically from the record's id and type. The backend has no image
 * column, so this is illustrative platform imagery rather than a photo of
 * that specific building — the alt text says so rather than implying
 * otherwise.
 *
 * If the photograph ever fails to load, the component falls back to the
 * drawn architectural artwork below, so a cover is never blank or broken.
 */
const props = defineProps({
  /** Used to pick a stable variant so a property always looks the same. */
  name: { type: String, default: '' },
  /** Raw `property_type` from the API; unknown values fall back to neutral. */
  type: { type: String, default: '' },
  /** Optional stable seed — the property id is ideal. */
  seed: { type: [Number, String], default: null },
  /** `card` for grids, `hero` for the detail header. */
  variant: { type: String, default: 'card' },
  /** Show the type chip drawn over the artwork. */
  showType: { type: Boolean, default: true },
  /** Set false to force the drawn artwork instead of a photograph. */
  photo: { type: Boolean, default: true },
})

const imageFailed = ref(false)
const imageLoaded = ref(false)

const imageSrc = computed(() =>
  props.photo ? propertyImage({ type: props.type, seed: props.seed ?? props.name }) : null,
)

const imageAlt = computed(() => propertyImageAlt(props.type))

/** A new record means a new image, so give it a fresh chance to load. */
watch(imageSrc, () => {
  imageFailed.value = false
  imageLoaded.value = false
})

/**
 * Images restored from the browser cache are already complete before the
 * `load` event would fire, so check for that rather than leaving a cached
 * cover stuck at zero opacity.
 *
 * The `@vue:mounted` hook receives the vnode, not a DOM event — the element
 * is on `vnode.el`.
 */
function onImageMounted(vnode) {
  if (vnode?.el?.complete) imageLoaded.value = true
}

const showPhoto = computed(() => Boolean(imageSrc.value) && !imageFailed.value)

/**
 * Map the free-text `property_type` onto one of the compositions. Matching is
 * keyword-based because the column is a plain string, and anything
 * unrecognised gets the neutral massing rather than a wrong motif.
 */
const motif = computed(() => {
  const value = String(props.type || '').toLowerCase()

  if (/villa|townhouse|house|duplex/.test(value)) return 'villa'
  if (/compound|residential|community|complex/.test(value)) return 'compound'
  if (/office|commercial|retail|shop|mall|business/.test(value)) return 'commercial'
  if (/apartment|flat|studio|tower|penthouse|building/.test(value)) return 'tower'

  return 'neutral'
})

/**
 * Three tonal treatments, chosen deterministically so cards vary but never
 * flicker. Uses the shared bucket helper rather than a second hash, so the
 * drawn fallback spreads the same way the photographs do.
 */
const tone = computed(
  () => `tone-${seedBucket(props.seed ?? props.name ?? '', 3) + 1}`,
)

const typeLabel = computed(() => props.type || 'Property')
</script>

<template>
  <div class="pcover" :class="[`pcover-${variant}`, tone, `motif-${motif}`]">
    <img
      v-if="showPhoto"
      class="pcover-photo ps-img-fade"
      :class="{ 'is-loaded': imageLoaded }"
      :src="imageSrc"
      :alt="imageAlt"
      loading="lazy"
      decoding="async"
      @load="imageLoaded = true"
      @error="imageFailed = true"
      @vue:mounted="onImageMounted"
    />

    <svg
      v-else
      class="pcover-art"
      viewBox="0 0 400 250"
      preserveAspectRatio="xMidYMid slice"
      aria-hidden="true"
      focusable="false"
    >
      <defs>
        <linearGradient :id="`sky-${tone}-${motif}`" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" class="stop-sky-top" />
          <stop offset="100%" class="stop-sky-bottom" />
        </linearGradient>

        <!-- Fine facade grid, used at low opacity so it reads as texture. -->
        <pattern :id="`grid-${tone}-${motif}`" width="12" height="14" patternUnits="userSpaceOnUse">
          <rect x="2.5" y="3" width="6" height="7" rx="1" class="win" />
        </pattern>
      </defs>

      <rect width="400" height="250" :fill="`url(#sky-${tone}-${motif})`" />

      <!-- A soft light wash, top-right, to give the plane some depth. -->
      <circle cx="330" cy="46" r="120" class="wash" />

      <!-- ── Tower: a minimal high-rise cluster ───────────────────── -->
      <g v-if="motif === 'tower'">
        <rect x="42" y="96" width="66" height="154" rx="3" class="mass mass-back" />
        <rect x="232" y="74" width="78" height="176" rx="3" class="mass mass-back" />
        <rect x="120" y="38" width="104" height="212" rx="4" class="mass mass-front" />
        <rect x="132" y="58" width="80" height="140" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="252" y="98" width="42" height="98" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="160" y="20" width="24" height="20" rx="2" class="mass mass-front" />
      </g>

      <!-- ── Villa: low, wide massing with a pitched roof ──────────── -->
      <g v-else-if="motif === 'villa'">
        <rect x="46" y="150" width="120" height="100" rx="3" class="mass mass-back" />
        <path d="M150 148 L236 92 L322 148 Z" class="mass mass-front" />
        <rect x="164" y="146" width="144" height="104" rx="3" class="mass mass-front" />
        <rect x="182" y="170" width="60" height="42" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="262" y="186" width="28" height="64" rx="2" class="mass mass-back" />
      </g>

      <!-- ── Compound: a cluster of low blocks around a court ──────── -->
      <g v-else-if="motif === 'compound'">
        <rect x="30" y="140" width="88" height="110" rx="3" class="mass mass-back" />
        <rect x="132" y="112" width="96" height="138" rx="3" class="mass mass-front" />
        <rect x="242" y="152" width="82" height="98" rx="3" class="mass mass-back" />
        <rect x="146" y="132" width="68" height="70" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="336" y="176" width="44" height="74" rx="3" class="mass mass-front" />
        <path d="M0 250 Q200 214 400 250 Z" class="ground" />
      </g>

      <!-- ── Commercial: a wide, regular curtain wall ──────────────── -->
      <g v-else-if="motif === 'commercial'">
        <rect x="24" y="118" width="150" height="132" rx="3" class="mass mass-back" />
        <rect x="186" y="66" width="190" height="184" rx="4" class="mass mass-front" />
        <rect x="202" y="88" width="158" height="126" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="40" y="140" width="118" height="70" :fill="`url(#grid-${tone}-${motif})`" />
        <rect x="186" y="238" width="190" height="12" class="mass mass-back" />
      </g>

      <!-- ── Neutral: abstract architectural planes ────────────────── -->
      <g v-else>
        <rect x="36" y="126" width="128" height="124" rx="3" class="mass mass-back" />
        <rect x="176" y="88" width="104" height="162" rx="3" class="mass mass-front" />
        <rect x="292" y="146" width="86" height="104" rx="3" class="mass mass-back" />
        <rect x="192" y="110" width="72" height="86" :fill="`url(#grid-${tone}-${motif})`" />
      </g>

      <!-- Horizon rule: the detail that makes it read as architecture. -->
      <rect x="0" y="248" width="400" height="2" class="horizon" />
    </svg>

    <span v-if="showType" class="pcover-chip">{{ typeLabel }}</span>
  </div>
</template>

<style scoped>
.pcover {
  position: relative;
  width: 100%;
  overflow: hidden;
  background: var(--surface-2);
  isolation: isolate;
}

/* Keeps the cover at roughly 40% of the card rather than dominating it. */
.pcover-card { aspect-ratio: 16 / 9; }

.pcover-hero {
  aspect-ratio: 21 / 8;
  border-radius: var(--r-lg);
  border: 1px solid var(--line);
}

.pcover-art,
.pcover-photo {
  display: block;
  width: 100%;
  height: 100%;
}

.pcover-photo {
  object-fit: cover;
  /* A restrained wash keeps the photography sitting inside the brand
     palette instead of fighting it. */
  filter: saturate(.94);
}

/* Bottom scrim so the type chip and any overlaid text stay readable
   whatever the photograph happens to be. */
.pcover::after {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background: linear-gradient(
    to bottom,
    rgba(20, 20, 31, .22) 0%,
    rgba(20, 20, 31, 0) 38%,
    rgba(20, 20, 31, 0) 62%,
    rgba(20, 20, 31, .30) 100%
  );
}

/* ── Tonal sets ──────────────────────────────────────────────────────
   Three restrained treatments built from the brand and neutral ramps, so
   a grid of cards varies without turning into a colour wheel. */

.tone-1 .stop-sky-top { stop-color: #f3f1fe; }
.tone-1 .stop-sky-bottom { stop-color: #e2ddf8; }
.tone-1 .mass-back { fill: #c9c0ef; }
.tone-1 .mass-front { fill: #a996e4; }
.tone-1 .horizon { fill: #8b76d4; }

.tone-2 .stop-sky-top { stop-color: #f2f5f9; }
.tone-2 .stop-sky-bottom { stop-color: #dde4ee; }
.tone-2 .mass-back { fill: #bfc9d9; }
.tone-2 .mass-front { fill: #97a5bd; }
.tone-2 .horizon { fill: #7d8ca6; }

.tone-3 .stop-sky-top { stop-color: #f6f4f1; }
.tone-3 .stop-sky-bottom { stop-color: #e9e3dc; }
.tone-3 .mass-back { fill: #d0c5b8; }
.tone-3 .mass-front { fill: #ab9c8b; }
.tone-3 .horizon { fill: #8f8172; }

.wash { fill: #fff; opacity: .3; }
.win { fill: #fff; opacity: .38; }
.ground { fill: #fff; opacity: .18; }

.mass { transition: opacity var(--dur) var(--ease); }

/* ── Type chip ───────────────────────────────────────────────────── */

.pcover-chip {
  position: absolute;
  top: var(--sp-3);
  left: var(--sp-3);
  z-index: 1;
  padding: 4px var(--sp-3);
  border-radius: var(--r-full);
  background: rgba(255, 255, 255, .92);
  color: var(--ink-2);
  font-size: var(--fs-2xs);
  font-weight: 650;
  letter-spacing: .04em;
  text-transform: uppercase;
  box-shadow: var(--sh-xs);
  backdrop-filter: blur(4px);
}

.pcover-hero .pcover-chip {
  top: var(--sp-4);
  left: var(--sp-4);
}

@media (prefers-reduced-motion: reduce) {
  .mass { transition: none; }
}
</style>
