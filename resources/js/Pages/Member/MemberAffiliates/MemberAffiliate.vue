<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ChevronRight} from "@/Components/Icons/outline.jsx";
import AffiliateTree from "@/Pages/Member/MemberAffiliates/Partials/AffiliateTree.vue";

const props = defineProps({
    referredCounts: Number,
    user: Object
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

        <AffiliateTree 
            :user="user"
        />

    </AuthenticatedLayout>
</template>
