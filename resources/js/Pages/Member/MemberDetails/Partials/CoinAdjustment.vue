<script setup>
import Button from "@/Components/Button.vue";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {XLCoinLogo} from "@/Components/Icons/outline.jsx";
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    member_details: Object,
    coin: Object,
    setting_coin: Object,
})
const emit = defineEmits(['update:memberDetailModal']);
const { formatAmount } = transactionFormat();

const form = useForm({
    user_id: props.member_details.id,
    coin_id: props.coin.id,
    setting_coin_id: props.coin.setting_coin_id,
    unit: '',
    description: '',
})

const submit = () => {
    form.post(route('member.coin_adjustment'), {
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
        <div class="flex justify-between items-center px-5 mb-8 shadow-md rounded-[20px]" style="background: linear-gradient(251deg, #00095E 2.14%, #0359E8 97.82%);">
            <div class="space-y-2">
                <div class="text-base font-semibold dark:text-white">
                    {{ coin.setting_coin.name }}
                </div>
                <div class="text-xl font-semibold dark:text-white">
                    {{ coin.unit }} XLC
                </div>
            </div>
                <XLCoinLogo class="w-24 h-24"/>
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
                        :class="form.errors.unit ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.unit"
                        autofocus
                    />
                    <InputError :message="form.errors.unit" class="mt-1 col-span-4" />
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
