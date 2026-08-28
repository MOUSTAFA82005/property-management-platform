<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Filler,
} from 'chart.js'

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler)

/**
 * Collected revenue per month, straight from GET /api/owner/dashboard.
 * The component holds no figures of its own.
 */
const props = defineProps({
  payments: { type: Object, default: null },
  series: { type: Array, default: () => [] },
})

const chartCanvas = ref(null)

const labels = computed(() => props.series.map((point) => point.label))
const values = computed(() => props.series.map((point) => Number(point.total) || 0))
const hasData = computed(() => values.value.some((value) => value > 0))
const range = computed(() => {
  if (props.series.length === 0) return ''
  return `${props.series[0].label} — ${props.series[props.series.length - 1].label}`
})

let revenueChart = null

function build() {
  if (!chartCanvas.value || revenueChart) return

  revenueChart = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: labels.value,
      datasets: [
        {
          label: 'Collected',
          data: values.value,
          borderColor: '#5B3FE0',
          backgroundColor: 'rgba(91, 63, 224, 0.10)',
          borderWidth: 2.5,
          tension: 0.35,
          fill: true,
          pointRadius: 3,
          pointHoverRadius: 6,
          pointBackgroundColor: '#5B3FE0',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#14141f',
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          titleFont: { size: 12 },
          bodyFont: { size: 12 },
          callbacks: {
            label: (context) => `Collected: EGP ${context.parsed.y.toLocaleString()}`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: { color: '#8b8da3', font: { size: 11 } },
        },
        y: {
          beginAtZero: true,
          border: { display: false },
          grid: { color: '#f1f2f7' },
          ticks: {
            color: '#8b8da3',
            font: { size: 11 },
            maxTicksLimit: 5,
            callback: (value) => (value >= 1000 ? `${value / 1000}k` : value),
          },
        },
      },
    },
  })
}

onMounted(build)

watch([labels, values], () => {
  build()
  if (!revenueChart) return
  revenueChart.data.labels = labels.value
  revenueChart.data.datasets[0].data = values.value
  revenueChart.update()
})

onBeforeUnmount(() => {
  if (revenueChart) {
    revenueChart.destroy()
    revenueChart = null
  }
})
</script>

<template>
  <section class="owner-card owner-chart-card">
    <div class="owner-card-head">
      <div>
        <h2>Collected revenue</h2>
        <p>Payments marked paid, by month</p>
      </div>
      <span v-if="range">{{ range }}</span>
    </div>

    <div class="owner-chart-body">
      <p v-if="!hasData" class="owner-chart-note">
        No payments have been collected in this period yet.
      </p>
      <div class="owner-chart-canvas"><canvas ref="chartCanvas"></canvas></div>
    </div>
  </section>
</template>
