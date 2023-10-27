<script setup>
import Loading from "@/Components/Loading.vue";
import {onMounted, ref, watch} from "vue";
import Chart from 'chart.js/auto'

const props = defineProps({
    selectedYear: Number
})

const chartData = ref({
    labels: [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ],
    datasets: [],
});
const isLoading = ref(false)
const year = ref(props.selectedYear)
let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('totalMembers');

        isLoading.value = true;

        const response = await axios.get('/getTotalMembers', { params: { year: year.value } });
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
        chartInstance = new Chart(ctx, {
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
}

onMounted(async () => {
    await fetchData(); // Fetch data on mount

    // Watch for changes in the date and fetch data when it changes

    watch(
        () => props.selectedYear, // Expression to watch
        (newYear) => {
            // This callback will be called when selectedMonth changes.
            year.value = newYear;
            fetchData();
        }
    );

});
</script>

<template>
    <div v-if="isLoading" class="flex justify-center">
        <Loading />
    </div>
    <div>
        <canvas id="totalMembers" height="350"></canvas>
    </div>

</template>
