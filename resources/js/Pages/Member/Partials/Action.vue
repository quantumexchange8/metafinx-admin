<script setup>
import Button from "@/Components/Button.vue";
import {MemberDetailIcon, AffiliateTreeIcon, DeleteIcon, AddIcon} from "@/Components/Icons/outline.jsx";
import {ref} from "vue";
import Modal from "@/Components/Modal.vue";
import DeleteMember from "@/Pages/Member/Partials/DeleteMember.vue";
import AddMember from "@/Pages/Member/Partials/AddMember.vue";
import Tooltip from "@/Components/Tooltip.vue";
import { usePermission } from "@/Composables/permissions";

const props = defineProps({
    members: Object,
    type: String,
    settingRanks: Array,
    countries: Array,
})

const { hasRole, hasPermission } = usePermission();
const memberDetailModal = ref(false);
// const getMemberId = ref(null);
const modalComponent = ref(null);

const openMemberModal = (memberId, componentType) => {
    memberDetailModal.value = true;
    if (componentType === 'deleteMember') {
        modalComponent.value = 'Delete Member';
    }
    else if (componentType === 'add_member') {
        modalComponent.value = 'Add Member';
    }
}

const closeModal = () => {
    memberDetailModal.value = false
    modalComponent.value = null;
}

</script>

<template>
    <div class="flex justify-center">
        <Tooltip content="View Details" placement="bottom" v-if="type === 'member'" class="relative">
            <Button
                type="button"
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="action"
                :href="'/member/member_details/' + members.id"
                pill
                v-if="(hasRole('admin') || hasPermission('ViewMemberDetail'))"
            >
                <MemberDetailIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">View Details</span>
            </Button>
        </Tooltip>
        <Tooltip content="Affiliate Tree" placement="bottom" v-if="type === 'member'" class="relative">
            <Button
                type="button"
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="action"
                :href="'/member/member_affiliates/' + members.id"
                pill
                v-if="(hasRole('admin') || hasPermission('ViewMemberAffiliateTree'))"
            >
                <AffiliateTreeIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">Affiliate Tree</span>
            </Button>
        </Tooltip>
        <Tooltip content="Delete Member" placement="bottom" v-if="type === 'member'" class="relative">
            <Button
                type="button"
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="danger"
                @click="openMemberModal(members.id, 'deleteMember')"
                pill
                v-if="hasRole('admin') || hasPermission('DeleteMember')"
            >
                <DeleteIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">Delete</span>
            </Button>
        </Tooltip>
        <Button
            type="button"
            class="justify-center px-3 py-2 gap-2 grow focus:outline-none"
            variant="primary"
            @click="openMemberModal('','add_member')"
            v-if="(type === 'add_member') && (hasRole('admin') || hasPermission('AddNewMember'))"
        >
            <AddIcon aria-hidden="true" class="w-5 h-5" />
            <span class="text-sm">Add Member</span>
        </Button>
    </div>


    <Modal :show="memberDetailModal" :title="modalComponent" @close="closeModal" max-width="xl">

            <template v-if="modalComponent === 'Delete Member'">
                <DeleteMember
                    :members="members"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
            <template v-if="modalComponent === 'Add Member'">
                <AddMember
                    :settingRanks="settingRanks"
                    :countries="countries"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>
    </Modal>
</template>
