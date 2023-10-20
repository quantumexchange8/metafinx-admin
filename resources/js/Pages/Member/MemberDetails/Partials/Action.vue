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
    investments: Object,
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
        modalComponent.value = 'Unsubscribe Member Plan';
    }
}

const closeModal = () => {
    memberDetailModal.value = false
    modalComponent.value = null;
}


</script>

<template>
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
        <Button
            type="button"
            class="justify-center px-3 py-2 gap-2 grow focus:outline-none dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
            variant="success"
            @click="openMemberModal('verify_member')"
            v-if="type === 'member'"
            :disabled="member_details.kyc_approval === 'approved'"
        >
            <VerifyMemberIcon aria-hidden="true" class="w-5 h-5" />
            <span class="text-sm">Verify Member</span>
        </Button>

    
    <Modal :show="memberDetailModal" :title="modalComponent" @close="closeModal" max-width="xl">
        <div class="">
            <template v-if="modalComponent === 'Unsubscribe Member Plan'">
                <UnsubscribePlan
                    :investments="investments"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
            <template v-if="modalComponent === 'Edit Member'">
                <EditMember
                    :member_details="member_details"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
            <template v-if="modalComponent === 'Verify Member'">
                <VerifyMember
                    :member_details="member_details"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
        </div>
    </Modal>
</template>