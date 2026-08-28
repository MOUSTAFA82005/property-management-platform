/**
 * The one place property imagery is decided.
 *
 * The API has no image column, so a photograph cannot be attached to a
 * specific listing. These are curated architectural photographs served from
 * `public/property/`, picked deterministically from the property's own id and
 * type so a given property always shows the same picture — illustrative
 * platform imagery, not a photo of that building.
 *
 * They are local files, so there are no external requests and nothing to fail
 * offline. `PropertyCover` still falls back to its drawn artwork if an image
 * ever fails to load.
 */

const APARTMENT = ['/property/tower-1.jpg', '/property/tower-2.jpg']
const VILLA = ['/property/villa-1.jpg', '/property/villa-2.jpg']
const COMPOUND = ['/property/compound-1.jpg', '/property/villa-1.jpg']
const COMMERCIAL = ['/property/commercial-1.jpg']
const INTERIOR = ['/property/interior-1.jpg']

/** The landing-page hero. */
export const HERO_IMAGE = '/property/villa-2.jpg'

/** Every file this module can reference, for preloading or auditing. */
export const ALL_IMAGES = [
  ...new Set([...APARTMENT, ...VILLA, ...COMPOUND, ...COMMERCIAL, ...INTERIOR, HERO_IMAGE]),
]

/**
 * Map the free-text `property_type`/`unit_type` onto a visual family.
 * Anything unrecognised falls back to apartment, the commonest stock.
 */
export function imageFamily(type) {
  const value = String(type || '').toLowerCase()

  if (/villa|penthouse/.test(value)) return 'villa'
  if (/townhouse|compound|community|complex/.test(value)) return 'compound'
  if (/office|commercial|retail|shop|mall|business/.test(value)) return 'commercial'
  if (/studio|room|interior/.test(value)) return 'interior'

  return 'apartment'
}

const FAMILIES = {
  apartment: APARTMENT,
  villa: VILLA,
  compound: COMPOUND,
  commercial: COMMERCIAL,
  interior: INTERIOR,
}

/**
 * Stable, non-cryptographic hash so the same record keeps the same picture.
 *
 * FNV-1a with an avalanche step. The obvious `h * 31 + char` version spreads
 * adjacent short inputs terribly — ids "1" and "3" both landed on an odd
 * number, so every property with an even-sized image pool got the same
 * photograph and a grid of cards showed the same building twice. The final
 * mix makes neighbouring ids land in unrelated buckets.
 */
function hash(value) {
  const text = String(value ?? '')
  let h = 2166136261

  for (let i = 0; i < text.length; i += 1) {
    h ^= text.charCodeAt(i)
    h = Math.imul(h, 16777619)
  }

  h ^= h >>> 13
  h = Math.imul(h, 0x5bd1e995)
  h ^= h >>> 15

  return h >>> 0
}

/** Exposed so the drawn fallback picks its tone with the same distribution. */
export function seedBucket(seed, buckets) {
  return hash(seed) % buckets
}

/**
 * The image for one property or unit.
 *
 * @param {{ type?: string, seed?: number|string }} options
 * @returns {string} a path under /property
 */
export function propertyImage({ type, seed } = {}) {
  const pool = FAMILIES[imageFamily(type)] || APARTMENT
  return pool[hash(seed) % pool.length]
}

/**
 * Alt text for a property image.
 *
 * The photograph is illustrative, so the alt says what it is rather than
 * claiming to depict this specific building.
 */
export function propertyImageAlt(type) {
  const family = imageFamily(type)

  const described = {
    apartment: 'a modern apartment building',
    villa: 'a contemporary villa',
    compound: 'a residential townhouse development',
    commercial: 'a commercial office building',
    interior: 'a furnished modern interior',
  }[family]

  return `Illustrative photograph of ${described}`
}

/**
 * The browsing categories on the landing page.
 *
 * `match` is used to filter the real catalogue by `property_type`, so a
 * category never promises stock the API does not have.
 */
export const PROPERTY_CATEGORIES = [
  {
    key: 'apartment',
    label: 'Apartments',
    blurb: 'City living, floor by floor',
    image: '/property/tower-1.jpg',
    match: /apartment|flat|studio|tower/i,
  },
  {
    key: 'villa',
    label: 'Villas',
    blurb: 'Space, privacy and a garden',
    image: '/property/villa-1.jpg',
    match: /villa|penthouse/i,
  },
  {
    key: 'compound',
    label: 'Compounds',
    blurb: 'Gated communities and townhouses',
    image: '/property/compound-1.jpg',
    match: /compound|townhouse|residential|community/i,
  },
  {
    key: 'commercial',
    label: 'Commercial',
    blurb: 'Offices and retail space',
    image: '/property/commercial-1.jpg',
    match: /office|commercial|retail|shop|business/i,
  },
]
