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
  Filler
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Filler
)

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

let revenueChart = null

onMounted(() => {
  if (!chartCanvas.value) return

  revenueChart = new Chart(chartCanvas.value, {
    type: 'line',

    data: {
      labels: labels.value,

      datasets: [
        {
          label: 'Revenue',

          data: values.value,

          borderColor: '#864CFF',
          backgroundColor: 'rgba(134, 76, 255, 0.10)',

          borderWidth: 3,
          tension: 0.4,

          fill: true,

          pointRadius: 4,
          pointHoverRadius: 7,

          pointBackgroundColor: '#864CFF',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2
        }
      ]
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        intersect: false,
        mode: 'index'
      },

      plugins: {
        legend: {
          display: false
        },

        tooltip: {
          backgroundColor: '#171526',
          padding: 12,
          displayColors: false,

          callbacks: {
            label(context) {
              return `Revenue: EGP ${context.parsed.y.toLocaleString()}`
            }
          }
        }
      },

      scales: {
        x: {
          grid: {
            display: false
          },

          border: {
            display: false
          },

          ticks: {
            color: '#737184'
          }
        },

        y: {
          beginAtZero: true,

          border: {
            display: false
          },

          grid: {
            color: '#ebeaf1'
          },

          ticks: {
            color: '#737184',

            callback(value) {
              return `EGP ${value / 1000}K`
            }
          }
        }
      }
    }
  })
})

watch([labels, values], () => {
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
  <div class="card border-0 shadow-sm h-100">

    <div class="card-header bg-white border-0 p-4">

      <div class="d-flex justify-content-between align-items-center">

        <div>
          <h5 class="fw-bold mb-1">
            Revenue Overview
          </h5>

          <small class="text-muted">
            Monthly revenue performance
          </small>
        </div>

        <span class="badge rounded-pill bg-light text-dark">
          2026
        </span>

      </div>

    </div>

    <div class="card-body">

      <div class="revenue-chart-container">
        <canvas ref="chartCanvas"></canvas>
      </div>

    </div>

  </div>
</template>