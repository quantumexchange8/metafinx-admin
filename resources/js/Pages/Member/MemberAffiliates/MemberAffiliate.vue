<script setup>
import { ref } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ChevronRight} from "@/Components/Icons/outline.jsx";
import AffiliateTree from "@/Pages/Member/MemberAffiliates/Partials/AffiliateTree.vue";
import GenealogyTree from "@/Pages/Member/MemberAffiliates/Partials/GenealogyTree.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Input from "@/Components/Input.vue";
import {SearchIcon} from "@heroicons/vue/outline";

const categories = ref([
    {
        id: 1,
        name: 'UniLevel Network',
        type: 'affiliate',
    },
    {
        id: 2,
        name: "Binary Network",
        type: 'binary',
    },
]);

const props = defineProps({
    referredCounts: Number,
    user: Object,
    downline: Array,
    uplineStaking: Boolean,
})
const search = ref('');
</script>

<template>
    <AuthenticatedLayout title="Member Affiliate">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-sm font-semibold dark:text-gray-400">
                    <a class="dark:hover:text-white" href="/member/listing">Member Listing</a>
                </h2>
                <ChevronRight aria-hidden="true" class="w-6 h-6" />
                <h2 class="text-sm font-semibold dark:text-white">
                    {{ user.name }} - View Details
                </h2>
            </div>
        </template>

        <!-- <AffiliateTree
            :user="user"
        /> -->

        <div class="flex justify-between">
            <div class="w-full">
                <TabGroup>
                    <TabList class="flex dark:bg-transparent w-full flex-col gap-3 sm:flex-row sm:justify-between">
                        <div>
                            <Tab
                                v-for="category in categories"
                                as="template"
                                :key="category"
                                v-slot="{ selected }"
                            >
                                <button
                                    v-show="category.type !== 'binary' || uplineStaking"
                                    class="px-4 py-2.5 text-sm font-semibold text-gray-900 border border-gray-200 focus:outline-none w-full sm:w-64"
                                    :class="{
                                    'rounded-lg': !uplineStaking && !$page.props.auth.user.binary && category.type === 'affiliate',
                                    'rounded-l-xl': category.type === 'affiliate' && uplineStaking,
                                    'rounded-r-xl': category.type === 'binary' && uplineStaking,
                                    'hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600': true,
                                    'bg-transparent dark:bg-[#38425080] dark:text-white': selected
                                }"
                                >
                                    {{ category.name }}
                                </button>
                            </Tab>
                        </div>
                        <div>
                            <InputIconWrapper>
                                <template #icon>
                                    <SearchIcon aria-hidden="true" class="w-5 h-5" />
                                </template>
                                <Input
                                    withIcon
                                    id="search"
                                    type="text"
                                    class="block border-transparent w-full"
                                    placeholder="Seach Name / Email"
                                    v-model="search"
                                />
                            </InputIconWrapper>
                        </div>

                    </TabList>
                    <TabPanels class="mt-2">
                        <TabPanel>
                            <AffiliateTree
                                :user="user"
                                :search="search"
                            />
                        </TabPanel>
                        <TabPanel>
                            <GenealogyTree
                                :user="user"
                                :downline="downline"
                                :search="search"
                            />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
