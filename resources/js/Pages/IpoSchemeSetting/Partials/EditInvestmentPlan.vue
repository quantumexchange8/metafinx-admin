<script setup>
import Button from "@/Components/Button.vue";
import {EditIcon, DeleteIcon, checkIcon, XIcon, CloudUploadIcon} from "@/Components/Icons/outline.jsx"
import {PlusIcon} from "@heroicons/vue/outline"
import {ref, watch} from "vue";
import Modal from "@/Components/Modal.vue";
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Tooltip from "@/Components/Tooltip.vue";

const props = defineProps({
    investmentPlan: Object,
});

const editInvestmentPlanModal = ref(false);

const openEditInvestmentPlan = () => {
    editInvestmentPlanModal.value = true
}

const closeModal = () => {
    editInvestmentPlanModal.value = false
}

const languages = [
    { value: 'en', name: 'English' },
    { value: 'cn', name: 'Chinese (Simplified)' },
    { value: 'tw', name: 'Chinese (Traditional)' },
]

const planNameEn = ref(props.investmentPlan.name.en);
const planNameCn = ref(props.investmentPlan.name.cn);
const planNameTw = ref(props.investmentPlan.name.tw);
const roiPercentage = ref(props.investmentPlan.roi_per_annum);

const selectedLogo = ref(props.investmentPlan.plan_logo ? props.investmentPlan.plan_logo : null);
const selectedLogoName = ref(null);

const form = useForm({
    id: props.investmentPlan.id,
    plan_name: {},
    investment_min_amount: props.investmentPlan.investment_min_amount,
    roi_per_annum: props.investmentPlan.roi_per_annum,
    investment_period: String(props.investmentPlan.investment_period),
    descriptions: props.investmentPlan.descriptions.map((description) => description.description),
    plan_logo: null,
    commision_multiplier: props.investmentPlan.commision_multiplier,
})

const onPlanLogoChanges = (event) => {
    const planLogoInput = event.target;
    const file = planLogoInput.files[0];

    if(file) {
        //Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedLogo.value = reader.result;
        };
        reader.readAsDataURL(file);
        selectedLogoName.value = file.name;
        form.plan_logo = file;
    } else {
        selectedLogo.value = null;
    }
}

const removePlanLogo = () => {
    selectedLogo.value = null;
}

