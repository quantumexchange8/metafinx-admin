<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import ConfigurationLink from "@/Pages/Configuration/Partials/ConfigurationLink.vue";
import {AnnouncementIcon, AffiliateSettingIcon} from '@/Components/Icons/outline'

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

<!--        <ConfigurationLink-->
<!--            title="Affiliate Setting"-->
<!--            @click="updateContent('AffiliateSetting')"-->
<!--            :active="props.content==='AffiliateSetting'"-->
<!--        >-->
<!--            <template #icon>-->
<!--                <AffiliateSettingIcon-->
<!--                    class="flex-shrink-0 w-6 h-6"-->
<!--                    aria-hidden="true"-->
<!--                />-->
<!--            </template>-->
<!--        </ConfigurationLink>-->

    </PerfectScrollbar>
</template>
