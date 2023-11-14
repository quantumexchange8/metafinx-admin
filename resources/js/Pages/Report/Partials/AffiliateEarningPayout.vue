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
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
// const referrals = ref({data: []});
// const currentPage = ref(1);
// const refreshReferral = ref(props.refresh);
const isLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);
// const { formatDateTime, formatAmount } = transactionFormat();


// watch(
//     [() => props.search, () => props.date],
//     debounce(([searchValue, dateValue]) => {
//         getResults(1, searchValue, dateValue);
//     }, 300)
// );

// const getResults = async (page = 1, search = '', date = '') => {
//     isLoading.value = true
//     try {
//         let url = `/report/getReferralDetails?page=${page}`;

//         if (search) {
//             url += `&search=${search}`;
//         }

//         if (date) {
//             url += `&date=${date}`;
//         }

//         const response = await axios.get(url);
//         referrals.value = response.data;
//     } catch (error) {
//         console.error(error.response.data);
//     } finally {
//         isLoading.value = false
//         emit('update:loading', false);
//     }
// }

// getResults()

// const handlePageChange = (newPage) => {
//     if (newPage >= 1) {
//         currentPage.value = newPage;

//         getResults(currentPage.value, props.search, props.date);
//     }
// };

// watch(() => props.refresh, (newVal) => {
//     refreshReferral.value = newVal;
//     if (newVal) {
//         // Call the getResults function when refresh is true
//         getResults();
//         emit('update:refresh', false);
//     }
// });

// watch(() => props.exportStatus, (newVal) => {
//     refreshReferral.value = newVal;
//     if (newVal) {
//         let url = `/report/getPayoutDetails?exportStatus=yes`;

//         if (props.date) {
//             url += `&date=${props.date}`;
//         }

//         if (props.type) {
//             url += `&type=${props.type}`;
//         }

//         if (props.search) {
//             url += `&search=${props.search}`;
//         }

//         window.location.href = url;
//         emit('update:export', false);
//     }
// });

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
                Date
            </th>
            <th scope="col" class="px-3 py-2.5">
                Amount
            </th>
        </tr>
        </thead>
        <tbody>
        <!-- <tr v-if="referrals.data.length === 0">
            <th colspan="5" class="py-4 text-lg text-center">
                No History
            </th>
        </tr> -->
        <!-- v-for="referral in referrals.data" -->
        <tr
            
            class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
        >
            <td class="px-3 py-2.5 inline-flex items-center gap-2">
                <!-- <img :src="referral.user.upline?.profile_photo_url || 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                {{ referral.user.upline ? referral.user.upline.name : 'N/A' }} -->
            </td>   
            <td class="px-3 py-2.5">
                <!-- {{ formatDateTime(referral.user.created_at) }} -->
            </td>
            <td class="px-3 py-2.5">
                <!-- ${{ formatAmount(referral.total_earnings) }} -->
            </td>
        </tr>
        </tbody>
    </table>
    <!-- <div class="flex justify-center mt-4" v-if="!isLoading">
        <TailwindPagination
            :item-classes=paginationClass
            :active-classes=paginationActiveClass
            :data="referrals"
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
    </div> -->
    
</template>
