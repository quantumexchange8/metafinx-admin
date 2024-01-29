<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import XLCCoinChart from "@/Pages/XLCSetting/TotalXlcoinSupply/XLCCoinChart.vue";
import CoinTransactionHistoryTable from "@/Pages/XLCSetting/Partials/CoinTransactionHistoryTable.vue";
import {computed, ref} from 'vue'
import { Switch } from '@headlessui/vue'
import AddInvestmentPlan from "@/Pages/IpoSchemeSetting/Partials/AddInvestmentPlan.vue";
import Button from "@/Components/Button.vue";
import EditInvestmentPlan from "@/Pages/IpoSchemeSetting/Partials/EditInvestmentPlan.vue";

const props = defineProps({
    investmentPlans: Object,
    totalInvestmentCount: String,
    totalEarningCount: String,
    onGoingAmountCount: String,
    coinTransactions: Object,
})

const currentYear = new Date().getFullYear();

const enabled = ref(false);

const toggleStatus = (investmentPlan) => {
    // Send a POST request to update the status field
    investmentPlan.status = investmentPlan.status === 'active' ? 'inactive' : 'active';
    updateStatus(investmentPlan.id, investmentPlan.status);
};

const updateStatus = async (planId, newStatus) => {
    try {
        const response = await axios.post('/ipo_scheme/updateStatus', {
            id: planId,
            status: newStatus
        });
        console.log('Status updated successfully:');
    } catch (error) {
        console.error('Error updating status:', error);
    }
};
</script>

<template>
    <AuthenticatedLayout title="MXT Setting">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-2xl font-semibold leading-tight">
                    MXT Setting
                </h2>
            </div>
            <p class="text-base font-normal dark:text-gray-400">
                View and modify ongoing MXT schemes provided to your members.
            </p>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-5 md:gap-5">
            <div class="grid grid-cols-2 md:grid-cols-1 gap-5 col-span-2">
                <div class="px-5 py-2.5 flex items-center rounded-[10px] dark:bg-gray-700">
                    <div class="grid gap-2">
                        <div class="text-xs font-medium dark:text-gray-400">
                            Current MXT Coin Price (today)
                        </div>
                        <div class="text-xl font-semibold">
                            MYR 
                        </div>
                    </div>
                </div>
                <div class="px-5 py-2.5 flex items-center rounded-[10px] dark:bg-gray-700">
                    <div class="grid gap-2">
                        <div class="text-xs font-medium dark:text-gray-400">
                            Current Investors (pax)
                        </div>
                        <div class="text-xl font-semibold">
                            
                        </div>
                    </div>
                </div>
                <div class="px-5 py-2.5 flex items-center rounded-[10px] dark:bg-gray-700">
                    <div class="grid gap-2">
                        <div class="text-xs font-medium dark:text-gray-400">
                            Total Supply
                        </div>
                        <div class="text-xl font-semibold">
                            
                        </div>
                    </div>
                </div>
                <div class="px-5 py-2.5 flex items-center rounded-[10px] dark:bg-gray-700">
                    <div class="grid gap-2">
                        <div class="text-xs font-medium dark:text-gray-400">
                            Market Capitalisation
                        </div>
                        <div class="text-xl font-semibold">
                            MYR 
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-5 md:pt-0 col-span-3">
                <XLCCoinChart 
                    :currentYear="currentYear"
                />
            </div>
        </div>

        
        <div class="p-5 my-5 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <CoinTransactionHistoryTable />
        </div>

        <template #asideRight>
            <div class="inset-y-0 p-6 flex flex-col space-y-6 bg-white shadow-lg dark:bg-gray-800 border-l dark:border-gray-700 lg:w-96 fixed right-0">
                <h3 class="text-xl font-semibold leading-tight">
                    Ongoing Staking Plan
                </h3>
                <!-- <AddInvestmentPlan /> -->
                <div v-for="investmentPlan in props.investmentPlans" class="p-5 dark:bg-gray-700 rounded-[20px] flex flex-col gap-2">
                    <div class="flex justify-between">
                        <div class="inline-flex items-center justify-center gap-3">
                            <!-- <img class="w-12 h-12 rounded-lg bg-white" src="/assets/icon.png" alt="Medium avatar"> -->
                            <img class="w-12 h-12 rounded-lg bg-white" :src="investmentPlan.plan_logo ? investmentPlan.plan_logo : '/assets/icon.png'" alt="Medium avatar">
                            <div class="grid">
                                <div class="font-semibold">
                                    {{ investmentPlan.name['en'] }}
                                </div>
                                <div class="font-normal dark:text-gray-400">
                                    {{ investmentPlan.roi_per_annum }}% p.a.
                                </div>
                            </div>
                        </div>
                        <Switch
                            :modelValue="enabled.value && investmentPlan.status === 'active'"
                            :class="investmentPlan.status === 'active' ? 'bg-success-500' : 'bg-gray-500'"
                            class="relative inline-flex h-[24px] w-[48px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
                            @click="toggleStatus(investmentPlan)"
                        >
                            <span class="sr-only">Toggle Active</span>
                            <span
                                aria-hidden="true"
                                :class="investmentPlan.status === 'active' ? 'translate-x-6' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-[20px] w-[20px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                            />
                        </Switch>
                    </div>
                    <div class="pt-5">
                        <EditInvestmentPlan
                            :investmentPlan="investmentPlan"
                        />
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
