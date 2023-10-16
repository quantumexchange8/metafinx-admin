<script setup>
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Loading from "@/Components/Loading.vue";
import {onMounted, ref, watch} from "vue";
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels';
const date = ref('');

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const chartData = ref({
    labels: [],
    datasets: [{
        data: [],
        backgroundColor: [
            '#FF2D55',
            '#FDB022',
            '#6C737F'
        ],
        hoverOffset: 4
    }]
});
const isLoading = ref(false)

let chartInstance = null; // Variable to store the chart instance

const fetchData = async () => {
    try {
        const ctx = document.getElementById('planChart');

        const response = await axios.get('/ipo_scheme/getSelectedPlans', { params: { date: date.value } });
        const { labels, datasetData } = response.data;
        // Update chartData
        chartData.value.labels = labels;
        chartData.value.datasets[0].data = datasetData;

        // Destroy previous chart instance if exists
        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'pie',
            data: chartData.value,
            plugins: [ChartDataLabels],
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                            padding: 20,
                            color: '#fff',
                            usePointStyle: true,
                            boxHeight: 8
                        },
                        align: 'center',
                        position: 'bottom',
                    },
                    datalabels: {
                        formatter: function(value, context) {
                            const dataset = context.chart.data.datasets[context.datasetIndex];
                            const sum = dataset.data.reduce((acc, curr) => acc + curr, 0);
                            const percentage = Math.round((value / sum) * 100);
                            return percentage + '%';
                        },
                        color: '#fff'
                    }
                },
            },
        });
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

onMounted(() => {
    fetchData(); // Fetch data on mount

    // Watch for changes in the date and fetch data when it changes
    watch(date, () => {
        fetchData();
    });
});

</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700">
        <div class="flex justify-between pb-3 border-b dark:border-gray-600">
            <div class="grid">
                <div class="text-xl font-semibold dark:text-white">
                    Most Selected Plans
                </div>
                <div class="text-xs font-normal dark:text-gray-400">
                    Current Month Investment Data
                </div>
            </div>
        </div>

        <div class="mt-5 space-y-5">
            <div class="md:w-1/3">
                <vue-tailwind-datepicker
                    placeholder="Select dates"
                    :formatter="formatter"
                    separator=" - "
                    v-model="date"
                    input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                />
            </div>
            <div>
                <canvas id="planChart"></canvas>
            </div>
        </div>
    </div>
</template>
