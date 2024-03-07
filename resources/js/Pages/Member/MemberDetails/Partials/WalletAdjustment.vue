<script setup>
import Button from "@/Components/Button.vue";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {Wallet} from "@/Components/Icons/outline.jsx";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    member_details: Object,
    wallet: Object
})
const emit = defineEmits(['update:memberDetailModal']);
const { formatAmount } = transactionFormat();

const form = useForm({
    user_id: props.member_details.id,
    wallet_id: props.wallet.id,
    amount: '',
    description: '',
})

const submit = () => {
    form.post(route('member.wallet_adjustment'), {
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
        <div class="flex justify-between items-center px-5 mb-8 shadow-md rounded-[20px]"
            :class="{
                        'bg-gradient-to-bl from-pink-400 to-pink-600': wallet.type === 'internal_wallet',
                        'bg-gradient-to-bl from-warning-300 to-warning-500': wallet.type === 'musd_wallet',
                    }"
        >
            <div class="space-y-2">
                <div class="text-base font-semibold dark:text-white">
                    {{ wallet.name }}
                </div>
                <div class="text-xl font-semibold dark:text-white">
                    $ {{ formatAmount(wallet.balance) }}
                </div>
            </div>
                <Wallet v-if="wallet.type === 'internal_wallet'" class="w-24 h-24"/>
                <img v-else-if="wallet.type === 'musd_wallet'" class="w-24 h-24" src="/assets/icon-no-color.png" alt="" >
        </div>
        <div class="flex flex-col gap-4 md:gap-8 mt-3 mb-8">
            <div class="flex flex-col gap-1 md:grid md:grid-cols-3">
                <Label class="text-sm dark:text-white" for="adjustment" value="Adjustment" />
                <div class="md:col-span-2">
                    <Input
                        id="adjustment"
                        type="number"
                        placeholder="+/-0.00"
                        class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                        :class="form.errors.amount ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.amount"
                        autofocus
                    />
                    <InputError :message="form.errors.amount" class="mt-1 col-span-4" />
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
