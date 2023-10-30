<script setup>
import Button from "@/Components/Button.vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {Wallet} from "@/Components/Icons/outline.jsx";
import {transactionFormat} from "@/Composables/index.js";
import Combobox from "@/Components/Combobox.vue";

const props = defineProps({
    member_details: Object,
    wallet: Object
})
const emit = defineEmits(['update:memberDetailModal']);
const { formatAmount } = transactionFormat();
const users = ref();

function loadUsers(query, setOptions) {
    fetch('/member/getAllUsers?query=' + query + '&id=' + props.member_details.id)
        .then(response => response.json())
        .then(results => {
            setOptions(
                results.map(user => {
                    return {
                        value: user.id,
                        label: user.name,
                        img: user.profile_photo
                    }
                })
            )
        });
}

const form = useForm({
    user_id: props.member_details.id,
    wallet_id: props.wallet.id,
    amount: '',
    description: '',
    to_user_id: {},
})

const submit = () => {
    form.to_user_id = users.value;

    form.post(route('member.internal_transfer'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        },
    })
}

const closeModal = () => {
    emit('update:memberDetailModal', false);
}
</script>

<template>
    <form>
        <div class="flex justify-between items-center px-5 mb-8 shadow-md bg-gradient-to-bl from-pink-400 to-pink-600 rounded-[20px]">
            <div class="space-y-2">
                <div class="text-base font-semibold dark:text-white">
                    {{ wallet.name }}
                </div>
                <div class="text-xl font-semibold dark:text-white">
                    $ {{ formatAmount(wallet.balance) }}
                </div>
            </div>
                <Wallet class="w-24 h-24"/>
        </div>
        <div class="flex flex-col gap-4 md:gap-8 mt-3 mb-8">
            <div class="flex flex-col gap-1 md:grid md:grid-cols-3">
                <Label class="text-sm dark:text-white" for="amount" value="Amount" />
                <div class="md:col-span-2">
                    <Input
                        id="amount"
                        type="text"
                        placeholder="$ 0.00"
                        class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                        :class="form.errors.amount ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.amount"
                        autofocus
                    />
                    <InputError :message="form.errors.amount" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-3">
                <span class="text-sm dark:text-white">Transfer to member</span>
                <div class="md:col-span-2">
                    <Combobox
                        :load-options="loadUsers"
                        v-model="users"
                        :error="form.errors.to_user_id"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-3">
                <Label class="text-sm dark:text-white" for="description" value="Description" />
                <div class="md:col-span-2">
                    <Input
                    id="description"
                    type="text"
                    placeholder="Description"
                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                    :class="form.errors.description ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                    v-model="form.description"
                    />
                    <InputError :message="form.errors.description" class="mt-1 col-span-4" />
                </div>
            </div>
        </div>
        <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
            <Button
                type="button"
                variant="secondary"
                class="px-4 py-2 justify-center"
                @click="closeModal"
            >
                <span class="text-sm font-semibold">Cancel</span>
            </Button>
            <Button
                variant="primary"
                class="px-4 py-2 justify-center"
                :disabled="form.processing"
                @click.prevent="submit"
            >
                <span class="text-sm font-semibold">Confirm</span>
            </Button>
        </div>
    </form>
</template>
