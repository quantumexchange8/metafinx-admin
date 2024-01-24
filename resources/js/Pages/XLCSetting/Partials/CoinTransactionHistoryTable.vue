<script setup>
import CoinTransactionHistory from "@/Pages/XLCSetting/Partials/CoinTransactionHistory.vue";
import StackingHistory from "@/Pages/XLCSetting/Partials/StackingHistory.vue";
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {RefreshIcon, SearchIcon} from "@heroicons/vue/outline";
import Input from "@/Components/Input.vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import BaseListbox from "@/Components/BaseListbox.vue";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const search = ref('');
const date = ref('');
const status = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const exportStatus = ref(false);

const statusList = [

  { value: '', label: 'All' },
  { value: 'BuyCoin', label: 'Buy Coin' },
  { value: 'Stacking', label: 'Stacking' },
  { value: 'SwapCoin', label: 'Swap Coin' },

];

function refreshTable() {
    search.value = '';
    date.value = '';
    status.value = '';
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const exportTransaction = () => {
    exportStatus.value = true;
}
</script>

<template>
    <div class="flex justify-between">
        <h4 class="font-semibold dark:text-white">MXT History</h4>
        <RefreshIcon
            @click="refreshTable"
            class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
            aria-hidden="true"
        />
    </div>
    <div class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
        <div class="w-full">
            <InputIconWrapper class="md:col-span-2">
                <template #icon>
                    <SearchIcon aria-hidden="true" class="w-5 h-5" />
                </template>
                <Input withIcon id="search" type="text" class="block w-full border border-transparent" placeholder="Search" v-model="search" />
            </InputIconWrapper>
        </div>
        <div class="w-full">
            <vue-tailwind-datepicker
                placeholder="Select dates"
                :formatter="formatter"
                separator=" - "
                v-model="date"
                input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
            />
        </div>
        <div class="w-full">
            <BaseListbox 
                v-model="status" 
                :options="statusList" 
                placeholder="Filter status"
            />
        </div>
        <div class="flex justify-end w-full">
            <Button
                type="button"
                class="justify-center w-full md:w-full gap-2 border border-gray-600 text-white text-sm dark:hover:bg-gray-600"
                variant="transparent"
                @click="exportTransaction"
            >
                <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />
                <span>Export as Excel</span>
            </Button>
        </div>
    </div>



    <TabGroup>
        <TabList class="max-w-xs flex py-1">
            <Tab 
                as="template"
                v-slot="{ selected }"
            >
                <button
                    :class="[
                              'w-full py-2.5 text-sm font-semibold dark:text-gray-400',
                              'ring-white ring-offset-0 focus:outline-none focus:ring-0',
                              selected
                                ? 'dark:text-white border-b-2'
                                : 'border-b border-gray-400',
                           ]"
                >
                    Transaction History
                </button>
            </Tab>
            <Tab
                as="template"
                v-slot="{ selected }"
            >
                <button
                    :class="[
                            'w-full py-2.5 text-sm font-semibold dark:text-gray-400',
                            'ring-white ring-offset-0 focus:outline-none focus:ring-0',
                            selected
                                ? 'dark:text-white border-b-2'
                                : 'border-b border-gray-400',
                        ]"
                >
                    Stacking History
                </button>
            </Tab>
        </TabList>
        <TabPanels>
            <TabPanel>
                <CoinTransactionHistory
                    :refresh="refresh"
                    :isLoading="isLoading"
                    :search="search"
                    :date="date"
                    :status="status"
                    :exportStatus="exportStatus"
                    @update:loading="isLoading = $event"
                    @update:refresh="refresh = $event"
                    @update:export="exportStatus = $event"
                />
            </TabPanel>
            <TabPanel>
                <StackingHistory
                    :refresh="refresh"
                    :isLoading="isLoading"
                    :search="search"
                    :date="date"
                    :exportStatus="exportStatus"
                    @update:loading="isLoading = $event"
                    @update:refresh="refresh = $event"
                    @update:export="exportStatus = $event"
                />
            </TabPanel>
        </TabPanels>
    </TabGroup>
</template>
