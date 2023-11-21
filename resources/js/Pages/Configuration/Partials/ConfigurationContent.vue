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
        class="relative flex flex-col flex-1 max-h-full gap-2 pr-6"
    >
        <ConfigurationLink
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
            title="Ticket Bonus"
            @click="updateContent('TicketBonus')"
            :active="props.content==='TicketBonus'"
        >
            <template #icon>
                <TicketBonusIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>
        </ConfigurationLink>

        <ConfigurationLink
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

    </PerfectScrollbar>
</template>
