<script setup>
import Button from "@/Components/Button.vue";
import {ref, watchEffect} from "vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {DeleteIcon} from "@/Components/Icons/outline.jsx";
import Tooltip from "@/Components/Tooltip.vue";
import {PlusIcon} from "@heroicons/vue/outline";

const props = defineProps({
    settingRanks: Object
})

const activeComponent = ref('Member');
const rankDetail = ref('');
const referralEarning = ref('');

const setActiveComponent = (component) => {
    activeComponent.value = component.name;
    fetchRankDetails(component); // Assuming you have a function to fetch rank details
};

const fetchRankDetails = async (rank) => {
    await axios.get('/configuration/getSettingRank', { params: { rank_id: rank.id } })
        .then(response => {
            // Assuming your API response contains the rank details
            rankDetail.value = response.data.settingRank || { value: null };
            referralEarning.value = response.data.referralEarning || { value: null };
            form.dividend_earnings = response.data.dividendEarning.map((dividend) => dividend.value) || { value: null };
            form.affiliate_settings = response.data.affiliateSettings.map((affliateSetting) => affliateSetting.value) || { value: null };
        })
        .catch(error => {
            console.error('Error fetching rank details:', error);
            // Handle error if needed
        });
};

const form = useForm({
    id: "",
    self_deposit: "",
    valid_direct_referral: "",
    valid_affiliate_deposit: "",
    capping_per_line: "",
    referral_earnings: "",
    dividend_earnings: [],
    affiliate_settings: [],
})

const submitForm = () => {
    form.id = rankDetail.value.id;
    form.self_deposit = rankDetail.value.self_deposit;
    form.valid_direct_referral = rankDetail.value.valid_direct_referral;
    form.valid_affiliate_deposit = rankDetail.value.valid_affiliate_deposit;
    form.capping_per_line = rankDetail.value.capping_per_line;
    form.referral_earnings = referralEarning.value.value;

    form.post(route('configuration.affiliateSetting'), {
        onSuccess: () => {
            form.reset();
            fetchRankDetails(rankDetail.value);
        },
    });
}

const addDividend = () => {
    form.dividend_earnings.push('');
}

const addAffiliateSetting = () => {
    form.affiliate_settings.push('');
}

const removeDividend = (index) => {
    form.dividend_earnings.splice(index, 1)
}

const removeAffiliateSetting = (index) => {
    form.affiliate_settings.splice(index, 1)
}

watchEffect(() => {
    if (activeComponent.value === 'Member') {
        fetchRankDetails({ id: 1 });
    }
});
</script>

