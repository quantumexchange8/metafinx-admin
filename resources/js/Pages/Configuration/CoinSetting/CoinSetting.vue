<script setup>
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import {useForm} from "@inertiajs/vue3";
import {transactionFormat} from "@/Composables/index.js";
import {ref, watch} from "vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import WithdrawalFeeTable from "@/Pages/Configuration/WithdrawalFee/WithdrawalFeeTable.vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import Combobox from "@/Components/Combobox.vue";
import CoinSettingTable from "@/Pages/Configuration/CoinSetting/CoinSettingTable.vue";

const props = defineProps({
    settingCoin: Object,
    totalCoinSupply: Number,
    conversionRate: Object,
    coinMarketTime: Object,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const meridiem = [
    { label: 'AM', value: 'AM' },
    { label: 'PM', value: 'PM' },
]

const { formatAmount } = transactionFormat();
const frequency = ref(props.coinMarketTime.frequency);
const isFormDirty = ref(false);
const isMarketTimeFormDirty = ref(false);
const price = ref('');
const date = ref('');
const conversion_rate = ref(props.conversionRate.price);
const open_time_hr = ref(props.coinMarketTime.open_time_hr);
const open_time_min = ref(props.coinMarketTime.open_time_min);
const open_time_meridiem = ref(props.coinMarketTime.open_time_meridiem);
const close_time_hr = ref(props.coinMarketTime.close_time_hr);
const close_time_min = ref(props.coinMarketTime.close_time_min);
const close_time_meridiem = ref(props.coinMarketTime.close_time_meridiem);

const form = useForm({
    setting_coin_id: props.settingCoin.id,
    price: '',
    date: '',
    conversion_rate: '',
})

const marketTimeForm = useForm({
    market_time_id: props.coinMarketTime.id,
    open_time_hr: props.coinMarketTime.open_time_hr,
    open_time_min: props.coinMarketTime.open_time_min,
    open_time_meridiem: props.coinMarketTime.open_time_meridiem,
    close_time_hr: props.coinMarketTime.close_time_hr,
    close_time_min: props.coinMarketTime.close_time_min,
    close_time_meridiem: props.coinMarketTime.close_time_meridiem,
    frequency: {},
})

function clearField() {
    form.amount = '';
    form.date = '';
}

function loadDays(query, setOptions) {
    fetch('/configuration/getDays')
        .then(response => response.json())
        .then(results => {
            setOptions(
                results.map(day => {
                    return {
                        value: day.value,
                        label: day.label,
                    }
                })
            )
        });
}

watch([price, date, conversion_rate], () => {
    isFormDirty.value = true;
});

watch([open_time_hr, open_time_min, open_time_meridiem, close_time_hr, close_time_min, close_time_meridiem, frequency], () => {
    isMarketTimeFormDirty.value = true;
});

const submit = () => {
    form.price = price.value;
    form.date = date.value;
    form.conversion_rate = conversion_rate.value;

    form.post(route('configuration.updateCoinPrice'), {
        onSuccess: () => {
            form.reset();
            clearField();
            isFormDirty.value = false
        },
    })
}

const submitMarketTime = () => {
    marketTimeForm.open_time_hr = open_time_hr.value
    marketTimeForm.open_time_min = open_time_min.value
    marketTimeForm.open_time_meridiem = open_time_meridiem.value
    marketTimeForm.close_time_hr = close_time_hr.value
    marketTimeForm.close_time_min = close_time_min.value
    marketTimeForm.close_time_meridiem = close_time_meridiem.value
    marketTimeForm.frequency = frequency.value

    marketTimeForm.post(route('configuration.updateCoinMarketTime'), {
        onSuccess: () => {
            isMarketTimeFormDirty.value = false
        },
    })
}

</script>

<template>
    <div class="flex flex-col gap-8 py-8 px-5 w-full ">
        <div class="flex flex-col gap-2 p-5 dark:bg-gray-700 items-center rounded-lg w-full md:w-4/5">
            <span class="text-xs text-gray-400">Total Supply</span>
            <h2>{{ totalCoinSupply }}</h2>
        </div>

        <!-- Coin Details -->
        <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full md:w-4/5 pb-3">
            Coin Details
        </h3>
        <form class="flex flex-col gap-8 w-full md:w-4/5">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="price" value="MXT Coin Price" />
                    <div class="md:col-span-3">
                        <Input
                            id="price"
                            type="number"
                            min="0"
                            class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                            :class="form.errors.price ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            placeholder="MYR 0.00"
                            autofocus
                            v-model="price"
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="date" value="Price Date" />
                    <div class="md:col-span-3">
                        <vue-tailwind-datepicker
                            placeholder="Select dates"
                            :formatter="formatter"
                            separator=" - "
                            as-single
                            v-model="date"
                            :input-classes="`py-2.5 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:bg-gray-600 dark:text-white border ${form.errors.date ? 'border-error-500 dark:border-error-500' : 'border-gray-400 dark:border-gray-600'}`"
                        />
                        <InputError :message="form.errors.date" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="conversion_rate" value="Conversion Rate" />
                    <div class="md:col-span-3">
                        <Input
                            id="conversion_rate"
                            type="number"
                            min="0"
                            step=".01"
                            class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                            :class="form.errors.conversion_rate ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            placeholder="MYR 0.00"
                            v-model="conversion_rate"
                        />
                        <InputError :message="form.errors.conversion_rate" class="mt-2" />
                    </div>
                </div>
            </div>
            <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                <Button
                    variant="primary"
                    class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                    :disabled="form.processing || !isFormDirty"
                    @click.prevent="submit"
                >
                    <span class="text-sm font-semibold">Save</span>
                </Button>
            </div>
        </form>

        <!-- Market Time -->
        <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full md:w-4/5 pb-3">
            Market Time
        </h3>
        <form class="flex flex-col gap-8 w-full md:w-4/5">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="open_time" value="Open Time" />
                    <div class="md:col-span-3">
                        <div class="flex items-center gap-3">
                            <div class="w-full">
                                <Input
                                    id="open_time_hr"
                                    type="number"
                                    min="1"
                                    max="12"
                                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                    :class="marketTimeForm.errors.open_time_hr ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    placeholder="Hr"
                                    v-model="open_time_hr"
                                />
                            </div>
                            <div class="text-[28px] font-semibold text-gray-400 dark:text-white">:</div>
                            <div class="w-full">
                                <Input
                                    id="open_time_min"
                                    type="number"
                                    min="1"
                                    max="59"
                                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                    :class="marketTimeForm.errors.open_time_min ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    placeholder="Min"
                                    v-model="open_time_min"
                                />
                            </div>
                            <div class="w-full">
                                <BaseListbox
                                    :options="meridiem"
                                    v-model="open_time_meridiem"
                                />
                            </div>
                        </div>
                        <InputError :message="marketTimeForm.errors.open_time_hr" class="mt-2" />
                        <InputError :message="marketTimeForm.errors.open_time_min" class="mt-2" />
                        <InputError :message="marketTimeForm.errors.open_time_meridiem" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="close_time" value="Close Time" />
                    <div class="md:col-span-3">
                        <div class="flex items-center gap-3">
                            <div class="w-full">
                                <Input
                                    id="close_time_hr"
                                    type="number"
                                    min="1"
                                    max="12"
                                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                    :class="marketTimeForm.errors.close_time_hr ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    placeholder="Hr"
                                    v-model="close_time_hr"
                                />
                            </div>
                            <div class="text-[28px] font-semibold text-gray-400 dark:text-white">:</div>
                            <div class="w-full">
                                <Input
                                    id="close_time_min"
                                    type="number"
                                    min="1"
                                    max="59"
                                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                    :class="marketTimeForm.errors.close_time_min ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    placeholder="Min"
                                    v-model="close_time_min"
                                />
                            </div>
                            <div class="w-full">
                                <BaseListbox
                                    :options="meridiem"
                                    v-model="close_time_meridiem"
                                />
                            </div>
                        </div>
                        <InputError :message="marketTimeForm.errors.close_time_hr" class="mt-2" />
                        <InputError :message="marketTimeForm.errors.close_time_min" class="mt-2" />
                        <InputError :message="marketTimeForm.errors.close_time_meridiem" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                    <Label class="text-sm dark:text-white" for="date" value="Frequency" />
                    <div class="md:col-span-3">
                        <Combobox
                            multiple
                            :load-options="loadDays"
                            v-model="frequency"
                            :error="marketTimeForm.errors.frequency"
                        />
                        <InputError :message="marketTimeForm.errors.frequency" class="mt-2" />
                    </div>
                </div>
            </div>
            <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                <Button
                    variant="primary"
                    class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                    :disabled="form.processing || !isMarketTimeFormDirty"
                    @click.prevent="submitMarketTime"
                >
                    <span class="text-sm font-semibold">Save</span>
                </Button>
            </div>
        </form>
        <CoinSettingTable />
    </div>

</template>
