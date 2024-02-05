<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ref} from "vue";
import PendingTransaction from "@/Pages/Transaction/PendingTransaction/PendingTransaction.vue";
import TransactionHistory from "@/Pages/Transaction/TransactionHistory/TransactionHistory.vue";
import {SearchIcon, RefreshIcon} from "@heroicons/vue/outline";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";
import BaseListbox from "@/Components/BaseListbox.vue";

const activeComponent = ref("pending"); // 'pending' is initially active
const refresh = ref(false);
const isLoading = ref(false);
const search = ref('');
const date = ref('');
const filter = ref('');
const exportStatus = ref(false);
const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const statusList = [
    {value:'Success', label:"Success"},
    {value:'Rejected', label:"Rejected"},
];

const setActiveComponent = (component) => {
    activeComponent.value = component;
};

function refreshTable() {
    search.value = '';
    date.value = '';
    filter.value = '';
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const exportTransaction = () => {
    exportStatus.value = true;
}
</script>

<template>
    <AuthenticatedLayout title="Transaction">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight">
                        Transaction
                    </h2>
                    <p class="text-base font-normal dark:text-gray-400">
                        Track and manage all transactions carried out by your members.
                    </p>
                </div>

                <div>
                    <Button
                        type="button"
                        class="justify-center w-full gap-2 border border-gray-600 text-white text-sm dark:hover:bg-gray-600"
                        variant="transparent"
                        v-slot="{ iconSizeClasses }"
                        @click="exportTransaction"
                    >
                        <div class="inline-flex items-center">
                            <CloudDownloadIcon
                                aria-hidden="true"
                                class="mr-2 w-5 h-5"
                            />
                            <span>Export as Excel</span>
                        </div>
                    </Button>
                </div>

            </div>
        </template>

        <div class="pt-3 md:flex md:justify-between items-center">
            <div class="inline-flex items-center justify-center rounded-md shadow-sm" role="group">
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
                <RefreshIcon
                    :class="{ 'animate-spin': isLoading }"
                    class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white ml-5 mr-5"
                    aria-hidden="true"
                    @click="refreshTable"
                />
            </div>
            <div class="flex flex-wrap md:flex-nowrap gap-3 mt-3 md:mt-0">
                <div class="w-full">
                    <InputIconWrapper class="w-full md:w-[280px]">
                        <template #icon>
                            <SearchIcon aria-hidden="true" class="w-5 h-5" />
                        </template>
                        <Input withIcon id="search" type="text" class="block w-full border border-transparent" placeholder="Search" v-model="search" />
                    </InputIconWrapper>
                </div>
                <div class="w-full md:w-[280px]">
                    <vue-tailwind-datepicker
                        placeholder="Select dates"
                        :formatter="formatter"
                        separator=" - "
                        v-model="date"
                        input-classes="py-2.5 border-gray-400 rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                        class="w-full md:w-[230px]"
                    />
                </div>
                <div class="w-full md:w-[280px]">
                    <BaseListbox
                        id="statusID"
                        class="rounded-lg text-base w-full md:w-[155px] text-black dark:text-white dark:bg-gray-600"
                        v-model="filter"
                        :options="statusList"
                        placeholder="Filter status"

                    />
                </div>
                <div class="w-full">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="refreshTable"
                        class="w-full md:w-auto flex items-center justify-center px-3 py-2 border border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                    >
                        Clear
                    </Button>
                </div>
            </div>
        </div>

        <div class="p-5 my-8 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <PendingTransaction
                v-if="activeComponent === 'pending'"
                :refresh="refresh"
                :isLoading="isLoading"
                :search="search"
                :date="date"
                :exportStatus="exportStatus"
                @update:loading="isLoading = $event"
                @update:refresh="refresh = $event"
                @update:export="exportStatus = $event"
            />
            <TransactionHistory
                v-if="activeComponent === 'history'"
                :refresh="refresh"
                :isLoading="isLoading"
                :search="search"
                :date="date"
                :filter="filter"
                :exportStatus="exportStatus"
                @update:loading="isLoading = $event"
                @update:refresh="refresh = $event"
                @update:export="exportStatus = $event"
            />
        </div>
    </AuthenticatedLayout>
</template>
