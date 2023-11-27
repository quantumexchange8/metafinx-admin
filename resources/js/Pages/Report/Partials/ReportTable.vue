<script setup>
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {SearchIcon, RefreshIcon} from "@heroicons/vue/outline";
import {ref, watch} from "vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";
import Loading from "@/Components/Loading.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import MonthlyReturnPayout from "@/Pages/Report/Partials/MonthlyReturnPayout.vue";
import QuarterlyDividendPayout from "@/Pages/Report/Partials/QuarterlyDividendPayout.vue";
import ReferralEarningPayout from "@/Pages/Report/Partials/ReferralEarningPayout.vue";
import AffiliateEarningPayout from "@/Pages/Report/Partials/AffiliateEarningPayout.vue";
import DividendEarningPayout from "@/Pages/Report/Partials/DividendEarningPayout.vue";
import TicketBonusPayout from "@/Pages/Report/Partials/TicketBonusPayout.vue";

const emit = defineEmits(['search', 'date', 'update:export'])
const props = defineProps({
    activePayout: String,
    exportStatus: Boolean,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const search = ref('');
const date = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const { formatDateTime, formatAmount } = transactionFormat();

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

watch(
    [() => search.value, () => date.value],
    debounce(([searchValue, dateValue]) => {
        emit('search', searchValue)
        emit('date', dateValue)
    }, 300)
);

</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="w-full">
            <InputIconWrapper class="md:col-span-2">
                <template #icon>
                    <SearchIcon aria-hidden="true" class="w-5 h-5" />
                </template>
                <Input withIcon id="search" type="text" class="block w-full dark:border-transparent" placeholder="Search" v-model="search" />
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
        <div class="flex justify-end items-center">
            <RefreshIcon
            :class="{ 'animate-spin': isLoading }"
            class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
            aria-hidden="true"
            @click="refreshTable"
            />
        </div>
    </div>

    <div class="relative overflow-x-auto sm:rounded-lg">
        <MonthlyReturnPayout
            v-if="activePayout==='Monthly_Return'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
        <QuarterlyDividendPayout
            v-if="activePayout==='Quarterly_Dividend'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
        <ReferralEarningPayout
            v-if="activePayout==='referral_earnings'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
        <AffiliateEarningPayout
            v-if="activePayout==='Affiliate_Earning'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
        <DividendEarningPayout
            v-if="activePayout==='Dividend_Earning'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
        <TicketBonusPayout
            v-if="activePayout==='Ticket_Bonus'"
            :refresh="refresh"
            :isLoading="isLoading"
            :search="search"
            :date="date"
            :type="activePayout"
            :exportStatus = "exportStatus"
            @update:loading="isLoading = $event"
            @update:refresh="refresh = $event"
            @update:export="$emit('update:export', $event)"
        />
    </div>

</template>
