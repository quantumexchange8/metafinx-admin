<script setup>
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import {ref} from "vue";
import PayoutPairingEarningDayChart from "@/Pages/Report/Charts/TotalPairingEarning/PayoutPairingEarningDayChart.vue";
import PayoutPairingEarningMonthChart from "@/Pages/Report/Charts/TotalPairingEarning/PayoutPairingEarningMonthChart.vue";
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/outline'

const props = defineProps({
    currentYear: Number,
    activePayout: String,
    search: String,
    date: String,
    type: String,
})

const categories = ref({
    Daily: [],
    Monthly: [],
})

const months = Array.from({ length: 12 }, (_, index) => {
    const monthNumber = (index + 1) % 12; // Adjust the month number to be in the range 1-12
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
</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700">
        <Disclosure :defaultOpen="true" v-slot="{ open }">
            <DisclosureButton
            class="flex w-full justify-between rounded-lg bg-purple-100 px-4 py-2 text-left text-sm font-medium text-purple-900 hover:bg-purple-200 focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75"
            >
                <div class="flex justify-between">
                    <div class="grid">
                        <span class="text-xl font-semibold dark:text-white">{{ props.activePayout }}</span>
                        <span class="text-xs font-normal dark:text-gray-400">{{currentYear}} {{ props.activePayout }} Data</span>
                    </div>
                </div>
                <ChevronDownIcon
                    :class="open ? 'rotate-180 transform' : ''"
                    class="h-5 w-5 text-purple-500"
                />
            </DisclosureButton>
            <DisclosurePanel class="px-4 pb-2 pt-2 text-sm text-gray-500">
                <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-600">

                <div class="w-full">
                    <TabGroup>
                        <div class="flex w-full gap-5">
                            <TabList class="flex rounded-xl bg-gray-900/20 dark:bg-transparent w-full max-w-md">
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
                            <div class="w-36">
                                <BaseListbox
                                    v-if="filterType==='Daily'"
                                    v-model="selectedMonth"
                                    :options="months"
                                />
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
                                <PayoutPairingEarningDayChart
                                    
                                    :selectedMonth="selectedMonth"
                                    :selectedYear="selectedYear"
                                    :search="props.search"
                                    :date="props.date"
                                    :type="props.type"
                                />
                            </TabPanel>
                            <TabPanel
                                :class="[
                                'rounded-xl dark:bg-transparent p-3',
                                ]"
                            >
                                <PayoutPairingEarningMonthChart
                                    :selectedYear="selectedYear"
                                    :search="props.search"
                                    :date="props.date"
                                />
                            </TabPanel>
                        </TabPanels>
                    </TabGroup>
                </div>
            
            </DisclosurePanel>
        </Disclosure>
    </div>
</template>
