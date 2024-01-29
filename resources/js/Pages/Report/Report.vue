<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import PayoutChart from "@/Pages/Report/Charts/PayoutChart.vue";
import ReportTable from "@/Pages/Report/Partials/ReportTable.vue";
import {onMounted, ref, watch, computed} from 'vue'
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import Button from "@/Components/Button.vue";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";

const props = defineProps({
    report: Object,
    totatMonthlyReturn: String,
    totalReferralEarning: String,
    totatAffiliateEarning: String,
})

const { formatAmount, formatDateTime } = transactionFormat();

const isLoading = ref(false);
const search = ref('');
const date = ref('');
const currentYear = new Date().getFullYear();
const activePayout = ref('MonthlyReturn');
const monthlyPayout = ref(0);
const referralEarning = ref(0);
const affiliateEarning = ref(0);
const dividendEarning = ref(0);
const exportStatus = ref(false);

const tableSearch = (searchValue) => {
    search.value = searchValue;
};

const tableDate = (dateValue) => {
    date.value = dateValue;
};

const payoutStats = ref([
    {
        key: 'MonthlyReturn',
        label: 'Total Monthly Return Payout',
        value: '$ ' + formatAmount(props.totatMonthlyReturn),
    },
    // {
    //     key: 'Quarterly_Dividend',
    //     label: 'Total Quarterly Dividend Payout',
    //     value: '$ ' + formatAmount(quarterlyDividend.value),
    // },
    {
        key: 'ReferralEarning',
        label: 'Total Referral Earning Payout',
        value: '$ ' + formatAmount(props.totalReferralEarning),
    },
    {
        key: 'AffiliateEarning',
        label: 'Total Affiliate Earning Payout',
        value: '$ ' + formatAmount(props.totatAffiliateEarning),
    },
    {
        key: 'DividendEarning',
        label: 'Total Dividend Earning Payout',
        value: '$ ' + formatAmount(dividendEarning.value),
    },
    // {
    //     key: 'Pairing Bonus',
    //     label: 'Total Pairing Bonus',
    //     value: '$ ' + formatAmount(dividendEarning.value),
    // },
    // {
    //     key: 'Ticket_Bonus',
    //     label: 'Total Ticket Bonus Payout',
    //     value: '$ ' + formatAmount(ticketBonus.value),
    // },
]);

const totalReferralEarningStat = payoutStats.value.find(stat => stat.key === 'ReferralEarning');

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true

    try {
        let url = `/report/getPayoutDetails?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        // monthlyPayout.value = response.data.monthlyPayout;
        // quarterlyDividend.value = response.data.quarterlyDividend;
        referralEarning.value = response.data.referralEarning;
        // affiliateEarning.value = response.data.affiliateEarning;
        // dividendEarning.value = response.data.dividendEarning;
        // ticketBonus.value = response.data.ticketBonus;
        totalReferralEarningStat.value = '$ ' + formatAmount(referralEarning.value);
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false
    }
}

getResults()

const selectPayout = (payout) => {
    activePayout.value = payout;

};

const isActivePayout = (key) => {
    return activePayout.value === key
        ? 'border-2 dark:border-white dark:bg-gray-600'
        : 'dark:bg-gray-700';
};

const exportReport = () => {
    exportStatus.value = true;
}

watch(
    [() => search.value, () => date.value],
    debounce(([search, date]) => {
        getResults(1, search, date);
    }, 500),
);

const activePayoutLabel = computed(() => {
    const selectedPayout = payoutStats.value.find(payout => payout.key === activePayout.value);
    return selectedPayout ? selectedPayout.label : '';
});
</script>

<template>
    <AuthenticatedLayout title="Report">
        <template #header>
            <div class="md:flex md:flex-row md:justify-between">
                <div class="flex flex-col gap-1 md:w-1/2">
                    <div class="md:flex-row md:items-center md:justify-between">
                        <h2 class="text-3xl font-semibold leading-tight">
                            Report
                        </h2>
                    </div>
                    <p class="text-base font-normal dark:text-gray-400">
                        Track all earnings and payout gained by your members.
                    </p>
                </div>
                <div class="flex justify-end md:w-1/5 md:h-1/2 md:self-end pt-5 md:pt-0">
                    <Button
                        type="button"
                        class="justify-center w-full gap-2 border border-gray-600 text-white text-sm dark:hover:bg-gray-600"
                        variant="transparent"
                        @click="exportReport"
                    >
                        <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />
                        <span>Export as Excel</span>
                    </Button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-5 md:gap-5">
            <div class="grid grid-cols-2 md:grid-cols-1 gap-5 col-span-1">
                <div
                    v-for="payoutStat in payoutStats"
                    :key="payoutStat.key"
                    class="px-5 py-3 rounded-xl flex flex-col justify-center overflow-hidden bg-white shadow-md dark:hover:bg-gray-600 cursor-pointer"
                    :class="isActivePayout(payoutStat.key)"
                    @click="selectPayout(payoutStat.key)"
                >
                    <div class="grid">
                        <div class="text-xs dark:text-gray-400">
                            {{ payoutStat.label }}
                        </div>
                        <div class="text-xl font-semibold">
                            {{ payoutStat.value }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-5 md:pt-0 col-span-4">
                <PayoutChart 
                :currentYear="currentYear"
                :activePayout="activePayoutLabel"
                :search="search"
                :date="date"
                />
            </div>
        </div>

        <div class="p-5 my-5 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <ReportTable 
            :activePayout="activePayout"
            :exportStatus="exportStatus"
            @update:export="exportStatus = $event"
            @search="tableSearch"
            @date="tableDate"
            />
        </div>

    </AuthenticatedLayout>
</template>
