<script setup>
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useFullscreen } from '@vueuse/core'
import {
    MenuIcon,
    XIcon,
    BellIcon,
    ChevronDownIcon,
} from '@heroicons/vue/outline'
import {
    handleScroll,
    isDark,
    scrolling,
    toggleDarkMode,
    sidebarState,
} from '@/Composables'
import Button from '@/Components/Button.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import {
    ArrowsInnerIcon, DashboardIcon,
    DashboardIconInactive,
    EarnIcon,
    InactiveEarnIcon, InactiveWalletIcon,
    WalletIcon,
    AffiliateIcon, InactiveAffiliateIcon
} from '@/Components/Icons/outline'

const { isFullscreen, toggle: toggleFullScreen } = useFullscreen()

onMounted(() => {
    document.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    document.removeEventListener('scroll', handleScroll)
})
</script>

<template>
    <nav
        aria-label="secondary"
        :class="[
            'md:hidden sticky top-0 z-10 p-4 md:py-8 md:px-4 bg-white flex items-center justify-between transition-transform duration-500 dark:bg-gray-800',
            {
                '-translate-y-full': scrolling.down,
                'translate-y-0': scrolling.up,
            },
        ]"
    >
        <div class="flex items-center gap-2">
            <Button
                iconOnly
                variant="secondary"
                type="button"
                @click="sidebarState.isOpen = !sidebarState.isOpen"
                v-slot="{ iconSizeClasses }"
                class="md:hidden"
                srText="Search"
            >
                <MenuIcon
                    v-show="!sidebarState.isOpen"
                    aria-hidden="true"
                    :class="iconSizeClasses"
                />
                <XIcon
                    v-show="sidebarState.isOpen"
                    aria-hidden="true"
                    :class="iconSizeClasses"
                />
            </Button>
        </div>
        <!--        <div class="flex items-center gap-2">-->
        <!--            <div class="flex flex-row">-->
        <!--                <div>-->
        <!--                    <Dropdown align="right">-->
        <!--                        <template #trigger>-->
        <!--                            <Button-->
        <!--                                iconOnly-->
        <!--                                variant="transparent"-->
        <!--                                type="button"-->
        <!--                                class="border-0 bg-transparent md:inline-flex p-0"-->
        <!--                                srText="Toggle dark mode"-->
        <!--                            >-->
        <!--                                <span class="dark:text-white">EN</span>-->
        <!--                                <ChevronDownIcon-->
        <!--                                    aria-hidden="true"-->
        <!--                                    class="w-4 h-4 ml-2 dark:text-white"-->
        <!--                                />-->
        <!--                            </Button>-->
        <!--                        </template>-->
        <!--                        <template #content>-->
        <!--                            <DropdownLink>-->
        <!--                                <div class="inline-flex items-center gap-2">-->
        <!--                                    English-->
        <!--                                </div>-->
        <!--                            </DropdownLink>-->
        <!--                            <DropdownLink>-->
        <!--                                <div class="inline-flex items-center gap-2">-->
        <!--                                    中文 (繁)-->
        <!--                                </div>-->
        <!--                            </DropdownLink>-->
        <!--                        </template>-->
        <!--                    </Dropdown>-->
        <!--                </div>-->
        <!--                <div>-->
        <!--                    <Button-->
        <!--                        iconOnly-->
        <!--                        variant="secondary"-->
        <!--                        type="button"-->
        <!--                        class="border-0 bg-transparent md:inline-flex p-0"-->
        <!--                        srText="Toggle dark mode"-->
        <!--                    >-->
        <!--                        <BellIcon-->
        <!--                            aria-hidden="true"-->
        <!--                            class="w-6 h-6 dark:text-white"-->
        <!--                        />-->
        <!--                    </Button>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
    </nav>

    <!-- Mobile bottom bar -->
    <div
        :class="[
            'fixed inset-x-0 z-50 bottom-0 flex items-center justify-between px-4 py-4 sm:px-6 transition-transform duration-500 bg-white md:hidden dark:bg-gray-900',
            {
                'translate-y-full': scrolling.down,
                'translate-y-0': scrolling.up,
            },
        ]"
    >
        <div>
            <Link :href="route('dashboard')">
                <div class="fixed bottom-4 dark:bg-gray-900 border-2 border-gray-800 rounded-full w-16 h-16 -translate-y-6">
                    <img src="/assets/icon.png" class="w-10 h-10 mx-auto mt-2" alt="logo" />
                </div>
                <div class="flex justify-center items-center mt-7 w-16">
                    <p :class="route().current('dashboard') ? 'text-pink-500' : 'text-white' " class="text-xs">
                        Dashboard
                    </p>
                </div>
            </Link>
        </div>
    </div>
</template>
