<script setup>
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import { useForm } from "@inertiajs/vue3";
import { transactionFormat } from "@/Composables/index.js";
import { ref } from "vue";
import MasterSettingTable from "@/Pages/Configuration/MasterSetting/MasterSettingTable.vue";

const props = defineProps({
    masterSetting: Object
});

const forms = ref({});

function clearField(setting) {
    forms.value[setting.slug].value = '';
}

function submit(setting) {
    forms.value[setting.slug].post(route('configuration.editMasterSetting'), {
        onSuccess: () => {
            forms.value[setting.slug].reset();
            clearField(setting);
        },
    });
}

props.masterSetting.forEach(setting => {
    forms.value[setting.slug] = useForm({
        name: setting.name,
        slug: setting.slug,
        value: '',
    });
});

const getPlaceholder = (slug) => {
    if (slug === 'withdrawal-fee') {
        return '$ 0.00';
    } else if (slug === 'gas-fee') {
        return '0.00 %';
    }
    else if (slug === 'stacking-fee') {
        return '0.00 %';
    }
    return 'Placeholder Text';
};

</script>

<template>
    <div class="flex flex-col gap-8 py-8 px-5 w-full ">
        <div v-for="(setting) in masterSetting" :key="setting.slug">
            <div class="flex flex-col gap-2 p-5 dark:bg-gray-700 items-center rounded-lg w-full md:w-4/5">
                <span class="text-xs text-gray-400">{{ setting.name }}</span>
                <h2>
                    <span v-if="setting.slug === 'withdrawal-fee'">$</span>
                    {{ setting.value }}
                    <span v-if="setting.slug === 'gas-fee' || setting.slug === 'stacking-fee' || setting.slug === 'deposit-fee'">%</span>
                </h2>
            </div>

            <form @submit.prevent="() => submit(setting)" class="flex flex-col gap-8 w-full mt-5 md:w-4/5">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white self-center" for="value" :value="setting.name" />
                        <div class="md:col-span-3">
                            <Input id="value" type="number" min="0"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                :class="{
                                    'border border-error-500 dark:border-error-500': forms[setting.slug].errors.value,
                                    'border border-gray-400 dark:border-gray-600': !forms[setting.slug].errors.value
                                }" :placeholder="getPlaceholder(setting.slug)" v-model="forms[setting.slug].value" />
                            <InputError :message="forms[setting.slug].errors.value" class="mt-2" />
                        </div>
                    </div>
                </div>
                <div class="flex pb-4 gap-3 justify-end border-b dark:border-gray-700">
                    <Button variant="primary" class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                        :disabled="forms[setting.slug].processing" @click.prevent="() => submit(setting)">
                        <span class="text-sm font-semibold">Save</span>
                    </Button>
                </div>
            </form>
        </div>
        <MasterSettingTable />
    </div>
</template>
