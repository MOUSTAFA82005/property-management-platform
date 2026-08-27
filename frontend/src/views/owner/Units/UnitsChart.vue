<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

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

const chartCanvas = ref(null)

let unitsChart = null

onMounted(() => {

    unitsChart = new Chart(
        chartCanvas.value,
        {
            type: 'doughnut',

            data: {
                labels: [
                    'Available',
                    'Reserved',
                    'Sold'
                ],

                datasets: [
                    {
                        data: [
                            45,
                            20,
                            35
                        ],

                        backgroundColor: [
                            '#864CFF',
                            '#F5B83D',
                            '#E85D75'
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