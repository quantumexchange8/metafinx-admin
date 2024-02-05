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

const props = defineProps({
    refresh: Boolean,
    isLoading: Boolean,
    search: String,
    date: String,
    exportStatus: Boolean,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const stackings = ref({data: []});
const search = ref('');
const date = ref('');
const historyLoading = ref(props.isLoading);
const historyRefresh = ref(props.refresh);
const currentPage = ref(1);
const { formatDateTime, formatAmount, formatType, formatDate } = transactionFormat();
const stackingDetailModal = ref(false);
const stackingDetail = ref();
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);

watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    historyLoading.value = true
    try {
        let url = `/mxt_setting/getStackingDetails?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        stackings.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        historyLoading.value = false
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

watch(() => props.refresh, (newVal) => {
    historyRefresh.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

watch(() => props.exportStatus, (newVal) => {
    historyLoading.value = newVal;
    if (newVal) {
        let url = `/mxt_setting/getStackingDetails?exportStatus=yes`;

        if (props.date) {
            url += `&date=${props.date}`;
        }

        if (props.search) {
            url += `&search=${props.search}`;
        }
        
        window.location.href = url;
        emit('update:export', false);
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

const openStackingDetailModal = (stacking) => {
    stackingDetailModal.value = true
    stackingDetail.value = stacking
}

const closeModal = () => {
    stackingDetailModal.value = false
}

</script>

<template>
    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="historyLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <div v-else class="overflow-x-auto">
            <table class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
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
                            Plan
                        </th>
                        <th scope="col" class="px-3 py-2.5">
                            Unit
                        </th>
                        <th scope="col" class="px-3 py-2.5">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="stackings.data.length === 0">
                        <th colspan="5" class="py-4 text-lg text-center">
                            No History
                        </th>
                    </tr>
                    <tr
                    v-for="stacking in stackings.data"
                    class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
                    @click="openStackingDetailModal(stacking)"
                    >
                        <td  class="px-3 py-2.5 inline-flex items-center gap-2">
                            <img :src="stacking.user.profile_photo_url ? subscription.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                            <div class="flex flex-col">
                                <div>
                                    {{ stacking.user.name }}
                                </div>
                                <div class="dark:text-gray-400">
                                    {{ stacking.user.email }}
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5">
                            {{ formatDate(stacking.created_at) }}
                        </td>
                        <td class="px-3 py-2.5">
                            {{ stacking.subscription_number }}
                        </td>
                        <td class="px-3 py-2.5">
                            {{ stacking.investment_plan.name.en }}
                        </td>
                        <td class="px-3 py-2.5">
                            {{ formatAmount(stacking.stacking_unit) }}
                        </td>
                        <td class="px-3 py-2.5">
                            <span class="uppercase dark:text-error-500 font-semibold" v-if="stacking.status === 'Terminated'">{{ formatType(stacking.status) }}</span>
                        <span class="uppercase dark:text-blue-500 font-semibold" v-if="stacking.status === 'CoolingPeriod'">{{ formatType(stacking.status) }}</span>
                        <span class="uppercase dark:text-warning-500 font-semibold" v-if="stacking.status === 'OnGoingPeriod'">{{ formatType(stacking.status) }}</span>
                        <span class="uppercase dark:text-success-500 font-semibold" v-if="stacking.status === 'MaturityPeriod'">{{ formatType(stacking.status) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex justify-center mt-4" v-if="!historyLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="stackings"
                :limit=2
                @pagination-change-page="handlePageChange"
            >
                <template #prev-nav>
                    <span class="flex gap-2"><ArrowLeftIcon class="w-5 h-5" /><span class="hidden sm:flex">Previous</span></span>
                </template>
                <template #next-nav>
                    <span class="flex gap-2"><span class="hidden sm:flex">Next</span><ArrowRightIcon class="w-5 h-5" /></span>
                </template>
            </TailwindPagination>
        </div>
    </div>

    <Modal :show="stackingDetailModal" title="Stacking Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="text-black dark:text-white py-2" >{{ stackingDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="text-black dark:text-white py-2">{{ stackingDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">ID Number</span>
            <span class="text-black dark:text-white py-2">{{ stackingDetail.subscription_number }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Start Date</span>
            <span class="text-black dark:text-white py-2">{{ formatDate(stackingDetail.created_at) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Valid Thru</span>
            <span class="text-black dark:text-white py-2">{{ formatDate(stackingDetail.expired_date) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Stacking Plan</span>
            <span class="text-black dark:text-white py-2">{{ stackingDetail.investment_plan.name.en }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Stacking Unit</span>
            <span class="text-black dark:text-white py-2">{{ formatAmount(stackingDetail.stacking_unit) }} unit</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Status</span>
            <span class="uppercase dark:text-error-500 font-semibold" v-if="stackingDetail.status === 'Terminated'">{{ formatType(stackingDetail.status) }}</span>
            <span class="uppercase dark:text-blue-500 font-semibold" v-if="stackingDetail.status === 'CoolingPeriod'">{{ formatType(stackingDetail.status) }}</span>
            <span class="uppercase dark:text-warning-500 font-semibold" v-if="stackingDetail.status === 'OnGoingPeriod'">{{ formatType(stackingDetail.status) }}</span>
            <span class="uppercase dark:text-success-500 font-semibold" v-if="stackingDetail.status === 'MaturityPeriod'">{{ formatType(stackingDetail.status) }}</span>
        </div>
    </Modal>
</template>