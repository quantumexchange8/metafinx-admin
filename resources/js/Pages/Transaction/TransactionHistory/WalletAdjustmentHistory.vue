<script setup>
import debounce from "lodash/debounce.js";
import {ref, watch} from "vue";
import {ArrowLeftIcon, ArrowRightIcon} from "@heroicons/vue/outline";
import {InternalWalletIcon, InternalMUSDWalletIcon, InternalXLCIcon} from "@/Components/Icons/outline.jsx";
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

const walletsAndAssets = ref({data: []});
const currentPage = ref(1);
const refreshWalletsAndAssets = ref(props.refresh);
const walletsAndAssetsLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);
const { formatDateTime, formatAmount } = transactionFormat();
const walletsAndAssetsHistoryModal = ref(false);
const walletsAndAssetsDetail = ref();

watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    walletsAndAssetsLoading.value = true;
    try {
        let url = `/transaction/getBalanceHistory/WalletAdjustment?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }
        
        const response = await axios.get(url);
        walletsAndAssets.value = response.data.WalletAdjustment;
    } catch (error) {
        console.error(error);
    } finally {
        walletsAndAssetsLoading.value = false
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
    refreshWalletsAndAssets.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

watch(() => props.exportStatus, (newVal) => {
    refreshWalletsAndAssets.value = newVal;
    if (newVal) {
        let url = `/transaction/getBalanceHistory/WalletAdjustment?exportStatus=yes`;

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

const openWalletsAndAssetsHistoryModal = (wallet) => {
    walletsAndAssetsHistoryModal.value = true
    walletsAndAssetsDetail.value = wallet
}

const closeModal = () => {
    walletsAndAssetsHistoryModal.value = false
}
</script>

<template>
    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="walletsAndAssetsLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <table v-else class="w-[650px] table-fixed md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
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
                    After Adjustment
                </th>
                <th scope="col" class="py-2">
                    Remark
                </th>
                <th scope="col" class="py-2 text-center">
                    Status
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="walletsAndAssets.data.length === 0">
                <th colspan="4" class="py-4 text-lg text-center">
                    No History
                </th>
            </tr>
            <tr
                v-for="walletAndAsset in walletsAndAssets.data"
                class="bg-white dark:bg-transparent text-xs text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
                @click="openWalletsAndAssetsHistoryModal(walletAndAsset)"
            >
                <td class="pl-5 py-2">
                    {{ formatDateTime(walletAndAsset.created_at) }}
                </td>
                <td class="py-2">
                    <div class="inline-flex items-center gap-2">
                        <img :src="walletAndAsset.user.profile_photo_url ? walletAndAsset.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                        <div class="flex flex-col">
                            <div>
                                {{ walletAndAsset.user.name }}
                            </div>
                            <div class="dark:text-gray-400">
                                {{ walletAndAsset.user.email }}
                            </div>
                        </div>                    
                    </div>
                </td>
                <td class="py-2">
                    <div v-if="walletAndAsset.transaction_type === 'WalletAdjustment'" class="inline-flex items-center gap-2">
                        <div v-if="walletAndAsset.from_wallet.type === 'internal_wallet'" class="bg-gradient-to-t from-pink-300 to-pink-600 dark:shadow-pink-500 rounded-full w-4 h-4 shrink-0 grow-0">
                            <InternalWalletIcon class="mt-0.5 ml-0.5"/>
                        </div>
                        <div v-else-if="walletAndAsset.from_wallet.type === 'musd_wallet'" class="bg-gradient-to-t from-warning-300 to-warning-600 dark:shadow-warning-500 rounded-full w-4 h-4 shrink-0 grow-0">
                            <InternalMUSDWalletIcon class="mt-0.5 ml-0.5"/>
                        </div>
                        <span>{{ walletAndAsset.from_wallet.name }}</span>
                    </div>
                    <div v-if="walletAndAsset.transaction_type === 'AssetAdjustment'" class="inline-flex items-center gap-2">
                        <div v-if="walletAndAsset.from_coin.setting_coin_id === 1">
                            <InternalXLCIcon />
                        </div>
                        <!-- <span>{{ walletAndAsset.from_coin.setting_coin_id }}</span> -->
                        <span>MXT</span>
                    </div>
                </td>
                <td class="py-2">
                    <span>{{ walletAndAsset.transaction_number }}</span>
                </td>
                <td class="py-2">
                    <div v-if="walletAndAsset.transaction_type === 'WalletAdjustment'">
                        $ {{ formatAmount(walletAndAsset.new_wallet_amount) }}
                    </div>
                    <div v-if="walletAndAsset.transaction_type === 'AssetAdjustment'">
                        {{ formatAmount(walletAndAsset.new_coin_amount) }} XLC Unit
                    </div>
                </td>
                <td class="py-2">
                    {{ walletAndAsset.remarks }}
                </td>
                <td v-if="walletAndAsset.status == 'Success'" class="py-2 text-center">
                    <span v-if="walletAndAsset.status === 'Success'" class="flex w-2 h-2 bg-green-500 dark:bg-green-500 mx-auto rounded-full"></span>
                    <span v-else class="flex w-2 h-2 bg-green-500 dark:bg-error-500 mx-auto rounded-full"></span>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex justify-center mt-4" v-if="!walletsAndAssetsLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="walletsAndAssets"
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

    <Modal :show="walletsAndAssetsHistoryModal" title="Wallet Adjustment Details" @close="closeModal">
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ walletsAndAssetsDetail.user.name }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ walletsAndAssetsDetail.user.email }}</span>
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Date & Time</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ formatDateTime(walletsAndAssetsDetail.created_at) }}</span>
        </div>
        <!-- <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Before Adjustment</span>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'WalletAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                <span>$ </span>{{ formatAmount(walletsAndAssetsDetail.new_amount) }}
            </div>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'AssetAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                {{ formatAmount(walletsAndAssetsDetail.old_amount) }}<span> XLC Unit</span>
            </div>
        </div> -->
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Adjust Amount</span>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'WalletAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                <span>$ </span>{{ formatAmount(walletsAndAssetsDetail.transaction_amount) }}
            </div>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'AssetAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                {{ formatAmount(walletsAndAssetsDetail.transaction_amount) }}<span> XLC Unit</span>
            </div>        
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">After Adjustment</span>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'WalletAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                <span>$ </span>{{ formatAmount(walletsAndAssetsDetail.new_wallet_amount) }}
            </div>
            <div v-if="walletsAndAssetsDetail.transaction_type === 'AssetAdjustment'" class="col-span-2 text-black dark:text-white py-2">
                {{ formatAmount(walletsAndAssetsDetail.new_coin_amount) }}<span> XLC Unit</span>
            </div>        
        </div>
        <div class="grid grid-cols-3 items-center gap-2">
            <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Remark</span>
            <span class="col-span-2 text-black dark:text-white py-2">{{ walletsAndAssetsDetail.remarks }}</span>
        </div>
    </Modal>
</template>