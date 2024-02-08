<script setup>
import { ref } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ChevronRight} from "@/Components/Icons/outline.jsx";
import AffiliateTree from "@/Pages/Member/MemberAffiliates/Partials/AffiliateTree.vue";
import GenealogyTree from "@/Pages/Member/MemberAffiliates/Partials/GenealogyTree.vue";

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
                    <TabList class="flex dark:bg-transparent w-full">
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
                    </TabList>
                    <TabPanels class="mt-2">
                        <TabPanel>
                            <AffiliateTree 
                                :user="user"
                            />
                        </TabPanel>
                        <TabPanel>
                            <GenealogyTree
                                :user="user"
                                :downline="downline"
                            />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
