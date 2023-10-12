<script setup>
import Chart from 'chart.js/auto'
import {onMounted, ref} from "vue";
import Loading from "@/Components/Loading.vue";

const chartData = ref({
    labels: [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ],
    datasets: [],
});
const isLoading = ref(false)

onMounted(async () => {
    const ctx = document.getElementById('totalMembers');

    try {
        isLoading.value = true;

        const response = await axios.get('/getTotalMembers', { params: { year: 2023 } });
        const data = response.data.data;
        const backgroundColors = ['#FFB2AB', '#FF2D55', '#FEC84B', '#F79009'];
        const dataLabels = ['Member', 'LVL 1', 'LVL 2', 'LVL 3'];

        // Initialize datasets based on the number of setting ranks
        for (let settingRankId = 1; settingRankId <= 4; settingRankId++) {
            const dataset = {
                label: dataLabels[settingRankId - 1],
                data: data.map(item => item[`totalRankId${settingRankId}`]),
                borderWidth: 1,
                borderRadius: Number.MAX_VALUE,
                borderSkipped: false,
                backgroundColor: backgroundColors[settingRankId - 1],
            };

            chartData.value.datasets.push(dataset);
        }
        isLoading.value = false

        // Create the chart after updating chartData
        new Chart(ctx, {
            type: 'bar',
            data: chartData.value,
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            color: '#9DA4AE',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                            stepSize: 1
                        },
                        grace: '10%',
                        beginAtZero: true
                    },
                    x: {
                        ticks: {
                            color: '#9DA4AE',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                        },
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                            color: '#fff',
                            usePointStyle: true,
                            boxHeight: 8
                        },
                        align: 'start',
                    },
                }
            }
        });
    } catch (error) {
        isLoading.value = false
        console.error('Error fetching chart data:', error);
    }
});

</script>

<template>
    <div>
        <div v-if="isLoading" class="flex justify-center mt-8">
            <Loading />
        </div>
        <canvas id="totalMembers" height="350"></canvas>
    </div>
</template>
