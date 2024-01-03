<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import XLCCoinChart from "@/Pages/XLCSetting/Partials/XLCCoinChart.vue";
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
})

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
    <AuthenticatedLayout title="XLC Setting">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-2xl font-semibold leading-tight">
                    XLC Setting
                </h2>
            </div>
            <p class="text-base font-normal dark:text-gray-400">
                View and modify ongoing XLC schemes provided to your members.
            </p>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-5 md:gap-5">
            <div class="grid grid-cols-2 md:grid-cols-1 gap-5 col-span-2">
                <div class="px-5 py-2.5 flex items-center rounded-[10px] dark:bg-gray-700">
                    <div class="grid gap-2">
                        <div class="text-xs font-medium dark:text-gray-400">
                            Current XLC Coin Price (today)
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
                <XLCCoinChart />
            </div>
        </div>

        
        <div class="p-5 my-5 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <CoinTransactionHistoryTable />
        </div>
    </AuthenticatedLayout>
</template>
