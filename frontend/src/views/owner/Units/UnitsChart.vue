<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Chart, DoughnutController, ArcElement, Tooltip } from 'chart.js'

Chart.register(DoughnutController, ArcElement, Tooltip)

/**
 * Unit mix from GET /api/owner/dashboard.
 *
 * The three statuses the schema supports are available, occupied and
 * reserved. Earlier mock data showed a fourth that does not exist.
 */
const props = defineProps({
  units: { type: Object, default: null },
})

const chartCanvas = ref(null)

const LEGEND = [
  { key: 'available', label: 'Available', color: '#0f9d6b' },
  { key: 'occupied', label: 'Occupied', color: '#5B3FE0' },
  { key: 'reserved', label: 'Reserved', color: '#E0A33D' },
]

const values = computed(() => LEGEND.map((item) => props.units?.[item.key] ?? 0))
const total = computed(() => values.value.reduce((sum, value) => sum + value, 0))
const hasData = computed(() => total.value > 0)

/** Share of units that are let — arithmetic on the API's own counters. */
const occupancy = computed(() =>
  total.value === 0 ? null : Math.round(((props.units?.occupied ?? 0) / total.value) * 100),
)

let unitsChart = null

function build() {
  if (!chartCanvas.value || unitsChart) return

  unitsChart = new Chart(chartCanvas.value, {
    type: 'doughnut',
    data: {
      labels: LEGEND.map((item) => item.label),
      datasets: [
        {
          data: values.value,
          backgroundColor: LEGEND.map((item) => item.color),
          borderWidth: 0,
          hoverOffset: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#14141f',
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
        },
      },
    },
  })
}

onMounted(build)

watch(values, () => {
  build()
  if (!unitsChart) return
  unitsChart.data.datasets[0].data = values.value
  unitsChart.update()
})

onBeforeUnmount(() => {
  if (unitsChart) unitsChart.destroy()
})
</script>

<template>
  <section class="owner-card owner-chart-card">
    <div class="owner-card-head">
      <div>
        <h2>Unit mix</h2>
        <p>Current status of every unit you own</p>
      </div>
      <span v-if="occupancy !== null">{{ occupancy }}% occupied</span>
    </div>

    <div class="owner-chart-body">
      <p v-if="!hasData" class="owner-chart-note">No units have been added yet.</p>

      <div class="owner-donut">
        <div class="owner-chart-canvas owner-donut-canvas"><canvas ref="chartCanvas"></canvas></div>

        <ul class="owner-legend">
          <li v-for="(item, index) in LEGEND" :key="item.key">
            <span class="owner-legend-dot" :style="{ background: item.color }" aria-hidden="true"></span>
            <span class="owner-legend-label">{{ item.label }}</span>
            <span class="owner-legend-value">{{ values[index] }}</span>
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>
