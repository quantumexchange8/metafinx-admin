<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { ref, onMounted } from "vue";
import {transactionFormat} from "@/Composables/index.js";
import MemberChart from "@/Pages/Dashboard/TotalMember/MemberChart.vue";
import PendingKYC from "@/Pages/Dashboard/PendingKYC.vue";
import PendingTransaction from "@/Pages/Dashboard/PendingTransaction.vue";
import TotalTransaction from "@/Pages/Dashboard/TotalTransaction/TotalTransaction.vue";
import TotalInvestment from "@/Pages/Dashboard/TotalInvestment/TotalInvestment.vue";
import TotalWalletBalance from "@/Pages/Dashboard/TotalWalletBalance/TotalWalletBalance.vue";
import { usePermission } from "@/Composables/permissions";

const props = defineProps({
    totalDeposit: Number,
    totalWithdrawal: Number,
    totalTransaction: Number,
    totalWalletBalance: Number,
    totalInvestment: Number,
    totalMembers: Number,
    pendingMembers: Object,
    pendingMemberCount: Number,
    pendingDeposits: Object,
    pendingWithdrawals: Object,
    pendingTransactions: Object,
    pendingTransactionCount: Number,
    currentTotalInvestment: String,
});

const { hasRole, hasPermission } = usePermission();
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
        key: 'totalTransactionStats',
        label: 'Total Transaction',
        value: '$ ' + formatAmount(props.totalTransaction),
    },
    {
        key: 'totalWalletBalanceStats',
        label: 'Total Wallet Balance',
        value: '$ ' + formatAmount(props.totalWalletBalance),
    },
    // {
    //     key: 'totalDepositStats',
    //     label: 'Total Deposit',
    //     value: '$ ' + formatAmount(props.totalDeposit),
    // },
    // {
    //     key: 'totalWithdrawalStats',
    //     label: 'Total Withdrawal',
    //     value: '$ ' + formatAmount(props.totalWithdrawal),
    // },
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

const navigateToFirstAvailablePage = () => {
    if (!hasPermission('ViewDashboard')) {
        if (hasPermission('ViewMemberListing')) {
            window.location.href = route('member.member_listing');
        } else if (hasPermission('ViewSchemeSettingIPO')) {
            window.location.href = route('ipo_scheme.setting');
        } else if (hasPermission('ViewSettingMXT')) {
            window.location.href = route('mxt.setting');
        } else if (hasPermission('ViewTransaction')) {
            window.location.href = route('transaction.listing');
        } else if (hasPermission('ViewReport')) {
            window.location.href = route('report.view');
        } else if (hasPermission('ViewConfiguration')) {
            window.location.href = route('configuration.index');
        } else if (hasPermission('ViewAdminUser')) {
            window.location.href = route('admin_user.admin_listing');
        }
    }
};

onMounted(navigateToFirstAvailablePage);
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header v-if="hasRole('admin') || hasPermission('ViewDashboard')">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl md:text-2xl font-semibold leading-tight">
                    Welcome Back
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-5" v-if="hasRole('admin') || hasPermission('ViewDashboard')">
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
            v-if="activeStats==='totalMemberStats' && (hasRole('admin') || hasPermission('ViewDashboard'))"
            :currentYear="currentYear"
            :totalMembers="totalMembers"
        />
        <TotalTransaction
            v-if="activeStats==='totalTransactionStats' && (hasRole('admin') || hasPermission('ViewDashboard'))"
            :currentYear="currentYear"
        />
        <TotalWalletBalance
            v-if="activeStats==='totalWalletBalanceStats' && (hasRole('admin') || hasPermission('ViewDashboard'))"
            :currentYear="currentYear"
        />
        <!-- <TotalTransaction
            v-if="activeStats==='totalDepositStats' || activeStats==='totalWithdrawalStats'"
            :currentYear="currentYear"
        /> -->
        <TotalInvestment
            v-if="activeStats==='totalInvestmentStats' && (hasRole('admin') || hasPermission('ViewDashboard'))"
            :currentYear="currentYear"
            :currentTotalInvestment="currentTotalInvestment"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 my-8" v-if="hasRole('admin') || hasPermission('ViewDashboard')">
            <PendingKYC
                v-if="hasRole('admin') || hasPermission('ViewMemberListing')"
                :pendingMemberCount="props.pendingMemberCount"
            />
            <PendingTransaction
                v-if="hasRole('admin') || hasPermission('ViewTransaction')"
                :pendingTransactions="props.pendingTransactions"
                :pendingTransactionCount="props.pendingTransactionCount"
            />
        </div>

    </AuthenticatedLayout>
</template>
