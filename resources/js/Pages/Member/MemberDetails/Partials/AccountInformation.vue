<script setup>
import {onMounted, ref} from "vue";
import {RefreshIcon} from "@heroicons/vue/outline";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    member_details: Object,
})

const isLoading = ref(false);
const refresh = ref(false);
const responseData = ref(null);
const totalWalletBalance = ref(null);
const totalAccountEarning = ref(null);
const referralMember = ref(null);
const selfDeposit = ref(null);
const totalAffiliateDeposit = ref(null);

const fetchData = async () => {
    // Replace this with your actual API endpoint and any necessary headers or parameters
    const apiUrl = '/member/getMemberInformation/' + props.member_details.id;

    try {
        const response = await fetch(apiUrl);

        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        throw error; // Propagate the error for better error handling if needed
    }
};

const refreshInfo = async () => {
    isLoading.value = true;
    try {
         // Replace fetchData with your actual function for fetching data
        responseData.value = await fetchData();
        totalWalletBalance.value = responseData.value.walletSum;
        totalAccountEarning.value = responseData.value.earningSum;
        referralMember.value = responseData.value.referralCount;
        selfDeposit.value = responseData.value.self_deposit;
        totalAffiliateDeposit.value = responseData.value.valid_affiliate_deposit;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        isLoading.value = false;
        refresh.value = false;
    }
};

onMounted(() => {
    // Fetch initial data on component mount
    refreshInfo();
});

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
            @click="refreshInfo"
            />
        </div>
        <div v-if="isLoading" role="status" class="w-full rounded animate-pulse dark:divide-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <div class="flex items-center justify-between pt-4">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <div class="flex items-center justify-between pt-4">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <div class="flex items-center justify-between pt-4">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <div class="flex items-center justify-between pt-4">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <div class="flex items-center justify-between pt-4">
                <div>
                    <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-600 w-24 mb-2.5"></div>
                    <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                </div>
                <div class="h-2.5 bg-gray-300 rounded-full dark:bg-gray-700 w-12"></div>
            </div>
            <span class="sr-only">Loading...</span>
        </div>

        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Joining Date</p>
            <p class="text-sm dark:text-white w-full">{{ formatDateTime(member_details.created_at, false) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Wallet Balance</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalWalletBalance) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Account Earning</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalAccountEarning)}}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Referral Member</p>
            <p class="text-sm dark:text-white w-full">{{ referralMember }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Self Deposit</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(selfDeposit) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Affiliate Deposit</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalAffiliateDeposit) }}</p>
        </div>
    </div>
</template>
