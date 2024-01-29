<script setup>
import {onMounted, ref} from "vue";
import {RefreshIcon} from "@heroicons/vue/outline";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    member_details: Object
})

const isLoading = ref(false);
const refresh = ref(false);
const responseData = ref(null);
const totalMonthlyReturn = ref(null);
const totalReferralEarning = ref(null);
const totalAffiliateEarning = ref(null);
const totalDividendEarning = ref(null);

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

        totalMonthlyReturn.value = responseData.value.totalEarnings.MonthlyReturn;
        totalReferralEarning.value = responseData.value.totalEarnings.ReferralEarning;
        totalAffiliateEarning.value = responseData.value.totalEarnings.AffiliateEarning;
        totalDividendEarning.value = responseData.value.totalEarnings.DividendEarning;
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
            <h3 class="text-base font-semibold">Earning Information</h3>
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
            <span class="sr-only">Loading...</span>
        </div>

        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Monthly Return</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalMonthlyReturn) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Referral Earning</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalReferralEarning) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Affiliate Earning</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalAffiliateEarning) }}</p>
        </div>
        <div v-if="!isLoading" class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Dividend Earning</p>
            <p class="text-sm dark:text-white w-full">$ {{ formatAmount(totalDividendEarning) }}</p>
        </div>
    </div>
</template>
