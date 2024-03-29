<script setup>
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import MemberMonthChart from "@/Pages/Dashboard/TotalMember/MemberMonthChart.vue";
import MemberDayChart from "@/Pages/Dashboard/TotalMember/MemberDayChart.vue";
import {ref} from "vue";
import TransactionDayChart from "@/Pages/Dashboard/TotalTransaction/TransactionDayChart.vue";
import TransactionMonthChart from "@/Pages/Dashboard/TotalTransaction/TransactionMonthChart.vue";
import {transactionFormat} from "@/Composables/index.js";
import TotalInvestmentDayChart from "@/Pages/Dashboard/TotalInvestment/TotalInvestmentDayChart.vue";
import TotalInvestmentMonthChart from "@/Pages/Dashboard/TotalInvestment/TotalInvestmentMonthChart.vue";

const props = defineProps({
    currentYear: Number,
    currentTotalInvestment: String,
})

const categories = ref({
    Daily: [],
    Monthly: [],
})

const months = Array.from({ length: 12 }, (_, index) => {
    const monthNumber = (index + 1) % 12 || 12; // Adjust the month number to be in the range 1-12
    const monthLabel = new Date(0, monthNumber - 1).toLocaleString('default', { month: 'long' });
    return { value: monthNumber, label: monthLabel };
});

const years = [
    {value: 2023, label: '2023'},
    {value: 2024, label: '2024'},
];

const selectedMonth = ref(new Date().getMonth() + 1); // Initialize with the current month (1-12)
const selectedYear = ref(new Date().getFullYear());
const { formatAmount } = transactionFormat();

const filterType = ref('Daily');

const selectFilterType = (type) => {
    filterType.value = type
}
</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700 my-8">
        <div class="flex justify-between">
            <div class="grid">
                <span class="text-xl font-semibold dark:text-white">Total Investment</span>
                <span class="text-xs font-normal dark:text-gray-400">{{currentYear}} Total Transaction Data</span>
            </div>
            <div class="grid text-right">
                <span class="text-xl font-semibold dark:text-white">$ {{ props.currentTotalInvestment }}</span>
                <span class="text-xs font-normal dark:text-gray-400">current active investment</span>
            </div>
        </div>
        <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-600">

        <div class="w-full">
            <TabGroup>
                <div class="flex flex-wrap flex-row w-full gap-5">
                    <TabList class="flex space-x-1 rounded-xl bg-gray-900/20 dark:bg-transparent w-full max-w-md">
                        <Tab
                            v-for="category in Object.keys(categories)"
                            as="template"
                            :key="category"
                            v-slot="{ selected }"
                        >
                            <button
                                :class="[
                            'w-full rounded-lg py-2.5 text-sm font-medium leading-5 text-gray-800 dark:text-gray-400',
                            'border dark:border-gray-600',
                            'ring-white ring-opacity-60 ring-offset-2 ring-offset-gray-400 focus:outline-none focus:ring-0',
                            selected
                            ? 'bg-white dark:bg-gray-600 text-gray-800 dark:text-white'
                            : 'text-gray-800 dark:text-gray-400 hover:bg-white/[0.12] hover:text-white dark:hover:bg-gray-300/20',
                            ]"
                                @click="selectFilterType(category)"
                            >
                                {{ category }}
                            </button>
                        </Tab>
                    </TabList>
                    <div v-if="filterType==='Daily'" class="flex gap-5 w-80">
                        <BaseListbox
                            v-model="selectedMonth"
                            :options="months"
                            class="w-40"
                        />
                        <BaseListbox
                            v-model="selectedYear"
                            :options="years"
                            class="w-40"
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
                        'rounded-xl dark:bg-transparent p-3',
                        ]"
                    >
                        <TotalInvestmentDayChart
                            :selectedMonth="selectedMonth"
                            :selectedYear="selectedYear"
                        />
                    </TabPanel>
                    <TabPanel
                        :class="[
                        'rounded-xl dark:bg-transparent p-3',
                        ]"
                    >
                        <TotalInvestmentMonthChart
                            :selectedYear="selectedYear"
                        />
                    </TabPanel>
                </TabPanels>
            </TabGroup>
        </div>
    </div>
</template>
