<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import ConfigurationLink from "@/Pages/Configuration/Partials/ConfigurationLink.vue";
import {AnnouncementIcon, DividendBonusIcon, TicketBonusIcon, AffiliateSettingIcon} from '@/Components/Icons/outline'

const props = defineProps({
    content: String
})
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
            title="Withdrawal Fee"
            @click="updateContent('WithdrawalFee')"
            :active="props.content==='WithdrawalFee'"
        >
            <template #icon>
                <TicketBonusIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

    </PerfectScrollbar>
</template>
