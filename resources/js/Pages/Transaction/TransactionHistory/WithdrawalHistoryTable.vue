<script setup>
import debounce from "lodash/debounce.js";
import {ref, watch} from "vue";
import {ArrowLeftIcon, ArrowRightIcon} from "@heroicons/vue/outline";
import {InternalWalletIcon, InternalMUSDWalletIcon} from "@/Components/Icons/outline.jsx";
import Loading from "@/Components/Loading.vue";
import {transactionFormat} from "@/Composables/index.js";
import {TailwindPagination} from "laravel-vue-pagination";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    search: String,
    date: String,
    filter: String,
    refresh: Boolean,
    isLoading: Boolean,
    exportStatus: Boolean,
})

const { formatAmount } = transactionFormat();
const withdrawals = ref({data: []});
const totalAmount = ref(0);
const currentPage = ref(1);
const refreshDeposit = ref(props.refresh);
const depositLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);
const { formatDateTime } = transactionFormat();
const withdrawalHistoryModal = ref(false);
const withdrawalDetail = ref();

watch(
    [() => props.search, () => props.date, () => props.filter],
    debounce(([searchValue, dateValue, filterValue]) => {
        getResults(1, searchValue, dateValue, filterValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '', filter = '') => {
    depositLoading.value = true
    try {
        let url = `/transaction/getTransactionHistory/Withdrawal?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        if (filter) {
            url += `&filter=${filter}`;
        }

        const response = await axios.get(url);
        withdrawals.value = response.data.Withdrawal;
        totalAmount.value = response.data.totalAmount;
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

watch(() => props.exportStatus, (newVal) => {
    refreshDeposit.value = newVal;
    if (newVal) {
        let url = `/transaction/getTransactionHistory/Withdrawal?exportStatus=yes`;

        if (props.date) {
            url += `&date=${props.date}`;
        }

        if (props.search) {
            url += `&search=${props.search}`;
        }

        if (props.filter) {
            url += `&search=${props.filter}`;
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
        <div v-else class="overflow-x-auto">
            <table class="w-[800px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
                <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                <tr>
                    <th scope="col" class="pl-5 py-2">
                        Date
                    </th>
                    <th scope="col" class="py-2">
                        Name
                    </th>
                    <th scope="col" class="py-2">
                        Type
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
                            {{ formatDateTime(withdrawal.created_at) }}
                        </div>
                    </td>
                    <td class="py-2">
                        <div class="inline-flex items-center gap-2">
                            <img :src="withdrawal.user.profile_photo_url ? withdrawal.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                            <div class="flex flex-col">
                                <div>
                                    {{ withdrawal.user.name }}
                                </div>
                                <div class="dark:text-gray-400">
                                    {{ withdrawal.user.email }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-2">
                        <div class="inline-flex items-center gap-2">
                            <div v-if="withdrawal.from_wallet.type === 'internal_wallet'" class="bg-gradient-to-t from-pink-300 to-pink-600 dark:shadow-pink-500 rounded-full w-4 h-4 shrink-0 grow-0">
                                <InternalWalletIcon class="mt-0.5 ml-0.5"/>
                            </div>
                            <div v-else-if="withdrawal.from_wallet.type === 'musd_wallet'" class="bg-gradient-to-t from-warning-300 to-warning-600 dark:shadow-warning-500 rounded-full w-4 h-4 shrink-0 grow-0">
                                <InternalMUSDWalletIcon class="mt-0.5 ml-0.5"/>
                            </div>
                            {{ withdrawal.from_wallet.name }}
                        </div>
                    </td>
                    <td class="py-2">
                        {{ withdrawal.transaction_number }}
                    </td>
                    <td class="py-2">
                        $ {{ withdrawal.transaction_amount }}
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
        </div>
        <div class="flex justify-center mt-4" v-if="!depositLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="withdrawals"
                :limit=2
                @pagination-change-page="handlePageChange"
            >
                <template #prev-nav>
                    <span class="flex gap-2"><ArrowLeftIcon class="w-5 h-5" /> <span class="hidden sm:flex">Previous</span></span>
                </template>
                <template #next-nav>
                    <span class="flex gap-2"><span class="hidden sm:flex">Next</span><ArrowRightIcon class="w-5 h-5" /></span>
                </template>
            </TailwindPagination>
        </div>
        <div class="text-xl font-semibold">
            Total Amount: ${{ formatAmount(totalAmount) }}
        </div>
    </div>

    <Modal :show="withdrawalHistoryModal" title="Transaction Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ withdrawalDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ withdrawalDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction Type</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ withdrawalDetail.transaction_type }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction ID</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ withdrawalDetail.transaction_number }}</span>
        </div>
        <div class="grid grid-cols-3 items-cente gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Date & Time</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ formatDateTime(withdrawalDetail.created_at) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">To</span>
            <span class="col-span-2 text-black dark:text-white py-2 break-words">{{ withdrawalDetail.to_wallet_address }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Amount</span>
            <span class="col-span-2 text-black dark:text-white py-2">$ {{ withdrawalDetail.transaction_amount }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction Status</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ withdrawalDetail.status }}</span>
        </div>
    </Modal>
</template>
