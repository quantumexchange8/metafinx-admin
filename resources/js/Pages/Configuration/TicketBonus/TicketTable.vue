<script setup>
import { RefreshIcon, SearchIcon, ArrowLeftIcon, ArrowRightIcon } from "@heroicons/vue/outline";
import {usePage} from "@inertiajs/vue3";
import {ref, watch, watchEffect} from "vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Button from "@/Components/Button.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import Loading from "@/Components/Loading.vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const tickets = ref({data: []});
const search = ref('');
const date = ref('');
const currentPage = ref(1);
const isLoading = ref(false);
const refresh = ref(false);

const { formatAmount, formatDate, formatDateTime } = transactionFormat();

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

watch(
    [search, date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/configuration/getTicketBonus?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        tickets.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false
    }
}

getResults()

const handlePageChange = (newPage) => {
    if (newPage >= 1) {
        currentPage.value = newPage;
        const dateRange = date.value.split(' - ');
        getResults(currentPage.value, dateRange, search.value);
    }
};

watchEffect(() => {
    if (usePage().props.title !== null) {
        getResults();
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400 dark:enabled:hover:text-white'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

watch(() => refresh.value, (newVal) => {
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        refresh.value = false;
    }
});
</script>

<template>
    <div class="p-5 flex flex-col gap-3 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700 w-full md:w-4/5">
        <div class="flex justify-between">
            <h4 class="font-semibold dark:text-white">History</h4>
            <RefreshIcon
                :class="{ 'animate-spin': isLoading }"
                class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
                aria-hidden="true"
                @click="refreshTable"
            />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <InputIconWrapper>
                    <template #icon>
                        <SearchIcon aria-hidden="true" class="w-5 h-5" />
                    </template>
                    <Input
                        withIcon
                        id="search"
                        type="text"
                        class="block w-full border border-gray-300 dark:border-gray-600"
                        placeholder="Search"
                        v-model="search"
                    />
                </InputIconWrapper>
            </div>
            <div class="md:col-span-2">
                <vue-tailwind-datepicker
                    placeholder="Select dates"
                    :formatter="formatter"
                    separator=" - "
                    v-model="date"
                    input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                />
            </div>
        </div>
        <div class="relative overflow-x-auto sm:rounded-lg">
            <div v-if="isLoading" class="w-full flex justify-center my-8">
                <Loading />
            </div>
            <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 table-fixed">
                <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                <tr>
                    <th scope="col" class="px-3 py-4">
                        Date
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Release Date
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Total Tickets
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Amount
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="tickets.data.length === 0">
                    <th colspan="4" class="py-4 text-lg text-center">
                        No History
                    </th>
                </tr>
                <tr
                    v-for="ticket in tickets.data"
                    class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    <td class="px-3 py-4">
                        {{ formatDateTime(ticket.created_at) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        {{ formatDateTime(ticket.release_date, false) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        2000
                    </td>
                    <td class="px-3 py-4 text-center">
                        ${{ formatAmount(ticket.amount) }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="flex justify-center mt-4" v-if="!isLoading">
                <TailwindPagination
                    :item-classes=paginationClass
                    :active-classes=paginationActiveClass
                    :data="tickets"
                    :limit=2
                    @pagination-change-page="handlePageChange"
                >
                    <template #prev-nav>
                        <span class="flex gap-2"><ArrowLeftIcon class="w-5 h-5" /> Previous</span>
                    </template>
                    <template #next-nav>
                        <span class="flex gap-2">Next <ArrowRightIcon class="w-5 h-5" /></span>
                    </template>
                </TailwindPagination>
            </div>
        </div>
    </div>
</template>
