<script setup>
import Loading from "@/Components/Loading.vue";
import {onMounted, ref, watch} from "vue";
import Chart from 'chart.js/auto'

const props = defineProps({
    selectedYear: Number
})

const chartData = ref({
    labels: [],
    datasets: [],
});
const isLoading = ref(false)
const month = ref(props.selectedYear)
let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('dailyTotalMembers');

        isLoading.value = true;

        const response = await axios.get('/getTotalInvestmentByMonths', { params: { year: month.value } });
        const { labels, datasets } = response.data;
        chartData.value.labels = labels;
        chartData.value.datasets = datasets;

        const gradientColors = [
            [
                'rgba(50, 173, 230, 0.40)',
                'rgba(50, 173, 230, 0.00)',
                'CoolingPeriod'
            ],
            [
                'rgba(253, 176, 34, 0.40)',
                'rgba(253, 176, 34, 0.00)',
                'OngoingPeriod'
            ],
            [
                'rgba(0, 199, 190, 0.40)',
                'rgba(0, 199, 190, 0.00)',
                'MaturityPeriod'
            ],
            [
                'rgba(255, 45, 85, 0.40)',
                'rgba(255, 45, 85, 0.00)',
                'Terminated'
            ]
        ];

        if (datasets.length > 0) {
            datasets.forEach((dataset, index) => {
                if (dataset) {
                    const label = dataset.label;
                    const matchingColor = gradientColors.find(colorData => colorData[2] === label);

                    if (matchingColor) {
                        dataset.backgroundColor = (context) => {
                            const bgColor = matchingColor;

                            if (!context.chart.chartArea) {
                                return;
                            }

                            const { ctx, chartArea: { top, bottom } } = context.chart;
                            const gradientBg = ctx.createLinearGradient(0, top, 0, bottom);
                            gradientBg.addColorStop(0, bgColor[0]);
                            gradientBg.addColorStop(1, bgColor[1]);
                            return gradientBg;
                        };
                    }
                }
            });
        }

        isLoading.value = false

        // Create the chart after updating chartData
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: chartData.value,
            options: {
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                var ranges = [
                                    { divider: 1e6, suffix: 'M' },
                                    { divider: 1e3, suffix: 'k' }
                                ];
                                function formatNumber(n) {
                                    for (var i = 0; i < ranges.length; i++) {
                                        if (n >= ranges[i].divider) {
                                            return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                        }
                                    }
                                    return n;
                                }
                                return formatNumber(value);
                            },
                            color: '#9DA4AE',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
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
                            pointStyle: 'circle',
                            boxHeight: 8,
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
        () => props.selectedYear, // Expression to watch
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
