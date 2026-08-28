<script setup>
/**
 * The PropSpace mark.
 *
 * The same glyph the public navbar has always used, lifted into one component
 * so the login screen, the owner portal and the public site all render the
 * identical brand rather than three near-misses.
 */
defineProps({
  /** Height of the mark in px; the wordmark scales with it. */
  size: { type: Number, default: 32 },
  /** Hide the "PropSpace" wordmark and show the glyph alone. */
  markOnly: { type: Boolean, default: false },
  /** `dark` for light backgrounds, `light` for dark ones. */
  tone: { type: String, default: 'dark' },
  /** Optional line under the wordmark, e.g. "Owner Portal". */
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <span class="brand" :class="[`brand-${tone}`]">
    <svg
      :width="size"
      :height="Math.round((size * 46) / 48)"
      viewBox="0 0 48 46"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="brand-mark"
      aria-hidden="true"
      focusable="false"
    >
      <path
        d="M25.946 44.938c-.664.845-2.021.375-2.021-.698V33.937a2.26 2.26 0 0 0-2.262-2.262H10.287c-.92 0-1.456-1.04-.92-1.788l7.48-10.471c1.07-1.497 0-3.578-1.842-3.578H1.237c-.92 0-1.456-1.04-.92-1.788L10.013.474c.214-.297.556-.474.92-.474h28.894c.92 0 1.456 1.04.92 1.788l-7.48 10.471c-1.07 1.498 0 3.579 1.842 3.579h11.377c.943 0 1.473 1.088.89 1.83L25.947 44.94z"
      />
    </svg>

    <span v-if="!markOnly" class="brand-text">
      <span class="brand-name">PropSpace</span>
      <span v-if="subtitle" class="brand-subtitle">{{ subtitle }}</span>
    </span>
  </span>
</template>

<style scoped>
.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  line-height: 1;
}

.brand-mark { flex-shrink: 0; }

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.brand-name {
  font-size: 1.1rem;
  font-weight: 750;
  letter-spacing: -0.02em;
  white-space: nowrap;
}

.brand-subtitle {
  font-size: 0.66rem;
  font-weight: 600;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  white-space: nowrap;
}

/* On light backgrounds. */
.brand-dark .brand-mark { fill: var(--brand-600, #5B3FE0); }
.brand-dark .brand-name { color: var(--ink, #14141F); }
.brand-dark .brand-subtitle { color: var(--muted-2, #8b8da3); }

/* On dark or photographic backgrounds. */
.brand-light .brand-mark { fill: #fff; }
.brand-light .brand-name { color: #fff; }
.brand-light .brand-subtitle { color: rgba(255, 255, 255, 0.72); }
</style>
