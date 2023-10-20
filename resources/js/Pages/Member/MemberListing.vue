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

const props = defineProps({
    settingRanks: Object
})

const search = ref('');
const date = ref('');
const type = ref('Deposit');
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

const updateTransactionType = (transaction_type) => {
    type.value = transaction_type
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
                <div class="md:w-2/3">
                    <vue-tailwind-datepicker
                        placeholder="Select dates"
                        :formatter="formatter"
                        separator=" - "
                        v-model="date"
                        input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                    />
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
                <span class="flex items-center text-xs font-normal text-gray-900 dark:text-white"><span class="flex w-2 h-2 bg-red-500 dark:bg-warning-500 rounded-full mr-2 flex-shrink-0"></span>Unverified</span>
                <span class="flex items-center text-xs font-normal text-gray-900 dark:text-white"><span class="flex w-2 h-2 bg-red-500 dark:bg-success-500 rounded-full mr-2 flex-shrink-0"></span>Verified</span>
            </div>

            <div class="w-full pt-5">
                <TabGroup>
                    <TabList class="max-w-xs flex py-1">
                        <Tab
                            v-for="settingRank in settingRanks"
                            :key="settingRank.id"
                            as="template"
                            v-slot="{ selected }"
                        >
                            <button
                                @click="updateTransactionType(settingRank.name)"
                                :class="[
                                    'w-full py-2.5 text-sm font-semibold dark:text-gray-400',
                                    'ring-white ring-offset-0 focus:outline-none focus:ring-0',
                                    selected ? 'dark:text-white border-b-2' : 'border-b border-gray-400',
                                ]"
                            >
                                {{ settingRank.name }}
                            </button>
                        </Tab>
                    </TabList>

                    <TabPanels>
                        <TabPanel
                            v-for="settingRank in settingRanks"
                            :key="settingRank.id"
                        >
                            <MemberTable
                                :refresh="refresh"
                                :isLoading="isLoading"
                                :search="search"
                                :date="date"
                                :settingRankId=settingRank.id
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
