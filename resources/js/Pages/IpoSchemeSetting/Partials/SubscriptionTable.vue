<script setup>
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {SearchIcon, RefreshIcon, ArrowLeftIcon, ArrowRightIcon} from "@heroicons/vue/outline";
import {ref, watch} from "vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";
import Loading from "@/Components/Loading.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import Modal from "@/Components/Modal.vue";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const subscriptions = ref({data: []});
const search = ref('');
const date = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const currentPage = ref(1);
const { formatDateTime, formatAmount, formatType } = transactionFormat();
const subscriptionDetailModal = ref(false);
const subscriptionDetail = ref();

watch(
    [search, date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/ipo_scheme/getSubscriptionDetails?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        subscriptions.value = response.data;
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

watch(() => refresh, (newVal) => {
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

const openSubscriptionDetailModal = (subscription) => {
    subscriptionDetailModal.value = true
    subscriptionDetail.value = subscription
}

const closeModal = () => {
    subscriptionDetailModal.value = false
}
</script>

<template>
    <div class="flex justify-between">
        <h4 class="font-semibold dark:text-white">Subscriptions</h4>
        <RefreshIcon
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
                <Input withIcon id="search" type="text" class="block w-full" placeholder="Search" v-model="search" />
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
            >
                <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />
                <span>Export as Excel</span>
            </Button>
        </div>
    </div>

    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="isLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
            <tr>
                <th scope="col" class="px-3 py-2.5">
                    Name
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Date
                </th>
                <th scope="col" class="px-3 py-2.5">
                    ID Number
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Amount
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Status
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="subscriptions.data.length === 0">
                <th colspan="5" class="py-4 text-lg text-center">
                    No History
                </th>
            </tr>
            <tr
                v-for="subscription in subscriptions.data"
                class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
                @click="openSubscriptionDetailModal(subscription)"
            >
                <td class="px-3 py-2.5 inline-flex items-center gap-2">
                    <img :src="subscription.user.profile_photo_url ? subscription.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                    {{ subscription.user.name }}
                </td>
                <td class="px-3 py-2.5">
                    {{ formatDateTime(subscription.created_at, false) }}
                </td>
                <td class="px-3 py-2.5">
                    {{ subscription.subscription_id }}
                </td>
                <td class="px-3 py-2.5">
                    $ {{ formatAmount(subscription.amount) }}
                </td>
                <td class="px-3 py-2.5 uppercase">
                    {{ formatType(subscription.status) }}
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex justify-center mt-4" v-if="!isLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="subscriptions"
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

    <Modal :show="subscriptionDetailModal" title="Investment Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="text-black dark:text-white py-2">{{ subscriptionDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="text-black dark:text-white py-2">{{ subscriptionDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">ID Number</span>
            <span class="text-black dark:text-white py-2">{{ subscriptionDetail.subscription_id }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Start Date</span>
            <span class="text-black dark:text-white py-2">{{ formatDateTime(subscriptionDetail.created_at, false) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Valid Thru</span>
            <span class="text-black dark:text-white py-2">{{ formatDateTime(subscriptionDetail.expired_date, false) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Investment Plan</span>
            <span class="text-black dark:text-white py-2">{{ subscriptionDetail.investment_plan.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Amount</span>
            <span class="text-black dark:text-white py-2">$ {{ subscriptionDetail.amount }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Investment Status</span>
            <span class="text-black dark:text-white py-2">{{ formatType(subscriptionDetail.status) }}</span>
        </div>
    </Modal>

</template>
