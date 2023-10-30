<script setup>
import {ref} from "vue";
import {RefreshIcon} from "@heroicons/vue/outline";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    walletSum: Number,
    member_details: Object,
    self_deposit: Number,
    valid_affiliate_deposit: Number,
    referralCount: Number,
})

const isLoading = ref(false);
const refresh = ref(false);

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const { formatDateTime, formatAmount } = transactionFormat();

</script>

<template>
    <div class="flex flex-col gap-5 w-full mb-14">
        <div class="flex justify-between pb-3 border-b border-gray-700">
            <h3 class="text-base font-semibold">Account Information</h3>
            <RefreshIcon
            :class="{ 'animate-spin': isLoading }"
            class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white ml-auto self-center"
            aria-hidden="true"
            @click="refreshTable"
            />
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Joining Date</p>
            <p class="text-sm dark:text-white w-full">{{ formatDateTime(member_details.created_at, false) }}</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Wallet Balance</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(walletSum) }}</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Account Earning</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Referral Member</p>
            <p class="text-sm dark:text-white w-full">{{ referralCount }}</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Self Deposit</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(self_deposit) }}</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Affiliate Deposit</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(valid_affiliate_deposit) }}</p>
        </div>
    </div>
</template>
