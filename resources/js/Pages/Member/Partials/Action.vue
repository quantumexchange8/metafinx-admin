<script setup>
import Button from "@/Components/Button.vue";
import {MemberDetailIcon, AffiliateTreeIcon, DeleteIcon} from "@/Components/Icons/outline.jsx";
import {ref} from "vue";
import Modal from "@/Components/Modal.vue";
// import DeleteMember from "@/Pages/Member/Partials/DeleteMember.vue";
import Tooltip from "@/Components/Tooltip.vue";


const props = defineProps({
    member: Object,
    countries: Object,
    accountTypes: Object,
    getMemberSel: Array,
})

// const memberDetailModal = ref(false);
// const getMemberId = ref(null);
// const modalComponent = ref(null);

// const openMemberDetail = (memberId, componentType) => {
//     memberDetailModal.value = true;
//     if (componentType === 'deleteMember') {
//         modalComponent.value = 'DeleteMemberComponent';
//     }
// }

// const closeModal = () => {
//     memberDetailModal.value = false
//     modalComponent.value = null;
// }

// const openInNewTab = (url) => {
//     window.open(url, '_blank');
// }


</script>

<template>
    <div class="flex justify-center">
        <Tooltip content="View Details" placement="top">
            <!-- @click="openInNewTab(route('member.impersonate', member.id))" -->
            <Button
                type="button"
                class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                variant="action"
            >
                <MemberDetailIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">View Details</span>
            </Button>
        </Tooltip>
        <Tooltip content="Affiliate Tree" placement="top">
            <!-- @click="openInNewTab(route('member.impersonate', member.id))" -->
            <Button
                class="justify-center p-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                variant="action"
            >
                <AffiliateTreeIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">Affiliate Tree</span>
            </Button>
        </Tooltip>
        <Tooltip content="Delete Member" placement="top">
            <!-- @click="openMemberDetail(member.id, 'deleteMember')" -->
            <Button
                class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                variant="danger"

            >
                <DeleteIcon aria-hidden="true" class="w-5 h-5 absolute" />
                <span class="sr-only">Delete</span>
            </Button>
        </Tooltip>
    </div>

    
    <!-- <Modal :show="memberDetailModal" @close="closeModal">
        <div class="p-6">

            <template v-if="modalComponent === 'DeleteMemberComponent'">
                <DeleteMember
                    :member="member"
                    @update:memberDetailModal="memberDetailModal = $event"
                />
            </template>

        </div>
    </Modal> -->
</template>