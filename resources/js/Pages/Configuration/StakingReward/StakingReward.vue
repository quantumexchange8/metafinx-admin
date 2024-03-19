<script setup>
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import {useForm} from "@inertiajs/vue3";
import {transactionFormat} from "@/Composables/index.js";
import {ref, watch} from "vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import BaseListbox from "@/Components/BaseListbox.vue";
import StakingRewardTable from "@/Pages/Configuration/StakingReward/StakingRewardTable.vue";
import { usePermission } from "@/Composables/permissions";

const props = defineProps({
    stakingReward: Object,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const { hasRole, hasPermission } = usePermission();
const { formatAmount } = transactionFormat();
const month = ref('');
const percent = ref('');

const form = useForm({
    month: '',
    percent: '',
})

function clearField() {
    form.month = '';
    form.percent = '';
}

const months = [
    {value: 'January', label: 'January'},
    {value: 'February', label: 'February'},
    {value: 'March', label: 'March'},
    {value: 'April', label: 'April'},
    {value: 'May', label: 'May'},
    {value: 'June', label: 'June'},
    {value: 'July', label: 'July'},
    {value: 'August', label: 'August'},
    {value: 'September', label: 'September'},
    {value: 'October', label: 'October'},
    {value: 'November', label: 'November'},
    {value: 'December', label: 'December'},
];

const submit = () => {
    form.month = month.value;
    form.percent = percent.value;

    form.post(route('configuration.addStakingReward'), {
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
            <span class="text-xs text-gray-400">Staking Reward</span>
            <h2>{{ stakingReward.percent }} %</h2>
        </div>

        <!-- Staking Reward -->
        <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full md:w-4/5 pb-3" v-if="hasRole('admin') || hasPermission('AddNewStakingReward')">
            Staking Reward
        </h3>
        <form class="flex flex-col gap-8 w-full md:w-4/5" v-if="hasRole('admin') || hasPermission('AddNewStakingReward')">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="percent" value="Percent(%)" />
                    <div class="md:col-span-3">
                        <Input
                            id="percent"
                            type="number"
                            min="0"
                            class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                            :class="form.errors.percent ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            placeholder="0.00%"
                            autofocus
                            v-model="percent"
                        />
                        <InputError :message="form.errors.percent" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="month" value="Month" />
                    <div class="md:col-span-3">
                        <BaseListbox
                            v-model="month"
                            :options="months"
                            placeholder="Select Month"
                        />
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
        <StakingRewardTable />
    </div>
</template>
