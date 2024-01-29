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
    search: String,
    date: String,
    refresh: Boolean,
    isLoading: Boolean,
    exportStatus: Boolean,
    type: String,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const affiliates = ref({data: []});
const totalAmount = ref(0);
const currentPage = ref(1);
const refreshAffiliate = ref(props.refresh);
const isLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);
const { formatDateTime, formatAmount } = transactionFormat();


watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/report/getAffiliateEarningPayoutDetails?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        affiliates.value = response.data.results;
        totalAmount.value = response.data.totalAmount;
        // console.log(affiliates.value);
    } catch (error) {
        console.error(error.response.data);
    } finally {
        isLoading.value = false
        emit('update:loading', false);
    }
}

getResults()

const handlePageChange = (newPage) => {
    if (newPage >= 1) {
        currentPage.value = newPage;

        getResults(currentPage.value, props.search, props.date);
    }
};

watch(() => props.refresh, (newVal) => {
    refreshAffiliate.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

watch(() => props.exportStatus, (newVal) => {
    refreshAffiliate.value = newVal;
    if (newVal) {
        let url = `/report/getAffiliateEarningPayoutDetails?exportStatus=yes`;

        if (props.date) {
            url += `&date=${props.date}`;
        }

        if (props.type) {
            url += `&type=${props.type}`;
        }

        if (props.search) {
            url += `&search=${props.search}`;
        }

        window.location.href = url;
        emit('update:export', false);
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400 dark:enabled:hover:text-white'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

</script>

<template>

    <div v-if="isLoading" class="w-full flex justify-center my-8">
        <Loading />
    </div>
    <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5 table-fixed">
        <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">            <tr>
            <th scope="col" class="px-3 py-2.5">
                Name
            </th>
            <th scope="col" class="px-3 py-2.5">
                Referee
            </th>
            <th scope="col" class="px-3 py-2.5">
                Date
            </th>
            <th scope="col" class="px-3 py-2.5">
                Amount
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="affiliates.data.length === 0">
            <th colspan="5" class="py-4 text-lg text-center">
                No History
            </th>
        </tr>
        
        <tr
            v-for="affiliate in affiliates.data"
            class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
        >
            <td class="px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <img :src="affiliate.user?.profile_photo_url || 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                    <div class="flex flex-col">
                        <div>
                            {{ affiliate.user ? affiliate.user.name : 'N/A' }}
                        </div>
                        <div class="dark:text-gray-400">
                            {{ affiliate.user ? affiliate.user.email : 'N/A' }}
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-3 py-2.5">
                <div class="flex items-center gap-2">
                    <img :src="affiliate.downline?.profile_photo_url || 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                    <div class="flex flex-col">
                        <div>
                            {{ affiliate.downline.name }}
                        </div>
                        <div class="dark:text-gray-400">
                            {{ affiliate.downline.email }}
                        </div>
                    </div>
                </div>
            </td>

            <td class="px-3 py-2.5">
                {{ formatDateTime(affiliate.created_at) }}
            </td>
            <td class="px-3 py-2.5">
                $&nbsp;{{ formatAmount(affiliate.after_amount) }}
            </td>
        </tr>
        </tbody>
    </table>
    <div class="flex justify-center mt-4" v-if="!isLoading">
        <TailwindPagination
            :item-classes=paginationClass
            :active-classes=paginationActiveClass
            :data="affiliates"
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
    <div class="flex items-center">
        <div class="text-xl font-semibold">
            Total Amount: ${{ formatAmount(totalAmount) }}
        </div>
    </div>
    
</template>
