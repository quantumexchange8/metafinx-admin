<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ref} from "vue";
import PendingTransaction from "@/Pages/Transaction/PendingTransaction/PendingTransaction.vue";
import TransactionHistory from "@/Pages/Transaction/TransactionHistory/TransactionHistory.vue";

const activeComponent = ref("pending"); // 'pending' is initially active

const setActiveComponent = (component) => {
    activeComponent.value = component;
};
</script>

<template>
    <AuthenticatedLayout title="Transaction">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-2xl font-semibold leading-tight">
                    Transaction
                </h2>
            </div>
            <p class="text-base font-normal dark:text-gray-400">
                Track and manage all transactions carried out by your members.
            </p>
        </template>

        <div class="pt-3">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button
                    type="button"
                    class="px-4 py-2 text-sm font-semibold text-gray-900 border border-gray-200 rounded-l-xl hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                    :class="{ 'bg-transparent': activeComponent !== 'pending', 'dark:bg-[#38425080] dark:text-white': activeComponent === 'pending' }"
                    @click="setActiveComponent('pending')"
                >
                    Pending Transaction
                </button>
                <button
                    type="button"
                    class="px-4 py-2 text-sm font-semibold text-gray-900 border border-gray-200 rounded-r-xl hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                    :class="{ 'bg-transparent': activeComponent !== 'history', 'dark:bg-[#38425080] dark:text-white': activeComponent === 'history' }"
                    @click="setActiveComponent('history')"
                >
                    Transaction History
                </button>
            </div>
        </div>

        <div class="p-5 my-8 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <PendingTransaction v-if="activeComponent === 'pending'"/>
            <TransactionHistory v-if="activeComponent === 'history'"/>
        </div>
    </AuthenticatedLayout>
</template>
