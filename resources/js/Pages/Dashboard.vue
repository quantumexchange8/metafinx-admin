<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ref} from "vue";
import {transactionFormat} from "@/Composables/index.js";
import MemberChart from "@/Pages/Dashboard/TotalMember/MemberChart.vue";
import PendingKYC from "@/Pages/Dashboard/PendingKYC.vue";
import PendingTransaction from "@/Pages/Dashboard/PendingTransaction.vue";
import TotalTransaction from "@/Pages/Dashboard/TotalTransaction/TotalTransaction.vue";
import TotalInvestment from "@/Pages/Dashboard/TotalInvestment/TotalInvestment.vue";

const props = defineProps({
    totalDeposit: Number,
    totalWithdrawal: Number,
    totalInvestment: Number,
    totalMembers: Number,
    pendingMembers: Object,
    pendingMemberCount: Number,
    pendingDeposits: Object,
    pendingWithdrawals: Object,
    pendingTransactions: Object,
    pendingTransactionCount: Number,
    currentTotalInvestment: String,
})

const { formatAmount } = transactionFormat();
const currentYear = new Date().getFullYear();
const activeStats = ref('totalMemberStats');

const statistics = [
    {
        key: 'totalMemberStats',
        label: 'Total Members',
        value: props.totalMembers,
    },
    {
        key: 'totalDepositStats',
        label: 'Total Deposit',
        value: '$ ' + formatAmount(props.totalDeposit),
    },
    {
        key: 'totalWithdrawalStats',
        label: 'Total Withdrawal',
        value: '$ ' + formatAmount(props.totalWithdrawal),
    },
    {
        key: 'totalInvestmentStats',
        label: 'Total Investment',
        value: '$ ' + formatAmount(props.totalInvestment),
    },
];

const selectStats = (stats) => {
    activeStats.value = stats;
};

const isActiveStatistic = (key) => {
    return activeStats.value === key
        ? 'border-2 dark:border-white dark:bg-gray-600'
        : 'dark:bg-gray-700';
};
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl md:text-2xl font-semibold leading-tight">
                    Welcome Back
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            <div
                v-for="statistic in statistics"
                :key="statistic.key"
                class="px-5 py-3 flex flex-col overflow-hidden bg-white rounded-xl shadow-md dark:hover:bg-gray-600 cursor-pointer"
                :class="isActiveStatistic(statistic.key)"
                @click="selectStats(statistic.key)"
            >
                <div class="text-xs dark:text-gray-400">
                    {{ statistic.label }}
                </div>
                <div class="text-xl font-semibold">
                    {{ statistic.value }}
                </div>
            </div>
        </div>

        <MemberChart
            v-if="activeStats==='totalMemberStats'"
            :totalMem="currentYear"
            :totalMembers="totalMembers"
        />
        <TotalTransaction
            v-if="activeStats==='totalDepositStats' || activeStats==='totalWithdrawalStats'"
            :currentYear="currentYear"
        />
        <TotalInvestment
            v-if="activeStats==='totalInvestmentStats'"
            :currentYear="currentYear"
            :currentTotalInvestment="currentTotalInvestment"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 my-8">
            <PendingKYC
                :pendingMemberCount="props.pendingMemberCount"
            />
            <PendingTransaction
                :pendingTransactions="props.pendingTransactions"
                :pendingTransactionCount="props.pendingTransactionCount"
            />
        </div>

    </AuthenticatedLayout>
</template>
