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

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const search = ref('');
const date = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const exportStatus = ref(false);

function refreshTable() {
    search.value = '';
    date.value = '';
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const exportSubscription = () => {
    exportStatus.value = true;
}
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
    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="w-full">
            <InputIconWrapper class="md:col-span-2">
                <template #icon>
                    <SearchIcon aria-hidden="true" class="w-5 h-5" />
                </template>
                <Input withIcon id="search" type="text" class="block w-full border border-transparent" placeholder="Search" v-model="search" />
            </InputIconWrapper>
        </div>
        <div class="md:w-2/3">
            <vue-tailwind-datepicker
                placeholder="Select dates"
                :formatter="formatter"
                separator=" - "
                v-model="date"
                input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
            />
        </div>
        <div class="flex justify-end">
            <Button
                type="button"
                class="justify-center w-full md:w-2/3 gap-2 border border-gray-600 text-white text-sm dark:hover:bg-gray-600"
                variant="transparent"
                @click="exportSubscription"
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
                    Pending EBMI
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
                    Subscription History
                </button>
            </Tab>
        </TabList>
        <TabPanels>
            <TabPanel>
                <SubscriptionPending
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
            <TabPanel>
                <SubscriptionHistory
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
