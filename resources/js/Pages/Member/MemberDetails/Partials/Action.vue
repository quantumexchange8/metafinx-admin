<script setup>
import Button from "@/Components/Button.vue";
import {UnsubscribeIcon, VerifyMemberIcon, EditIcon} from "@/Components/Icons/outline.jsx";
import {ref} from "vue";
import Modal from "@/Components/Modal.vue";
import UnsubscribePlan from "@/Pages/Member/MemberDetails/Partials/UnsubscribePlan.vue";
import EditMember from "@/Pages/Member/MemberDetails/Partials/EditMember.vue";
import VerifyMember from "@/Pages/Member/MemberDetails/Partials/VerifyMember.vue";
import Tooltip from "@/Components/Tooltip.vue";


const props = defineProps({
    member_details: Object,
    upline_member: Object,
    investments: Object,
    settingRank: Array,
    type: String,
})

const memberDetailModal = ref(false);
// const getMemberId = ref(null);
const modalComponent = ref(null);

const openMemberModal = (componentType) => {
    memberDetailModal.value = true;
    if (componentType === 'edit_member') {
        modalComponent.value = 'Edit Member';
    }
    else if (componentType === 'verify_member') {
        modalComponent.value = 'Verify Member';
    }
    else if (componentType === 'unsubscribe_plan') {
        modalComponent.value = 'Terminate Member Plan';
    }
}

const closeModal = () => {
    memberDetailModal.value = false
    modalComponent.value = null;
}


</script>

<template>
    <div>
        <Tooltip content="Unsubscribe" placement="bottom" v-if="type === 'investment'">
            <Button
                type="button"
                class="justify-center px-4 pt-2 w-8 h-8 relative focus:outline-none"
                variant="danger"
                @click="openMemberModal('unsubscribe_plan')"
                pill
            >
                <UnsubscribeIcon aria-hidden="true" class="w-7 h-7 absolute" />
                <span class="sr-only">Unsubscribe</span>
            </Button>
        </Tooltip>
        <Button
            type="button"
            class="justify-center px-3 py-2 gap-2 grow focus:outline-none"
            variant="gray"
            @click="openMemberModal('edit_member')"
            v-if="type === 'member'"
        >
            <EditIcon aria-hidden="true" class="w-5 h-5" />
            <span class="text-sm">Edit</span>
        </Button>
    </div>

    <Modal :show="memberDetailModal" :title="modalComponent" @close="closeModal" max-width="xl">
        <div class="">
            <template v-if="modalComponent==='Terminate Member Plan'">
                <UnsubscribePlan
                    :investments="investments"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
            <template v-if="modalComponent==='Edit Member'">
                <EditMember
                    :member_details="member_details"
                    :upline_member="upline_member"
                    :settingRank="settingRank"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
        </div>
    </Modal>
</template>
