<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import ConfigurationLink from "@/Pages/Configuration/Partials/ConfigurationLink.vue";
import {AnnouncementIcon, DividendBonusIcon, TicketBonusIcon, AffiliateSettingIcon, CoinSettingIcon} from '@/Components/Icons/outline'
import { usePermission } from "@/Composables/permissions";

const props = defineProps({
    content: String
})
const { hasRole, hasPermission } = usePermission();
const emit = defineEmits(['update:content'])

const updateContent = (newContent) => {
    // Emit the update:content event with the new content
    emit('update:content', newContent)
}
</script>

<template>
    <PerfectScrollbar
        tagname="nav"
        aria-label="main"
        class="relative grid grid-cols-4 md:flex md:flex-col md:flex-1 md:max-h-full gap-2 md:pr-6 [overflow-wrap:anywhere]"
    >
        <ConfigurationLink
            class="flex-col md:flex-row"
            title="Announcement"
            @click="updateContent('Announcement')"
            :active="props.content==='Announcement'"
            v-if="hasRole('admin') || hasPermission('ViewAnnouncement')"
        >
            <template #icon>
                <AnnouncementIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
            class="flex-col md:flex-row"
            title="T&C Setting"
            @click="updateContent('TnCSetting')"
            :active="props.content==='TnCSetting'"
            v-if="hasRole('admin') || hasPermission('ViewTermsSetting')"
        >
            <template #icon>
                <AnnouncementIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

         <ConfigurationLink
            class="flex-col md:flex-row"
            title="Dividend Bonus"
            @click="updateContent('DividendBonus')"
            :active="props.content==='DividendBonus'"
            v-if="hasRole('admin') || hasPermission('ViewDividendBonus')"
        >
            <template #icon>
                <DividendBonusIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
            class="flex-col md:flex-row"
            title="Affiliate Setting"
            @click="updateContent('AffiliateSetting')"
            :active="props.content==='AffiliateSetting'"
            v-if="hasRole('admin') || hasPermission('ViewAffiliateSetting')"
        >
            <template #icon>
                <AffiliateSettingIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
            class="flex-col md:flex-row"
            title="Master Setting"
            @click="updateContent('MasterSetting')"
            :active="props.content==='MasterSetting'"
            v-if="hasRole('admin') || hasPermission('ViewMasterSetting')"
        >
            <template #icon>
                <TicketBonusIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
            class="flex-col md:flex-row"
            title="MXT Coin"
            @click="updateContent('CoinSetting')"
            :active="props.content==='CoinSetting'"
            v-if="hasRole('admin') || hasPermission('ViewCoinPrice')"
        >
            <template #icon>
                <CoinSettingIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
            class="flex-col md:flex-row"
            title="Staking Reward"
            @click="updateContent('StakingReward')"
            :active="props.content==='StakingReward'"
            v-if="hasRole('admin') || hasPermission('ViewStakingReward')"
        >
            <template #icon>
                <CoinSettingIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>


    </PerfectScrollbar>
</template>