<template>
    <div class="py-6 pl-5 w-full">
        <form class="space-y-8" @submit.prevent="submitForm">
            <div class="inline-flex items-center justify-center rounded-md shadow-sm" role="group">
                <button
                    v-for="(rank, index) in settingRanks"
                    :key="index"
                    type="button"
                    class="px-4 py-2.5 text-sm font-semibold text-gray-900 border border-gray-200"
                    :class="{
                    'rounded-l-xl': index === 0,
                    'rounded-r-xl': index === settingRanks.length - 1,
                    'hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600': true,
                    'bg-transparent dark:bg-[#38425080] dark:text-white': activeComponent === rank.name
                }"
                    @click="setActiveComponent(rank)"
                >
                    {{ rank.name }}
                </button>
            </div>
            <div>
                <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full md:w-4/5 pb-3">
                    Requirements
                </h3>
                <div class="mt-5 flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="self_deposit" value="Valid Self Deposit" />
                        <div class="md:col-span-3">
                            <Input
                                id="self_deposit"
                                type="number"
                                min="0"
                                step=".01"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="$ 0.00"
                                v-model="rankDetail.self_deposit"
                                autofocus
                                :class="form.errors.self_deposit ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.self_deposit" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="valid_direct_referral" value="Direct Referral" />
                        <div class="md:col-span-3">
                            <Input
                                id="valid_direct_referral"
                                type="number"
                                min="0"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                v-model="rankDetail.valid_direct_referral"
                                :class="form.errors.valid_direct_referral ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.valid_direct_referral" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="valid_affiliate_deposit" value="Valid Affiliate Deposit" />
                        <div class="md:col-span-3">
                            <Input
                                id="valid_affiliate_deposit"
                                type="number"
                                min="0"
                                step=".01"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="$ 0.00"
                                v-model="rankDetail.valid_affiliate_deposit"
                                :class="form.errors.valid_affiliate_deposit ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.valid_affiliate_deposit" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="capping_per_line" value="Capping per Line" />
                        <div class="md:col-span-3">
                            <Input
                                id="capping_per_line"
                                type="number"
                                min="0"
                                step=".01"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="$ 0.00"
                                v-model="rankDetail.capping_per_line"
                                :class="form.errors.capping_per_line ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.capping_per_line" class="mt-1 col-span-4" />
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full md:w-4/5 pb-3">
                    Earnings
                </h3>
                <div class="mt-5 flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="referral_earnings" value="Referral Earnings" />
                        <div class="md:col-span-3">
                            <Input
                                id="referral_earnings"
                                type="number"
                                min="0"
                                step=".01"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="0%"
                                v-model="referralEarning.value"
                                :class="form.errors.referral_earnings ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.referral_earnings" class="mt-1 col-span-4" />
                        </div>
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-5 w-full md:w-4/5">
                    <div class="flex flex-col gap-4">
                        <Label class="text-sm dark:text-white" for="dividend_earnings" value="Dividend Earnings (%)" />
                        <div class="flex flex-col gap-4 col-span-3">
                            <div v-for="(dividend, index) in form.dividend_earnings" class="inline-flex items-center gap-3">
                                <Input
                                    :id="`dividend_earnings_${index}`"
                                    type="number"
                                    min="0"
                                    step=".01"
                                    :placeholder="`Level ${index+1}`"
                                    class="block w-full"
                                    :class="form.errors[`dividend_earnings.${index}`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    :aria-label="`Dividend Level ${index+1}`"
                                    v-model="form.dividend_earnings[index]"
                                />
                                <InputError :message="form.errors[`dividend_earnings.${index}`]" class="mt-2" />
                                <Tooltip content="Remove" placement="bottom">
                                    <Button
                                        type="button"
                                        pill
                                        class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                                        variant="danger"
                                        @click="removeDividend(index)"
                                    >
                                        <DeleteIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                        <span class="sr-only">Delete</span>
                                    </Button>
                                </Tooltip>
                            </div>
                            <Button
                                type="button"
                                variant="transparent"
                                class="pl-0 pt-0 inline-flex items-center max-w-xs"
                                @click="addDividend"
                            >
                                <PlusIcon
                                    class="w-5 h-5 mr-2"
                                />
                                Add Another
                            </Button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        <Label class="text-sm dark:text-white" for="affiliate_settings" value="Affiliate Earnings (%)" />
                        <div class="flex flex-col gap-4 col-span-3">
                            <div v-for="(affiliate, index) in form.affiliate_settings" class="inline-flex items-center gap-3">
                                <Input
                                    :id="`affiliate_settings_${index}`"
                                    type="number"
                                    min="0"
                                    step=".01"
                                    :placeholder="`L${index+1}`"
                                    class="block w-full"
                                    :class="form.errors[`affiliate_settings.${index}`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    :aria-label="`Affiliate Level ${index+1}`"
                                    v-model="form.affiliate_settings[index]"
                                />
                                <InputError :message="form.errors[`affiliate_settings.${index}`]" class="mt-2" />
                                <Tooltip content="Remove" placement="bottom">
                                    <Button
                                        type="button"
                                        pill
                                        class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                                        variant="danger"
                                        @click="removeAffiliateSetting(index)"
                                    >
                                        <DeleteIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                        <span class="sr-only">Delete</span>
                                    </Button>
                                </Tooltip>
                            </div>
                            <Button
                                type="button"
                                variant="transparent"
                                class="pl-0 pt-0 inline-flex items-center max-w-xs"
                                @click="addAffiliateSetting"
                            >
                                <PlusIcon
                                    class="w-5 h-5 mr-2"
                                />
                                Add Another
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex pt-8 gap-3 w-full md:w-4/5 justify-end border-t dark:border-gray-700">
                <Button
                    variant="primary"
                    class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                    :disabled="form.processing"
                    @click="submitForm"
                >
                    <span class="text-sm font-semibold">Save</span>
                </Button>
            </div>
        </form>
    </div>
</template>
