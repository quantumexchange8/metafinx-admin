<script setup>
import debounce from "lodash/debounce.js";
import {ref, watch} from "vue";
import {ArrowLeftIcon, ArrowRightIcon} from "@heroicons/vue/outline";
import {InternalWalletIcon} from "@/Components/Icons/outline.jsx";
import Loading from "@/Components/Loading.vue";
import {transactionFormat} from "@/Composables/index.js";
import {TailwindPagination} from "laravel-vue-pagination";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    search: String,
    date: String,
    refresh: Boolean,
    isLoading: Boolean,
})

const withdrawals = ref({data: []});
const currentPage = ref(1);
const refreshDeposit = ref(props.refresh);
const depositLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh']);
const { formatDateTime } = transactionFormat();
const withdrawalHistoryModal = ref(false);
const withdrawalDetail = ref();

watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    depositLoading.value = true
    try {
        let url = `/transaction/getTransactionHistory/Withdrawal?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        withdrawals.value = response.data.Withdrawal;
    } catch (error) {
        console.error(error);
    } finally {
        depositLoading.value = false
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
    refreshDeposit.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400 dark:enabled:hover:text-white'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

const openWithdrawalHistoryModal = (withdrawal) => {
    withdrawalHistoryModal.value = true
    withdrawalDetail.value = withdrawal
}

const closeModal = () => {
    withdrawalHistoryModal.value = false
}
</script>

<template>
    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="depositLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
            <tr>
                <th scope="col" class="pl-5 py-2">
                    Name
                </th>
                <th scope="col" class="py-2">
                    Asset
                </th>
                <th scope="col" class="py-2">
                    Date
                </th>
                <th scope="col" class="py-2">
                    Transaction ID
                </th>
                <th scope="col" class="py-2">
                    Amount
                </th>
                <th scope="col" class="py-2 text-center">
                    Status
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="withdrawals.data.length === 0">
                <th colspan="5" class="py-4 text-lg text-center">
                    No History
                </th>
            </tr>
            <tr
                v-for="withdrawal in withdrawals.data"
                class="bg-white dark:bg-transparent text-xs text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
                @click="openWithdrawalHistoryModal(withdrawal)"
            >
                <td class="pl-5 py-2">
                    <div class="inline-flex items-center gap-2">
                        <img :src="withdrawal.user.profile_photo_url ? withdrawal.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                        {{ withdrawal.user.name }}
                    </div>
                </td>
                <td class="py-2">
                    <div class="inline-flex items-center gap-2">
                        <div class="bg-gradient-to-t from-pink-300 to-pink-600 dark:shadow-pink-500 rounded-full w-4 h-4 shrink-0 grow-0">
                            <InternalWalletIcon class="mt-0.5 ml-0.5"/>
                        </div>
                        {{ withdrawal.wallet.name }}
                    </div>
                </td>
                <td class="py-2">
                    {{ formatDateTime(withdrawal.created_at) }}
                </td>
                <td class="py-2">
                    {{ withdrawal.transaction_id }}
                </td>
                <td class="py-2">
                    $ {{ withdrawal.amount }}
                </td>
                <td class="py-2 text-center">
                    <span v-if="withdrawal.status === 'Success'" class="flex w-2 h-2 bg-green-500 dark:bg-success-500 mx-auto rounded-full"></span>
                    <span v-else-if="withdrawal.status === 'Pending'" class="flex w-2 h-2 bg-red-500 dark:bg-warning-500 mx-auto rounded-full"></span>
                    <span v-else-if="withdrawal.status === 'Processing'" class="flex w-2 h-2 bg-red-500 dark:bg-[#007AFF] mx-auto rounded-full"></span>
                    <span v-else-if="withdrawal.status === 'Rejected'" class="flex w-2 h-2 bg-red-500 dark:bg-error-500 mx-auto rounded-full"></span>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex justify-center mt-4" v-if="!depositLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="withdrawals"
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

    <Modal :show="withdrawalHistoryModal" title="Transaction Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction Type</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.type }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction ID</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.transaction_id }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Date & Time</span>
            <span class="text-black dark:text-white py-2">{{ formatDateTime(withdrawalDetail.created_at) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">To</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.to_wallet_address }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Amount</span>
            <span class="text-black dark:text-white py-2">$ {{ withdrawalDetail.amount }}</span>
        </div>
        <div class="grid grid-cols-3 items-center">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction Status</span>
            <span class="text-black dark:text-white py-2">{{ withdrawalDetail.status }}</span>
        </div>
    </Modal>
</template>
