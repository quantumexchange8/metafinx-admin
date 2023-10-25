<script setup>
import Loading from "@/Components/Loading.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import {ref, watch, watchEffect} from "vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import Action from "@/Pages/Member/Partials/Action.vue";
import {Rank1Icon, Rank2Icon, Rank3Icon} from "@/Components/Icons/outline.jsx";
import KycAction from "@/Pages/Member/Partials/KycAction.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    search: String,
    date: String,
    rank: String,
    refresh: Boolean,
    isLoading: Boolean,
    kycStatus: String,
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
    [() => props.search, () => props.rank, () => props.date],
    debounce(([searchValue, rankValue, dateValue]) => {
        getResults(1, searchValue, rankValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', rank = '', date = '', type = props.kycStatus) => {
    isLoading.value = true
    try {
        let url = `/member/getMemberDetails?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (type) {
            url += `&type=${type}`;
        }

        if (rank) {
            url += `&rank=${rank}`;
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

        getResults(currentPage.value, props.search, props.rank, props.date, props.kycStatus);
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

watchEffect(() => {
    if (usePage().props.title !== null) {
        getResults();
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
                <th scope="col" class="px-3 py-2.5 text-right w-56">
                    Joining Date
                </th>
                <th scope="col" class="px-3 py-2.5 text-right w-56">
                    Wallet Balance
                </th>
                <th scope="col" class="px-3 py-2.5 text-right w-56">
                    Active Investment
                </th>
                <th scope="col" class="px-3 py-2.5 text-center w-24">
                    Rank
                </th>
                <th v-if="kycStatus !== 'pending'" scope="col" class="px-3 py-2.5 text-center w-24">
                    Status
                </th>
                <th scope="col" class="px-3 py-2.5 text-center w-36">
                    Action
                </th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="member in members.data"
                class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600"
            >
                <td class="pl-3 py-2.5">
                    <div class="inline-flex items-center gap-2">
                        <img :src="member.profile_photo_url ? member.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                        <div class="flex flex-col">
                            <div>
                                {{ member.name }}
                            </div>
                            <div class="dark:text-gray-400">
                                {{ member.email }}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2.5 text-right">
                    {{ formatDateTime(member.created_at, false) }}
                </td>
                <td class="px-3 py-2.5 text-right">
                    $ {{ formatAmount(member.wallets_sum_balance) }}
                </td>
                <td class="px-3 py-2.5 text-right">
                    $ {{ formatAmount(member.active_investment_amount) }}
                </td>
                <td class="px-3 py-2.5 text-center uppercase">
                    <span v-if="member.rank.id === 1">{{ member.rank.name }}</span>
                    <Rank1Icon class="h-5" v-if="member.rank.id === 2" />
                    <Rank2Icon class="h-5" v-if="member.rank.id === 3" />
                    <Rank3Icon class="h-5" v-if="member.rank.id === 4" />
                </td>
                <td v-if="kycStatus !== 'pending'" class="px-3 py-2.5 text-center">
                    <span v-if="member.kyc_approval === 'verified'" class="flex w-2 h-2 bg-green-500 dark:bg-success-500 mx-auto rounded-full"></span>
                    <span v-else-if="member.kyc_approval === 'unverified'" class="flex w-2 h-2 bg-red-500 dark:bg-warning-500 mx-auto rounded-full"></span>
                </td>
                <td class="px-3 py-2.5 text-center">
                    <Action
                        v-if="kycStatus !== 'pending'"
                        :members="member"
                        type="member"
                    />
                    <KycAction
                        v-else
                        :member="member"
                    />
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
        <div v-if="members.data.length === 0 && !isLoading" class="flex flex-col justify-center items-center gap-2">
            <img src="/assets/no_data.png" class="md:w-1/4" alt="no data">
            <div class="font-semibold dark:text-gray-400">
                No Member
            </div>
        </div>
    </div>

</template>