const submit = () => {
    form.plan_name.en = planNameEn.value;
    form.plan_name.cn = planNameCn.value;
    form.plan_name.tw = planNameTw.value;

    form.post(route('ipo_scheme_setting.editInvestmentPlan'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

watch(form, (newFormValues) => {
    roiPercentage.value = newFormValues.roi_per_annum;
});

const addDescription = () => {
    form.descriptions.push({'en': '', 'cn': '', 'tw': ''});
}

const removeDescription = (index) => {
    form.descriptions.splice(index, 1)
}
</script>

<template>
    <Button
        type="button"
        variant="gray"
        class="w-full inline-flex justify-center items-center"
        @click="openEditInvestmentPlan"
    >
        <EditIcon class="w-5 h-5 mr-2"/>
        Edit
    </Button>

    <Modal :show="editInvestmentPlanModal" title="Edit Investment Plan" @close="closeModal">
        <form>
            <TabGroup>
                <TabList class="flex py-1">
                    <Tab
                        as="template"
                        v-for="lang in languages"
                        v-slot="{ selected }"
                    >
                        <button
                            :class="[
                              'px-3 py-2.5 text-sm font-semibold dark:text-gray-400',
                              'ring-white ring-offset-0 focus:outline-none focus:ring-0',
                              selected
                                ? 'dark:text-white border-b-2'
                                : 'border-b border-gray-400',
                           ]"
                        >
                            {{ lang.name }}
                        </button>
                    </Tab>
                </TabList>
                <TabPanels>
                    <TabPanel>
                        <div class="mt-8 space-y-2">
                            <div class="text-sm font-semibold dark:text-gray-400">
                                Preview
                            </div>
                            <div class="px-4 py-5 rounded-xl dark:bg-gray-700">
                                <div class="flex justify-center items-center gap-5">
                                    <div class="flex flex-col justify-center items-center gap-2 w-full">
                                        <img class="w-10 h-10 rounded-lg bg-white" src="/assets/icon.png" alt="Medium avatar">
                                        <div class="font-semibold dark:text-white">
                                            {{ planNameEn || 'Plan Name' }}
                                        </div>
                                        <div class="font-semibold text-2xl md:text-[32px] dark:text-white text-center">
                                            {{ roiPercentage || '0' }}% p.a.
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-3 justify-center w-full">
                                        <div v-for="descriptionEn in form.descriptions" class="inline-flex items-center gap-2 text-xs">
                                            <checkIcon />
                                            <div class="text-xs flex-1 dark:text-gray-300">{{ descriptionEn.en }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="plan_name" value="Plan name" />
                            <div class="flex flex-col w-full">
                                <Input
                                    id="plan_name"
                                    type="text"
                                    placeholder="Enter investment plan name"
                                    class="block w-full"
                                    :class="form.errors[`plan_name.en`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    v-model="planNameEn"
                                />
                                <InputError :message="form.errors[`plan_name.en`]" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="description" value="Description" />
                            <div class="flex flex-col gap-4 w-full">
                                <div v-for="(descriptionEn, index) in form.descriptions" class="inline-flex items-center gap-3">
                                    <div class="w-full">
                                        <Input
                                            :id="`descriptionEn_${index}`"
                                            type="text"
                                            :placeholder="`Description item ${index+1}`"
                                            class="block w-full"
                                            :class="form.errors[`descriptions.${index}.en`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                            :aria-label="`Description item ${index+1}`"
                                            v-model="form.descriptions[index].en"
                                        />
                                        <InputError :message="form.errors[`descriptions.${index}.en`]" class="mt-2" />
                                    </div>
                                    <Tooltip content="Remove" placement="bottom">
                                        <Button
                                            type="button"
                                            pill
                                            class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                                            variant="danger"
                                            @click="removeDescription(index)"
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
                                    @click="addDescription"
                                >
                                    <PlusIcon
                                        class="w-5 h-5 mr-2"
                                    />
                                    Add Another
                                </Button>
                            </div>
                        </div>
                    </TabPanel>
                    <TabPanel>
                        <div class="mt-8 space-y-2">
                            <div class="text-sm font-semibold dark:text-gray-400">
                                Preview
                            </div>
                            <div class="px-4 py-5 rounded-xl dark:bg-gray-700">
                                <div class="flex justify-center items-center gap-5">
                                    <div class="flex flex-col justify-center items-center gap-2 w-full">
                                        <img class="w-10 h-10 rounded-lg bg-white" src="/assets/icon.png" alt="Medium avatar">
                                        <div class="font-semibold dark:text-white">
                                            {{ planNameCn || 'Plan Name' }}
                                        </div>
                                        <div class="font-semibold text-2xl md:text-[32px] dark:text-white text-center">
                                            {{ roiPercentage }}% p.a.
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center gap-3 w-full">
                                        <div v-for="descriptionCn in form.descriptions" class="inline-flex items-center gap-2 text-xs">
                                            <checkIcon />
                                            <div class="text-xs flex-1 dark:text-gray-300">{{ descriptionCn.cn }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="plan_name" value="Plan name" />
                            <div class="flex flex-col w-full">
                                <Input
                                    id="plan_name"
                                    type="text"
                                    placeholder="Enter investment plan name"
                                    class="block w-full"
                                    :class="form.errors[`plan_name.cn`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    v-model="planNameCn"
                                />
                                <InputError :message="form.errors[`plan_name.cn`]" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="description_cn" value="Description" />
                            <div class="flex flex-col gap-4 w-full">
                                <div v-for="(descriptionCn, index) in form.descriptions" class="inline-flex items-center gap-3">
                                    <div class="w-full">
                                        <Input
                                            id="description_cn"
                                            type="text"
                                            :placeholder="`Description item ${index+1}`"
                                            class="block w-full"
                                            :class="form.errors[`descriptions.${index}.cn`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                            :aria-label="`Description item ${index+1}`"
                                            v-model="descriptionCn.cn"
                                        />
                                        <InputError :message="form.errors[`descriptions.${index}.cn`]" class="mt-2" />
                                    </div>
                                    <Tooltip content="Remove" placement="bottom">
                                        <Button
                                            type="button"
                                            pill
                                            class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                                            variant="danger"
                                            @click="removeDescription(index)"
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
                                    @click="addDescription"
                                >
                                    <PlusIcon
                                        class="w-5 h-5 mr-2"
                                    />
                                    Add Another
                                </Button>
                            </div>
                        </div>
                    </TabPanel>
                    <TabPanel>
                        <div class="mt-8 space-y-2">
                            <div class="text-sm font-semibold dark:text-gray-400">
                                Preview
                            </div>
                            <div class="px-4 py-5 rounded-xl dark:bg-gray-700">
                                <div class="flex justify-center items-center gap-5">
                                    <div class="flex flex-col justify-center items-center gap-2 w-full">
                                        <img class="w-10 h-10 rounded-lg bg-white" src="/assets/icon.png" alt="Medium avatar">
                                        <div class="font-semibold dark:text-white">
                                            {{ planNameTw || 'Plan Name' }}
                                        </div>
                                        <div class="font-semibold text-2xl md:text-[32px] dark:text-white text-center">
                                            {{ roiPercentage }}% p.a.
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center gap-3 w-full">
                                        <div v-for="descriptionTw in form.descriptions" class="inline-flex items-center gap-2 text-xs">
                                            <checkIcon />
                                            <div class="text-xs flex-1 dark:text-gray-300">{{ descriptionTw.tw }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="plan_name" value="Plan name" />
                            <div class="flex flex-col w-full">
                                <Input
                                    id="plan_name"
                                    type="text"
                                    placeholder="Enter investment plan name"
                                    class="block w-full"
                                    :class="form.errors[`plan_name.tw`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                    v-model="planNameTw"
                                />
                                <InputError :message="form.errors[`plan_name.tw`]" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                            <Label class="text-sm dark:text-white md:w-1/4" for="description" value="Description" />
                            <div class="flex flex-col gap-4 w-full">
                                <div v-for="(descriptionTw, index) in form.descriptions" class="inline-flex items-center gap-3">
                                    <div class="w-full">
                                        <Input
                                            id="description"
                                            type="text"
                                            :placeholder="`Description item ${index+1}`"
                                            class="block w-full"
                                            :class="form.errors[`descriptions.${index}.tw`] ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                            :aria-label="`Description item ${index+1}`"
                                            v-model="descriptionTw.tw"
                                        />
                                        <InputError :message="form.errors[`descriptions.${index}.tw`]" class="mt-2" />
                                    </div>
                                    <Tooltip content="Remove" placement="bottom">
                                        <Button
                                            type="button"
                                            pill
                                            class="justify-center px-4 pt-2 mx-1 rounded-full w-8 h-8 focus:outline-none"
                                            variant="danger"
                                            @click="removeDescription(index)"
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
                                    @click="addDescription"
                                >
                                    <PlusIcon
                                        class="w-5 h-5 mr-2"
                                    />
                                    Add Another
                                </Button>
                            </div>
                        </div>
                    </TabPanel>
                </TabPanels>
            </TabGroup>

            <div class="my-8 space-y-5">
                <div class="pb-3 border-b dark:border-gray-700">
                    <div class="font-semibold dark:text-white">Investment Details</div>
                </div>
                <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label class="text-sm dark:text-white md:w-1/4" for="investment_min_amount" value="Min. investment amount" />
                    <div class="flex flex-col w-full">
                        <Input
                            id="investment_min_amount"
                            min="0"
                            type="number"
                            placeholder="$ 0.00"
                            class="block w-full"
                            :class="form.errors.investment_min_amount ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            v-model="form.investment_min_amount"
                        />
                        <InputError :message="form.errors.investment_min_amount" class="mt-2" />
                    </div>
                </div>
                <div v-if="investmentPlan.type == 'standard'" class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label class="text-sm dark:text-white md:w-1/4" for="roi_per_annum" value="ROI per annum" />
                    <div class="flex flex-col w-full">
                        <Input
                            id="roi_per_annum"
                            min="0"
                            type="number"
                            placeholder="0%"
                            class="block w-full"
                            :class="form.errors.roi_per_annum ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            v-model="form.roi_per_annum"
                        />
                        <InputError :message="form.errors.roi_per_annum" class="mt-2" />
                    </div>
                </div>
                <div v-else class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label class="text-sm dark:text-white md:w-1/4" for="staking_reward" value="Staking Reward" />
                    <Input
                            id="staking_reward"
                            min="0"
                            type="number"
                            placeholder="0%"
                            class="block w-full"
                            :class="form.errors.commision_multiplier ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            v-model="form.commision_multiplier"
                        />
                        <InputError :message="form.errors.commision_multiplier" class="mt-2" />
                </div>
                <div v-if="investmentPlan.type == 'standard'" class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label class="text-sm dark:text-white md:w-1/4" for="investment_period" value="Investment period (months)" />
                    <div class="flex flex-col w-full">
                        <Input
                            id="investment_period"
                            min="0"
                            type="number"
                            placeholder="0"
                            class="block w-full"
                            :class="form.errors.investment_period ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            v-model="form.investment_period"
                        />
                        <InputError :message="form.errors.investment_period" class="mt-2" />
                    </div>
                </div>
                <div v-else class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label class="text-sm dark:text-white md:w-1/4" for="investment_period" value="Investment period (days)" />
                    <div class="flex flex-col w-full">
                        <Input
                            id="investment_period"
                            min="0"
                            type="number"
                            placeholder="0"
                            class="block w-full"
                            :class="form.errors.investment_period ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            v-model="form.investment_period"
                        />
                        <InputError :message="form.errors.investment_period" class="mt-2" />
                    </div>
                </div>

                <div class="flex gap-1 md:gap-4 mt-8 flex-col md:flex-row">
                    <Label for="plan_logo" class="text-sm dark:text-white md:w-1/4" value="Upload logo"/>
                    <div v-if="selectedLogo == null" class="flex gap-3 w-full">
                        <input
                            ref="planLogoInput"
                            id="plan_logo"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="onPlanLogoChanges"
                        />
                        <Button
                            type="button"
                            variant="secondary"
                            @click="$refs.planLogoInput.click()"
                            class="justify-center gap-2"
                        >
                            <CloudUploadIcon aria-hidden="true"/>
                            <span>Browse</span>
                        </Button>
                        <div>
                        </div>
                    </div>
                    <div
                        v-if="selectedLogo"
                        class="relative w-full py-2 pl-4 flex justify-between rounded-lg border focus:ring-1 focus:outline-none"
                        
                    >
                        <div class="inline-flex items-center gap-3">
                            <img :src="selectedLogo" alt="Selected Image" class="max-w-full h-9 object-contain rounded" />
                            <div v-for="name in investmentPlan.media" class="text-gray-light-900 dark:text-white">
                                {{ name.file_name }}
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="transparent"
                            pill
                            @click="removePlanLogo"
                        >
                            <XIcon />
                        </Button>
                    </div>
                </div>
            </div>

            <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

            <div class="pb-5 grid grid-cols-2 gap-4 w-full md:w-1/3 md:float-right">
                <Button variant="secondary" type="button" class="justify-center" @click.prevent="closeModal">
                    Cancel
                </Button>
                <Button class="justify-center" @click="submit" :disabled="form.processing">Confirm</Button>
            </div>
        </form>
    </Modal>

</template>
