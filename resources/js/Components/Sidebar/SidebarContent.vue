<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import {DashboardIcon, UserIcon, IPOIcon, XLCIcon, TransactionIcon, ConfigurationIcon, ReportIcon, AdminUserIcon, LogoutIcon} from '@/Components/Icons/outline'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { usePermission } from "@/Composables/permissions";

const { hasRole, hasPermission } = usePermission();
</script>

<template>
    <PerfectScrollbar
        tagname="nav"
        aria-label="main"
        class="relative flex flex-col flex-1 max-h-full gap-2 px-3"
    >
        <SidebarLink
            title="Dashboard"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
            v-if="hasRole('admin') || hasPermission('ViewDashboard')"
        >
            <template #icon>
                <DashboardIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="Member Listing"
            :href="route('member.member_listing')"
            :active="route().current('member.*')"
            v-if="hasRole('admin') || hasPermission('ViewMemberListing')"
        >
            <template #icon>
                <UserIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="IPO Scheme Setting"
            :href="route('ipo_scheme.setting')"
            :active="route().current('ipo_scheme.*')"
            v-if="hasRole('admin') || hasPermission('ViewSchemeSettingIPO')"
        >
            <template #icon>
                <IPOIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="MXT Setting"
            :href="route('mxt.setting')"
            :active="route().current('mxt_setting.*')"
            v-if="hasRole('admin') || hasPermission('ViewSettingMXT')"
        >
            <template #icon>
                <XLCIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="Transaction"
            :href="route('transaction.listing')"
            :active="route().current('transaction.*')"
            v-if="hasRole('admin') || hasPermission('ViewTransaction')"
        >
            <template #icon>
                <TransactionIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="Report"
            :href="route('report.view')"
            :active="route().current('report.*')"
            v-if="hasRole('admin') || hasPermission('ViewReport')"
        >
            <template #icon>
                <ReportIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

        <SidebarLink
            title="Configuration"
            :href="route('configuration.index')"
            :active="route().current('configuration.*')"
            v-if="hasRole('admin') || hasPermission('ViewConfiguration')"
        >
            <template #icon>
                <ConfigurationIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>

       <SidebarLink
           title="Admin User"
           :href="route('admin_user.admin_listing')"
           :active="route().current('admin_user.*')"
           v-if="hasRole('admin') || hasPermission('ViewAdminUser')"
       >
           <template #icon>
               <AdminUserIcon
                   class="flex-shrink-0 w-6 h-6"
                   aria-hidden="true"
               />
           </template>
       </SidebarLink>

        <SidebarLink
            title="Log Out"
            :href="route('logout')"
            method="post"
            as="button"
            class="mt-6"
        >
            <template #icon>
                <LogoutIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </SidebarLink>


        <!-- Examples -->
        <!--
        => External link example
        <SidebarLink
            title="Github"
            href="https://github.com/kamona-wd/kui-laravel-breeze"
            external
            target="_blank"
        >
        </SidebarLink>

        => Collapsible examples
        <SidebarCollapsible title="Users" :active="$page.url.startsWith('/users')">
            <SidebarCollapsibleItem :href="route('users.index')" title="List" :active="$page.url === '/users/index'" />
            <SidebarCollapsibleItem :href="route('users.create')" title="Create" :active="$page.url === '/users/create'" />
        </SidebarCollapsible>

        <SidebarCollapsible title="Users" :active="usePage().url.value.startsWith('/users')">
            <template #icon>
                <UserIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>

            <SidebarCollapsibleItem :href="route('users.index')" title="List" :active="route().current('users.index')" />
            <SidebarCollapsibleItem :href="route('users.create')" title="Create" :active="route().current('users.create')" />
        </SidebarCollapsible>-->
    </PerfectScrollbar>
</template>
