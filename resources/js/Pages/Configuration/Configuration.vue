<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import ConfigurationContent from "@/Pages/Configuration/Partials/ConfigurationContent.vue";
import {ref} from "vue";
import Announcement from "@/Pages/Configuration/Announcement/Announcement.vue";

const props = defineProps({
    users: Array
})

const content = ref('Announcement');

const updateContent = (newContent) => {
    content.value = newContent;
}
</script>

<template>
    <AuthenticatedLayout title="Configuration">
        <div class="flex">
            <section
                tabindex="-1"
                class="fixed inset-y-0 z-10 flex-shrink-0 w-60 bg-white border-r dark:border-gray-700 dark:bg-gray-800 lg:static focus:outline-none"
                aria-labelledby="secondSidebarHeader"
            >
                <div class="flex flex-col h-screen">
                    <!-- Panel header -->
                    <div class="pb-4 sm:py-6 px-0">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <h2 class="text-xl md:text-2xl font-semibold leading-tight">
                                Configuration
                            </h2>
                        </div>
                    </div>

                    <!-- Panel content -->
                    <div class="flex-1 overflow-y-hidden hover:overflow-y-auto">
                        <ConfigurationContent
                            :content="content"
                            @update:content="updateContent"
                        />
                    </div>
                </div>
            </section>
            <Announcement
                v-if="content==='Announcement'"
            />
        </div>

    </AuthenticatedLayout>
</template>
