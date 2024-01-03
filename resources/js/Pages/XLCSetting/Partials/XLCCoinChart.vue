<script setup>
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Loading from "@/Components/Loading.vue";
import { onMounted, ref, watch } from "vue";
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels';
import Button from '@/Components/Button.vue'
const date = ref('');

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

// const chartData = ref({
//     labels: [],
//     datasets: [{
//         data: [],
//         backgroundColor: [
//             '#FF2D55',
//             '#FDB022',
//             '#6C737F',
//             '#209CEE',
//         ],
//         hoverOffset: 4
//     }]
// });
// const isLoading = ref(false)

// let chartInstance = null; // Variable to store the chart instance

// const fetchData = async () => {
//     try {
//         const ctx = document.getElementById('planChart');

//         const response = await axios.get('/ipo_scheme/getSelectedPlans', { params: { date: date.value } });
//         const { labels, datasetData } = response.data;
//         // Update chartData
//         chartData.value.labels = labels;
//         chartData.value.datasets[0].data = datasetData;

//         // Destroy previous chart instance if exists
//         if (chartInstance) {
//             chartInstance.destroy();
//         }

//         chartInstance = new Chart(ctx, {
//             type: 'pie',
//             data: chartData.value,
//             plugins: [ChartDataLabels],
//             options: {
//                 maintainAspectRatio: false,
//                 plugins: {
//                     legend: {
//                         labels: {
//                             font: {
//                                 family: 'Inter, sans-serif',
//                                 size: 14,
//                                 weight: 400,
//                             },
//                             padding: 20,
//                             color: '#fff',
//                             usePointStyle: true,
//                             boxHeight: 8
//                         },
//                         align: 'center',
//                         position: 'bottom',
//                     },
//                     datalabels: {
//                         formatter: function(value, context) {
//                             const dataset = context.chart.data.datasets[context.datasetIndex];
//                             const sum = dataset.data.reduce((acc, curr) => acc + curr, 0);
//                             const percentage = Math.round((value / sum) * 100);
//                             return percentage + '%';
//                         },
//                         color: '#fff'
//                     }
//                 },
//             },
//         });
//     } catch (error) {
//         console.error('Error fetching data:', error);
//     }
// };

// onMounted(() => {
//     fetchData(); // Fetch data on mount

//     // Watch for changes in the date and fetch data when it changes
//     watch(date, () => {
//         fetchData();
//     });
// });

</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700">
        <div class="flex justify-between pb-3 border-b dark:border-gray-600">
            <div class="grid">
                <div class="text-xl font-semibold dark:text-white">
                    XLC Coin Chart
                </div>
                <div class="text-xs font-normal dark:text-gray-400">
                    Overview data since 01 January 2023
                </div>
            </div>
        </div>

        <div class="flex dark:bg-transparent mt-2">
            <div class="flex dark:bg-transparent">
                <TabList class="flex">
                    <Tab v-for="timeFrame in ['1M', '3M', '6M', 'All Time']" :key="timeFrame"
                        :selected="selectedTimeFrame === timeFrame" @click="selectTimeFrame(timeFrame)">
                        <button
                            class="px-4 py-2.5 text-sm font-semibold text-gray-900 border border-gray-500 focus:outline-none w-24"
                            :class="{
                                'rounded-l-xl': timeFrame === '1M',
                                'rounded-r-xl': timeFrame === 'All Time',
                                'hover:bg-gray-100 dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600': true,
                                'bg-gray-300 dark:bg-gray-800': selectedTimeFrame === timeFrame,
                                'bg-transparent dark:bg-[#38425080] dark:text-white': selectedTimeFrame !== timeFrame
                            }">
                            <span>{{ timeFrame }}</span>
                        </button>
                    </Tab>
                </TabList>

                <vue-tailwind-datepicker placeholder="Select dates" :formatter="formatter" separator=" - " v-model="date"
                    input-classes="ml-2 py-2.5 border-gray-700 w-20 rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-700 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white" />
            </div>
        </div>
    </div>
</template>
  