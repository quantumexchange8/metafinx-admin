<script setup>
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Loading from "@/Components/Loading.vue";
import { onMounted, ref, watch } from "vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels';
import Button from '@/Components/Button.vue'
import BaseListbox from "@/Components/BaseListbox.vue";
import TotalXlcUnitDayChart from "@/Pages/XLCSetting/TotalXlcoinSupply/TotalXlcUnitDayChart.vue";
import TotalXlcUnitMonthChart from "@/Pages/XLCSetting/TotalXlcoinSupply/TotalXlcUnitMonthChart.vue";
import { RefreshIcon, SearchIcon } from "@heroicons/vue/outline";

const date = ref('');
const isLoading = ref(false);
const refresh = ref(false);

const props = defineProps({
    currentYear: Number,
})

const categories = ref({
    Daily: [],
    Monthly: [],
});

const months = Array.from({ length: 12 }, (_, index) => {
    const monthNumber = (index + 1) % 12 || 12; // Adjust the month number to be in the range 1-12
    const monthLabel = new Date(0, monthNumber - 1).toLocaleString('default', { month: 'long' });
    return { value: monthNumber, label: monthLabel };
});

const years = [
    {value: 2024, label: '2024'},
];

const selectedMonth = ref(new Date().getMonth() + 1); // Initialize with the current month (1-12)
const selectedYear = ref(new Date().getFullYear());

const filterType = ref('Daily');

const selectFilterType = (type) => {
    filterType.value = type
}

function refreshTable() {
    date.value = '';
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700">
        <div class="flex justify-between pb-3 border-b dark:border-gray-600">
            <div class="grid">
                <div class="text-xl font-semibold dark:text-white">
                    Total XL Coin Supply
                </div>
                <div class="text-xs font-normal dark:text-gray-400">
                    Overview data since 01 January 2023
                </div>
            </div>
            <div>
                <RefreshIcon
                    class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
                    aria-hidden="true"
                    @click="refreshTable"
                />
            </div>
        </div>

        <div class="flex flex-col dark:bg-transparent mt-2">
            <TabGroup>
                <div class="flex flex-wrap gap-5 w-full">
                    <TabList class="flex dark:bg-transparent w-full max-w-[362px]">
                        <Tab
                                v-for="category in Object.keys(categories)"
                                as="template"
                                :key="category"
                                v-slot="{ selected }"
                            >
                            <div class="w-full" role="group">
                                
                                <div v-if="category == 'Daily'">
                                    <button
                                        :class="[
                                        'w-full rounded-l-xl py-2.5 text-sm font-medium leading-5 text-gray-800 dark:text-gray-400',
                                        'border dark:border-gray-600',
                                        'ring-white ring-opacity-60 ring-offset-2 ring-offset-gray-400 focus:outline-none focus:ring-0',
                                        'dark:focus:outline-none',
                                        selected
                                        ? 'bg-white dark:bg-gray-600 text-gray-800 dark:text-white'
                                        : 'text-gray-800 dark:text-gray-400 hover:bg-white/[0.12] hover:text-white dark:hover:bg-gray-300/20',
                                        ]"
                                            @click="selectFilterType(category)"
                                        >
                                        {{ category }}
                                    </button>
                                </div>
                                <div v-else>
                                    <button
                                        :class="[
                                        'w-full rounded-r-xl py-2.5 text-sm font-medium leading-5 text-gray-800 dark:text-gray-400',
                                        'border dark:border-gray-600',
                                        'ring-white ring-opacity-60 ring-offset-2 ring-offset-gray-400 focus:outline-none focus:ring-0',
                                        'dark:focus:outline-none',
                                        selected
                                        ? 'bg-white dark:bg-gray-600 text-gray-800 dark:text-white'
                                        : 'text-gray-800 dark:text-gray-400 hover:bg-white/[0.12] hover:text-white dark:hover:bg-gray-300/20',
                                        ]"
                                            @click="selectFilterType(category)"
                                        >
                                        {{ category }}
                                    </button>
                                </div>
                                
                            </div>
                                
                            </Tab>
                    </TabList>
                    <div v-if="filterType==='Daily'" class="flex gap-5 w-[276px]">
                            <BaseListbox
                                v-model="selectedMonth"
                                :options="months"
                                class="w-32"
                            />
                            <BaseListbox
                                v-model="selectedYear"
                                :options="years"
                                class="w-32"
                            />
                    </div>
                    <div v-else>
                        <BaseListbox
                            v-if="filterType==='Monthly'"
                            v-model="selectedYear"
                            :options="years"
                        />
                    </div>
                </div>

                <TabPanels class="mt-2">
                    <TabPanel
                        :class="[
                        'rounded-xl dark:bg-transparent',
                        ]"
                    >
                        <TotalXlcUnitDayChart
                            :selectedMonth="selectedMonth"
                            :selectedYear="selectedYear"
                        />
                    </TabPanel>
                    <TabPanel
                        :class="[
                        'rounded-xl dark:bg-transparent',
                        ]"
                    >
                        <TotalXlcUnitMonthChart
                            :selectedYear="selectedYear"
                        />
                    </TabPanel>
                </TabPanels>
            </TabGroup>
        </div>
    </div>
</template>
  