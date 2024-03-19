<script setup>
import SubscriptionHistory from "@/Pages/IpoSchemeSetting/Partials/SubscriptionHistory.vue";
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import PendingDeposit from "@/Pages/Transaction/PendingTransaction/PendingDeposit.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {RefreshIcon, SearchIcon} from "@heroicons/vue/outline";
import Input from "@/Components/Input.vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import SubscriptionPending from "@/Pages/IpoSchemeSetting/Partials/SubscriptionPending.vue";
import PendingTransaction from "@/Pages/Transaction/PendingTransaction/PendingTransaction.vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import { usePermission } from "@/Composables/permissions";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const { hasRole, hasPermission } = usePermission();
const search = ref('');
const date = ref('');
const status = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const exportStatus = ref(false);

const statusList = [
  { value: '', label: 'All' },
  { value: 'CoolingPeriod', label: 'Cooling Period' },
  { value: 'OnGoingPeriod', label: 'On Going Period' },
  { value: 'MaturityPeriod', label: 'Maturity Period' },
  { value: 'Terminated', label: 'Terminated' },

];

function refreshTable() {
    search.value = '';
    date.value = '';
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const exportSubscription = () => {
    exportStatus.value = true;
}

const clearFilters = () => {
    search.value = '';
    date.value = '';
    status.value = '';
    isLoading.value = true;
    refresh.value = true;
};
</script>

<template>
    <div class="flex justify-between">
        <h4 class="font-semibold dark:text-white">Subscriptions</h4>
        <RefreshIcon
            @click="refreshTable"
            class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
            aria-hidden="true"
        />
    </div>
    <div>
        <div class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
            <div class="w-full">
                <InputIconWrapper>
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
            <div class="w-full flex justify-end items-center space-x-3">
                <Button
                    type="button"
                    class="w-full md:w-auto flex items-center justify-center px-3 py-2 border border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                    variant="transparent"
                    @click="clearFilters"
                >
                    Clear
                </Button>
                <Button
                    type="button"
                    class="w-full md:w-auto flex items-center justify-center px-3 py-2 border border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                    variant="transparent"
                    @click="exportSubscription"
                    v-if="hasRole('admin') || hasPermission('ExportInvestmentHistory')"
                >
                    <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />
                    <span>Export</span>
                </Button>
            </div>
        </div>
    </div>


    <TabGroup>
        <TabList class="max-w-xs flex py-1">
            <Tab
                as="template"
                v-slot="{ selected }"
                v-if="hasRole('admin') || hasPermission('ViewSubscriptionHistory')"
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
                    Subscription History
                </button>
            </Tab>
            <Tab
                as="template"
                v-slot="{ selected }"
                v-if="hasRole('admin') || hasPermission('ViewPendingEBMI')"
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
                    Pending EBMI
                </button>
            </Tab>
        </TabList>
        <TabPanels>
            <TabPanel v-if="hasRole('admin') || hasPermission('ViewSubscriptionHistory')">
                <SubscriptionHistory
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
            <TabPanel v-if="hasRole('admin') || hasPermission('ViewPendingEBMI')">
                <SubscriptionPending
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
        </TabPanels>
    </TabGroup>
</template>
