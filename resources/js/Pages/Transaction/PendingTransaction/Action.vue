<script setup>
import {ref} from "vue";
import Tooltip from "@/Components/Tooltip.vue";
import {MemberDetailIcon, alertTriangle} from "@/Components/Icons/outline.jsx";
import {CheckIcon, XIcon} from "@heroicons/vue/outline";
import Button from "@/Components/Button.vue";
import Modal from "@/Components/Modal.vue";
import {transactionFormat} from "@/Composables/index.js";
import {useForm} from "@inertiajs/vue3";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import { usePermission } from "@/Composables/permissions";

const props = defineProps({
    transaction: Object
})

const { hasRole, hasPermission } = usePermission();
const transactionModal = ref(false);
const modalComponent = ref(null);
const { formatDateTime, formatAmount, formatType } = transactionFormat();

const openTransactionModal = (ibId, componentType) => {
    transactionModal.value = true;
    if (componentType === 'approve') {
        modalComponent.value = 'Approve Transaction';
    } else if (componentType === 'reject') {
        modalComponent.value = 'Reject Transaction';
    } else if (componentType === 'rejectRemarks') {
        modalComponent.value = 'Reject Remark';
    } else if (componentType === 'view') {
        modalComponent.value = 'Transaction Details';
    }
}

const closeModal = () => {
    transactionModal.value = false
    modalComponent.value = null;
}

const form = useForm({
    id: props.transaction.id,
    type: 'single',
    remark: '',
});

