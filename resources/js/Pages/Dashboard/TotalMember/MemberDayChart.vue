<script setup>
import Loading from "@/Components/Loading.vue";
import {onMounted, ref, watch} from "vue";
import Chart from 'chart.js/auto'

const props = defineProps({
    selectedMonth: Number
})

const chartData = ref({
    labels: [],
    datasets: [],
});
const isLoading = ref(false)
const month = ref(props.selectedMonth)
let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('dailyTotalMembers');

        isLoading.value = true;

        const response = await axios.get('/getTotalMembersByDays', { params: { month: month.value } });
        const { labels, datasets } = response.data;
        chartData.value.labels = labels;
        chartData.value.datasets = datasets;
        isLoading.value = false

        // Create the chart after updating chartData
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: chartData.value,
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        stacked: true,
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
                        stacked: true,
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
        const ctx = document.getElementById('dailyTotalMembers');

        isLoading.value = false
        console.error('Error fetching chart data:', error);
    }
}

onMounted(async () => {
    await fetchData(); // Fetch data on mount

    // Watch for changes in the date and fetch data when it changes

    watch(
        () => props.selectedMonth, // Expression to watch
        (newMonth) => {
            // This callback will be called when selectedMonth changes.
            month.value = newMonth;
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
        <canvas id="dailyTotalMembers" height="350"></canvas>
    </div>
</template>
