<script setup>
import Loading from "@/Components/Loading.vue";
import {TailwindPagination} from "laravel-vue-pagination";
// import DepositTableBody from "@/Pages/Wallet/Transaction/DepositTableBody.vue";
import {ref, watch} from "vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    search: String,
    date: String,
    refresh: Boolean,
    isLoading: Boolean,
    settingRankId: Number,
})
const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const members = ref({data: []});
const currentPage = ref(1);
const refreshDeposit = ref(props.refresh);
const isLoading = ref(props.isLoading);
const emit = defineEmits(['update:loading', 'update:refresh']);
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
        let url = `/member/getMemberDetails/${props.settingRankId}?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        members.value = response.data;
    } catch (error) {
        console.error(error);
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
    refreshDeposit.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];
</script>

<template>
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
                <th scope="col" class="px-3 py-2.5 text-right">
                    Joining Date
                </th>
                <th scope="col" class="px-3 py-2.5 text-right">
                    Wallet Balance
                </th>
                <th scope="col" class="px-3 py-2.5 text-right">
                    Referral Members
                </th>
                <th scope="col" class="px-3 py-2.5 text-center">
                    Status
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="members.data.length === 0">
                <th colspan="5" class="py-4 text-lg text-center">
                    No Member
                </th>
            </tr>
            <tr
                v-for="member in members.data"
                class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 hover:cursor-pointer dark:hover:bg-gray-600"
            >
                <td class="pl-5 py-2.5 text-right inline-flex items-center gap-2">
                    {{ member.name }}
                </td>
                <td class="px-3 py-2.5 text-right">
                    {{ formatDateTime(member.created_at, false) }}
                </td>
                <td class="px-3 py-2.5 text-right">
                    $ {{ formatAmount(member.wallets_sum_balance) }}
                </td>
                <td class="px-3 py-2.5 text-right">
                    {{ member.total_children }}
                </td>
                <td class="px-3 py-2.5 text-center">
                    <span v-if="member.kyc_approval === 'approved'" class="flex w-2 h-2 bg-green-500 dark:bg-success-500 mx-auto rounded-full"></span>
                    <span v-else-if="member.kyc_approval === 'pending'" class="flex w-2 h-2 bg-red-500 dark:bg-warning-500 mx-auto rounded-full"></span>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex justify-center mt-4" v-if="!isLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="members"
                :limit=2
                @pagination-change-page="handlePageChange"
            />
        </div>
    </div>

</template>