const submitForm = () => {
    let submitRoute;
    if (modalComponent.value === 'Approve Transaction') {
        submitRoute = route('transaction.approveTransaction');
    } else if (modalComponent.value === 'Reject Remark') {
        submitRoute = route('transaction.rejectTransaction');
    }

    if (submitRoute) {
        form.post(submitRoute, {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        console.error('Invalid modal component:', modalComponent);
    }
}

</script>

<template>
    <div class="inline-flex justify-center items-center gap-2">
        <Tooltip content="Approve" placement="bottom" class="relative" v-if="hasRole('admin')">
            <Button
                type="button"
                pill
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="success"
                @click="openTransactionModal(transaction.id, 'approve')"
            >
                <CheckIcon aria-hidden="true" class="w-6 h-6 absolute" />
                <span class="sr-only">View</span>
            </Button>
        </Tooltip>
        <Tooltip content="Reject" placement="bottom" class="relative" v-if="hasRole('admin')">
            <Button
                type="button"
                pill
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="danger"
                @click="openTransactionModal(transaction.id, 'reject')"
            >
                <XIcon aria-hidden="true" class="w-6 h-6 absolute" />
                <span class="sr-only">Transfer Upline</span>
            </Button>
        </Tooltip>
        <Tooltip content="View" placement="bottom" class="relative" v-if="hasRole('admin') || hasPermission('ViewPendingWithdrawal')">
            <Button
                type="button"
                pill
                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                variant="action"
                @click="openTransactionModal(transaction.id, 'view')"
            >
                <MemberDetailIcon aria-hidden="true" class="w-6 h-6 absolute" />
                <span class="sr-only">Reset</span>
            </Button>
        </Tooltip>
    </div>

    <Modal :show="transactionModal" :title="modalComponent" @close="closeModal" max-width="lg">
        <!-- Approve -->
        <div v-if="modalComponent === 'Approve Transaction'">
            <div class="px-2 space-y-2">
                <alertTriangle />
                <h2 class="text-xl font-semibold dark:text-white pt-5">Approve Transaction</h2>
                <div class="text-sm font-normal dark:text-gray-400">
                    Do you want to approve a total amount of ${{ formatAmount(transaction.amount )}}?
                </div>
            </div>
            <div class="pt-5 px-2 grid grid-cols-2 gap-4">
                <Button type="button" variant="secondary" class="px-6 justify-center" @click="closeModal">
                    Cancel
                </Button>
                <Button class="px-6 justify-center" @click.prevent="submitForm">Confirm</Button>
            </div>
        </div>

        <!-- Reject -->
        <div v-if="modalComponent === 'Reject Transaction'">
            <div class="px-2 space-y-2">
                <alertTriangle />
                <h2 class="text-xl font-semibold dark:text-white pt-5">Reject Transaction</h2>
                <div class="text-sm font-normal dark:text-gray-400">
                    Do you want to reject a total amount of ${{ formatAmount(transaction.amount )}}?
                </div>
            </div>
            <div class="pt-5 px-2 grid grid-cols-2 gap-4">
                <Button type="button" variant="secondary" class="px-6 justify-center" @click="closeModal">
                    Cancel
                </Button>
                <Button class="px-6 justify-center" @click="openTransactionModal(transaction.id, 'rejectRemarks')">Confirm</Button>
            </div>
        </div>

        <!-- Reject Remarks -->
        <div v-if="modalComponent === 'Reject Remark'">
            <div class="flex gap-2 mt-3 mb-8">
                <Label class="text-sm dark:text-white w-1/4 pt-0.5" for="remark" value="Remark" />
                <div class="flex flex-col w-full">
                    <Input
                        id="remark"
                        type="text"
                        placeholder="Enter remark (visible to member)"
                        class="block w-full"
                        :class="form.errors.remark ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.remark"
                    />
                    <InputError :message="form.errors.remark" class="mt-2" />
                </div>
            </div>
            <div class="pt-5 px-2 grid grid-cols-2 gap-4 border-t dark:border-gray-700">
                <Button type="button" variant="secondary" class="px-6 justify-center" @click="closeModal">
                    Cancel
                </Button>
                <Button class="px-6 justify-center" @click.prevent="submitForm">Confirm</Button>
            </div>
        </div>

        <!-- View -->
        <div v-if="modalComponent === 'Transaction Details'">

        <!-- Transaction Details -->
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Name</span>
                <span class="col-span-2 text-black dark:text-white py-2">{{ transaction.user.name }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Email</span>
                <span class="col-span-2 text-black dark:text-white py-2">{{ transaction.user.email }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">ID Number</span>
                <span class="col-span-2 text-black dark:text-white py-2">{{ transaction.transaction_number }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Date & time</span>
                <span class="col-span-2 text-black dark:text-white py-2">{{ formatDateTime(transaction.created_at) }}</span>
            </div>
            <div v-if="transaction.type === 'Deposit'" class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Transaction Hash</span>
                <span class="col-span-2 text-black dark:text-white py-2 break-all">{{ transaction.txn_hash }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">To Wallet Address</span>
                <span class="col-span-2 text-black dark:text-white py-2 break-all">{{ transaction.to_wallet_address }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Amount</span>
                <span class="col-span-2 text-black dark:text-white py-2">$ {{ transaction.transaction_amount }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Withdrawal Fee</span>
                <span class="col-span-2 text-black dark:text-white py-2">$ {{ transaction.transaction_charges }}</span>
            </div>
            <div class="grid grid-cols-3 items-center gap-2">
                <span class="text-sm font-semibold dark:text-gray-400">Transaction Status</span>
                <span class="col-span-2 text-black dark:text-white py-2">{{ formatType(transaction.status) }}</span>
            </div>

            <!-- KYC Details
            <div v-if="transaction.transaction_type === 'Withdrawal'">
                <h2 class="py-3 text-xl font-semibold dark:text-white border-b dark:border-gray-700">KYC Details</h2>
                <div class="p-5 mt-3 bg-white overflow-hidden md:overflow-visible rounded-xl dark:bg-gray-700">
                    <div class="flex flex-col justify-center items-center">
                        <img :src="transaction.user.profile_photo_url ? transaction.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-16 h-16 rounded-full" alt="">
                        <div class="text-xl font-semibold dark:text-white mt-3">
                            {{ transaction.user.name }}
                        </div>
                        <div class="text-sm font-normal dark:text-gray-400">
                            {{ transaction.user.identity_number }}
                        </div>
                    </div>
                </div>
                <div class="py-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-2">
                            <div class="text-sm text-left w-full dark:text-white">
                                Proof of Identity (FRONT)
                            </div>
                            <div class="dark:bg-white rounded-lg w-full flex justify-center border-2 dark:border-white">
                                <img :src="transaction.user.front_identity" class="max-h-64 rounded-lg" alt="">
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="text-sm text-left w-full dark:text-white">
                                Proof of Identity (BACK)
                            </div>
                            <div class="dark:bg-white rounded-lg w-full flex justify-center border-2 dark:border-white">
                                <img :src="transaction.user.back_identity" class="max-h-64 rounded-lg" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            
        </div>

    </Modal>

</template>
