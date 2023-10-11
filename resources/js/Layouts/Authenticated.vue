<script setup>
import { Head } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'
import PageFooter from '@/Components/PageFooter.vue'
import { sidebarState } from '@/Composables'

defineProps({
    title: String
})
</script>

<template>
    <Head :title="title"></Head>

    <div
        class="min-h-screen text-gray-900 bg-gray-100 dark:bg-gray-800 dark:text-white"
    >
        <!-- Sidebar -->
        <Sidebar />

        <div
            style="transition-property: margin; transition-duration: 150ms"
            :class="[
                'min-h-screen flex flex-col',
                {
                    'lg:ml-64': sidebarState.isOpen,
                    'md:ml-16': !sidebarState.isOpen,
                },
            ]"
        >
            <!-- Navbar -->
            <Navbar />

            <main class="flex-1 px-4 sm:px-6 md:pt-0" :class="{ 'md:mr-80': $slots.asideRight, 'lg:mr-96': $slots.asideRight}">
                <!-- Page Heading -->
                <header v-if="$slots.header">
                    <div class="pb-4 sm:py-6 px-0">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
<!--                <Alert-->
<!--                    :show="showAlert"-->
<!--                    :on-dismiss="() => showAlert = false"-->
<!--                    :title="alertTitle"-->
<!--                    :intent="intent"-->
<!--                >-->
<!--                    {{ alertMessage }}-->
<!--                </Alert>-->
<!--                <ToastList />-->
                <slot />

            </main>

            <!-- <PageFooter class="hidden md:block"/> -->
        </div>

        <aside v-if="$slots.asideRight" class="hidden md:block">
            <slot name="asideRight" />
        </aside>
    </div>
</template>
