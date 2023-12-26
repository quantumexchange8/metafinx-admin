<script setup>
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {transactionFormat} from "@/Composables/index.js";
import {ref} from "vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import WithdrawalFeeTable from "@/Pages/Configuration/WithdrawalFee/WithdrawalFeeTable.vue";

const props = defineProps({
    withdrawalFee: Object
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const { formatAmount } = transactionFormat();

const form = useForm({
    amount: '',
    date: '',
})

function clearField() {
    form.amount = '';
    form.date = '';
}

const submit = () => {
    form.post(route('configuration.editWithdrawalFee'), {
        onSuccess: () => {
            form.reset();
            clearField();
        },
    })
}

</script>

<template>
    <div class="flex flex-col gap-8 py-8 px-5 w-full ">
        <div class="flex flex-col gap-2 p-5 dark:bg-gray-700 items-center rounded-lg w-full md:w-4/5">
            <span class="text-xs text-gray-400">Withdrawal Fee</span>
            <h2>$ {{ withdrawalFee.amount }}</h2>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-8 w-full md:w-4/5">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="amount" value="Withdrawal Fee" />
                    <div class="md:col-span-3">
                        <Input
                            id="amount"
                            type="number"
                            min="0"
                            class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                            :class="form.errors.amount ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            placeholder="$ 0.00"
                            autofocus
                            v-model="form.amount"
                        />
                        <InputError :message="form.errors.amount" class="mt-2" />
                    </div>
                </div>
            </div>
            <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                <Button
                    variant="primary"
                    class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                    :disabled="form.processing"
                    @click.prevent="submit"
                >
                    <span class="text-sm font-semibold">Save</span>
                </Button>
            </div>
        </form>
        <WithdrawalFeeTable />
    </div>

</template>
