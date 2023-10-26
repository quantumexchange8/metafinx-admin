<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import {onMounted, ref} from "vue";
import {transactionFormat} from "@/Composables/index.js";
import MemberChart from "@/Pages/Dashboard/MemberChart.vue";
import PendingKYC from "@/Pages/Dashboard/PendingKYC.vue";
import PendingTransaction from "@/Pages/Dashboard/PendingTransaction.vue";

const props = defineProps({
    newMemberCount: Number,
    totalDeposit: Number,
    totalWithdrawal: Number,
    totalInvestment: Number,
    totalMembers: Number,
    pendingMembers: Object,
    pendingMemberCount: Number,
    pendingDeposits: Object,
    pendingWithdrawals: Object,
    pendingTransactions: Object,
    pendingTransactionCount: Number
})

const currentMonth = ref('');
const { formatAmount } = transactionFormat();

onMounted(() => {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const currentDate = new Date();
    const currentMonthIndex = currentDate.getMonth();
    currentMonth.value = months[currentMonthIndex];
});
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
            <div class="px-5 py-3 flex flex-col overflow-hidden bg-white rounded-xl shadow-md dark:bg-gray-700">
                <div class="text-xs dark:text-gray-400">
                    New Member Joined ({{ currentMonth }})
                </div>
                <div class="text-xl font-semibold">
                    {{ props.newMemberCount }}
                </div>
            </div>
            <div class="px-5 py-3 flex flex-col overflow-hidden bg-white rounded-xl shadow-md dark:bg-gray-700">
                <div class="text-xs dark:text-gray-400">
                   Total Deposit
                </div>
                <div class="text-xl font-semibold">
                    $ {{ formatAmount(props.totalDeposit) }}
                </div>
            </div>
            <div class="px-5 py-3 flex flex-col overflow-hidden bg-white rounded-xl shadow-md dark:bg-gray-700">
                <div class="text-xs dark:text-gray-400">
                    Total Withdrawal
                </div>
                <div class="text-xl font-semibold">
                    $ {{ formatAmount(props.totalWithdrawal) }}
                </div>
            </div>
            <div class="px-5 py-3 flex flex-col overflow-hidden bg-white rounded-xl shadow-md dark:bg-gray-700">
                <div class="text-xs dark:text-gray-400">
                    Total Investment
                </div>
                <div class="text-xl font-semibold">
                    $ {{ formatAmount(props.totalInvestment) }}
                </div>
            </div>
        </div>

        <div class="p-5 rounded-[10px] dark:bg-gray-700 my-8">
            <div class="flex justify-between">
                <div class="grid">
                    <span class="text-xl font-semibold dark:text-white">Total Members</span>
                    <span class="text-xs font-normal dark:text-gray-400">Yearly Total Members Data</span>
                </div>
                <div>
                    <span class="text-[32px] font-semibold dark:text-white">{{ props.totalMembers }}</span> <span class="text-xl dark:text-gray-400">members</span>
                </div>
            </div>
            <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-600">

            <MemberChart />

        </div>

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
