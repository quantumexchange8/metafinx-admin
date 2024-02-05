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
    exportStatus: Boolean,
})

const transfers = ref({data: []});
const currentPage = ref(1);
const refreshTransfer = ref(props.refresh);
const transferLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);
const { formatDateTime, formatAmount } = transactionFormat();
const transferHistoryModal = ref(false);
const transferDetail = ref();

watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    transferLoading.value = true
    try {
        let url = `/transaction/getTransactionHistory/InternalTransfer?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }
        
        const response = await axios.get(url);
        transfers.value = response.data.InternalTransfer;
    } catch (error) {
        console.error(error);
    } finally {
        transferLoading.value = false
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
    refreshTransfer.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

watch(() => props.exportStatus, (newVal) => {
    refreshTransfer.value = newVal;
    if (newVal) {
        let url = `/transaction/getBalanceHistory/InternalTransfer?exportStatus=yes`;

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
    'bg-transparent border-0 dark:text-gray-400 dark:enabled:hover:text-white'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

const openTransferHistoryModal = (transfer) => {
    transferHistoryModal.value = true
    transferDetail.value = transfer
}

const closeModal = () => {
    transferHistoryModal.value = false
}
</script>

<template>
    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="transferLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <div v-else class="overflow-x-auto">
            <table class="w-[800px] table-fixed md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
                <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                <tr>
                    <th scope="col" class="pl-5 py-2">
                        Date
                    </th>
                    <th scope="col" class="py-2">
                        Name
                    </th>
                    <th scope="col" class="py-2">
                        Transfer From
                    </th>
                    <th scope="col" class="py-2">
                        Transfer To
                    </th>
                    <th scope="col" class="py-2">
                        Transfer Amount
                    </th>
                    <!-- <th scope="col" class="py-2">
                        Balance
                    </th> -->
                    <!-- <th scope="col" class="py-2">
                        Remark
                    </th> -->
                    <th scope="col" class="py-2 text-center">
                        Status
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="transfers.data.length === 0">
                    <th colspan="6" class="py-4 text-lg text-center">
                        No History
                    </th>
                </tr>
                <tr
                    v-for="transfer in transfers.data"
                    class="bg-white dark:bg-transparent text-xs text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
                    @click="openTransferHistoryModal(transfer)"
                >
                    <td class="pl-5 py-2">
                        {{ formatDateTime(transfer.created_at) }}
                    </td>
                    <td class="py-2">
                        <div class="inline-flex items-center gap-2">
                            <img :src="transfer.user.profile_photo_url ? transfer.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                            <div class="flex flex-col">
                                <div>
                                    {{ transfer.user.name }}
                                </div>
                                <div class="dark:text-gray-400">
                                    {{ transfer.user.email }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-2">
                        {{ transfer.from_wallet.name }}
                    </td>
                    <td class="py-2">
                        {{ transfer.to_wallet.name }}
                    </td>
                    <td class="py-2">
                    $ {{ formatAmount(transfer.transaction_amount) }}
                    </td>
                    <!-- <td class="py-2">
                        {{ transfer.remarks }}
                    </td> -->
                    <td v-if="transfer.status == 'Success'" class="py-2 text-center">
                        <span v-if="transfer.status === 'Success'" class="flex w-2 h-2 bg-green-500 dark:bg-green-500 mx-auto rounded-full"></span>
                        <span v-else class="flex w-2 h-2 bg-green-500 dark:bg-error-500 mx-auto rounded-full"></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="flex justify-center mt-4" v-if="!transferLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="transfers"
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
    </div>

    <Modal :show="transferHistoryModal" title="Internal Transfer Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Date & Time</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ formatDateTime(transferDetail.created_at) }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transaction ID</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.transaction_number }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transfer From</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.from_wallet.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transfer To</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.to_wallet.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Transfer Amount</span>
            <span class="col-span-2 text-black dark:text-white py-2">$ {{ formatAmount(transferDetail.amount) }}</span>
        </div>
        <!-- <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Current Balance</span>
            <span class="col-span-2 text-black dark:text-white py-2">$ {{ formatAmount(transferDetail.new_balance) }}</span>
        </div> -->
        <!-- <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Remark</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ transferDetail.remarks }}</span>
        </div> -->
    </Modal>
</template>
