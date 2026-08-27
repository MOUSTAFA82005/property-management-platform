<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js'

Chart.register(
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend
)

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

const values = computed(() => [
  props.units?.available ?? 0,
  props.units?.occupied ?? 0,
  props.units?.reserved ?? 0,
])

let unitsChart = null

onMounted(() => {

    unitsChart = new Chart(
        chartCanvas.value,
        {
            type: 'doughnut',

            data: {
                labels: [
                    'Available',
                    'Occupied',
                    'Reserved'
                ],

                datasets: [
                    {
                        data: values.value,

                        backgroundColor: [
                            '#864CFF',
                            '#47BFFF',
                            '#F5B83D'
                        ],

                        borderWidth: 0,

                        hoverOffset: 8
                    }
                ]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: '70%',

                plugins: {

                    legend: {
                        position: 'bottom',

                        labels: {
                            usePointStyle: true,

                            padding: 20
                        }
                    }

                }
            }
        }
    )

})

watch(values, () => {
    if (!unitsChart) return
    unitsChart.data.datasets[0].data = values.value
    unitsChart.update()
})

onBeforeUnmount(() => {

    if (unitsChart) {
        unitsChart.destroy()
    }

})
</script>

<template>

    <div class="card border-0 shadow-sm h-100">

        <div class="card-header bg-white border-0 p-4">

            <h5 class="fw-bold mb-1">
                Unit Occupancy
            </h5>

            <small class="text-muted">
                Current unit status
            </small>

        </div>

        <div class="card-body">

            <div style="height: 300px;">

                <canvas ref="chartCanvas"></canvas>

            </div>

        </div>

    </div>

</template>