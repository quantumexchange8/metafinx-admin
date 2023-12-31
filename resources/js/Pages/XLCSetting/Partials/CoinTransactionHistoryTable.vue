<script setup>
import CoinTransactionHistory from "@/Pages/XLCSetting/Partials/CoinTransactionHistory.vue";
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {RefreshIcon, SearchIcon} from "@heroicons/vue/outline";
import Input from "@/Components/Input.vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";

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

const exportTransaction = () => {
    exportStatus.value = true;
}
</script>

<template>
    <div class="flex justify-between">
        <h4 class="font-semibold dark:text-white">Coin Transaction History</h4>
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
                @click="exportTransaction"
            >
                <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />
                <span>Export as Excel</span>
            </Button>
        </div>
    </div>

    <TabGroup>
        <TabPanels>
                <CoinTransactionHistory
                    :refresh="refresh"
                    :isLoading="isLoading"
                    :search="search"
                    :date="date"
                    :exportStatus="exportStatus"
                    @update:loading="isLoading = $event"
                    @update:refresh="refresh = $event"
                    @update:export="exportStatus = $event"
                />
        </TabPanels>
    </TabGroup>
</template>
