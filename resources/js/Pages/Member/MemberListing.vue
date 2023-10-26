<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { RefreshIcon, SearchIcon } from "@heroicons/vue/outline";
import {ref} from "vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {CloudDownloadIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import MemberTable from "@/Pages/Member/Partials/MemberTable.vue";
import Action from "@/Pages/Member/Partials/Action.vue";
import BaseListbox from "@/Components/BaseListbox.vue";

const props = defineProps({
    settingRanks: Array,
    countries: Array,
    pendingKycCount: Number,
    unverifiedKycCount: Number,
})

const search = ref('');
const date = ref('');
const type = ref('');
const rank = ref('');
const isLoading = ref(false);
const refresh = ref(false);
const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const kycStatuses = [
    { value: '', name: 'All' },
    { value: 'pending', name: 'Pending' },
    { value: 'verified', name: 'Verified' },
    { value: 'unverified', name: 'Unverified' },
]

const rankList = [
    {value:'1', label:"Member"},
    {value:'2', label:"LVL 1"},
    {value:'3', label:"LVL 2"},
    {value:'4', label:"LVL 3"},
];

const updateKycStatus = (kyc_status) => {
    type.value = kyc_status
};
</script>

<template>
    <AuthenticatedLayout title="Member Listing">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight">
                        Member Listing
                    </h2>
                    <p class="text-base font-normal dark:text-gray-400">
                        Track and manage all your members accounts here
                    </p>
                </div>
                <Action
                    type="add_member"
                    :settingRanks="settingRanks"
                    :countries="countries"
                />
            </div>
        </template>

        <div class="p-5 my-5 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700">
            <div class="flex justify-between">
                <h4 class="font-semibold dark:text-white">All Members</h4>
                <RefreshIcon
                    :class="{ 'animate-spin': isLoading }"
                    class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
                    aria-hidden="true"
                    @click="refreshTable"
                />
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="w-full">
                    <InputIconWrapper class="md:col-span-2">
                        <template #icon>
                            <SearchIcon aria-hidden="true" class="w-5 h-5" />
                        </template>
                        <Input withIcon id="search" type="text" class="block w-full" placeholder="Search" v-model="search" />
                    </InputIconWrapper>
                </div>
                <div class="flex gap-3">
                    <div>
                        <vue-tailwind-datepicker
                            placeholder="Select dates"
                            :formatter="formatter"
                            separator=" - "
                            v-model="date"
                            input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                        />
                    </div>
                    <div>
                        <BaseListbox
                            id="rankID"
                            class="w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600"
                            v-model="rank"
                            :options="rankList"
                            placeholder="Filter rank"
                        />
                    </div>
                </div>

<!--                <div class="flex justify-end">-->
<!--                    <Button-->
<!--                        type="button"-->
<!--                        class="justify-center w-full md:w-1/2 gap-2 border border-gray-600 text-white text-sm dark:hover:bg-gray-600"-->
<!--                        variant="transparent"-->
<!--                    >-->
<!--                        <CloudDownloadIcon aria-hidden="true" class="w-5 h-5" />-->
<!--                        <span>Export as Excel</span>-->
<!--                    </Button>-->
<!--                </div>-->
            </div>

            <div class="flex gap-4 mt-5">
                <span class="flex items-center text-xs font-normal text-gray-900 dark:text-white"><span class="flex w-2 h-2 bg-blue-500 dark:bg-blue-500 rounded-full mr-2 flex-shrink-0"></span>Pending</span>
                <span class="flex items-center text-xs font-normal text-gray-900 dark:text-white"><span class="flex w-2 h-2 bg-warning-500 dark:bg-warning-500 rounded-full mr-2 flex-shrink-0"></span>Unverified</span>
                <span class="flex items-center text-xs font-normal text-gray-900 dark:text-white"><span class="flex w-2 h-2 bg-success-500 dark:bg-success-500 rounded-full mr-2 flex-shrink-0"></span>Verified</span>
            </div>

            <div class="w-full pt-5">
                <TabGroup>
                    <TabList class="max-w-md flex py-1">
                        <Tab
                            v-for="kycStatus in kycStatuses"
                            as="template"
                            v-slot="{ selected }"
                        >
                            <button
                                @click="updateKycStatus(kycStatus.value)"
                                :class="[
                                    'w-full py-2.5 text-sm font-semibold dark:text-gray-400',
                                    'ring-white ring-offset-0 focus:outline-none focus:ring-0',
                                    selected ? 'dark:text-white border-b-2' : 'border-b border-gray-400',
                                ]"
                            >
                                {{ kycStatus.name }} <span v-if="kycStatus.value === 'pending'">({{ pendingKycCount }})</span><span v-if="kycStatus.value === 'unverified'">({{ unverifiedKycCount }})</span>
                            </button>
                        </Tab>
                    </TabList>

                    <TabPanels>
                        <TabPanel
                            v-for="kycStatus in kycStatuses"
                        >
                            <MemberTable
                                :refresh="refresh"
                                :isLoading="isLoading"
                                :search="search"
                                :date="date"
                                :rank="rank"
                                :kycStatus=kycStatus.value
                                @update:loading="isLoading = $event"
                                @update:refresh="refresh = $event"
                            />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
