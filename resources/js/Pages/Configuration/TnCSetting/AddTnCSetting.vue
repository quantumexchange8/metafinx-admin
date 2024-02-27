<script setup>
import {ref, watch} from "vue";
import Button from "@/Components/Button.vue";
import Modal from "@/Components/Modal.vue";
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Input from "@/Components/Input.vue";
import {useForm,usePage} from "@inertiajs/vue3";
import BaseListbox from "@/Components/BaseListbox.vue";
import Combobox from "@/Components/Combobox.vue";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import vueFilePond from "vue-filepond";
import "filepond/dist/filepond.min.css";

// Import FilePond plugins
// Please note that you need to install these plugins separately

// Import image preview plugin styles
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";

// Import the plugin code
import FilePondPluginFilePoster from 'filepond-plugin-file-poster';

// Import the plugin styles
import 'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css';

// Import image preview and file type validation plugins
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";

const FilePond = vueFilePond(
    FilePondPluginFilePoster,
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
)

const addTnCSettingModal = ref(false);
const user = usePage().props.auth.user.id;

const openAddTnCSettingModal = () => {
    addTnCSettingModal.value = true
}

const closeModal = () => {
    addTnCSettingModal.value = false
}

const previewTitle = ref('');
const previewContents = ref('');

const form = useForm({
    type: '',
    title: '',
    contents: '',
    user_id: user,
})

watch(form, (watchFormSubject) => {
    previewTitle.value = watchFormSubject.title;
    previewContents.value = watchFormSubject.contents;
});

const submit = () => {
    form.post(route('configuration.addTnCSetting'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    })
}

const tncSetting = [
  { value: 'staking_subscription', label: "Staking Subscription" },
  { value: 'staking_learn_more', label: "Staking Learn More" },
  { value: 'standard_subscription', label: "Standard Subscription" },
  { value: 'standard_learn_more', label: "Standard Learn More" },
  { value: 'buy_coin', label: "Buy Coin" },
  { value: 'deposit', label: "Deposit" },
  { value: 'swap', label: "Swap Coin" },
  { value: 'withdrawal', label: "Withdrawal" },
  { value: 'sign_up', label: "Sign Up" },

];

</script>

<template>
    <Button
        type="button"
        variant="primary"
        @click="openAddTnCSettingModal"
    >
        Add New T&C Setting
    </Button>

    <Modal :show="addTnCSettingModal" title="Add new T&C Setting" max-width="6xl" @close="closeModal">
        <div class="grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-5 w-full">
            <form
                @submit.prevent="submit"
                class="flex flex-col gap-5"
            >
                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="type" value="Type" />
                    <div class="md:col-span-3">
                    <BaseListbox
                        v-model="form.type"
                        :options=tncSetting
                        :error="form.errors.type"
                    />
                </div>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="title" value="Title" />
                    <Input
                        id="title"
                        type="text"
                        placeholder="Enter title"
                        class="block w-full"
                        :class="form.errors.title ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.title"
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>
                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="content" value="Contents" />
                    <TipTapEditor
                        v-model="form.contents"
                    />
                    <InputError :message="form.errors.contents" class="mt-2" />
                </div>
                <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                    <Button variant="secondary" class="px-4 py-2 justify-center" @click="closeModal">
                        <span class="text-sm font-semibold">Cancel</span>
                    </Button>
                    <Button variant="primary" class="px-4 py-2 justify-center" :disabled="form.processing">
                        <span class="text-sm font-semibold">Confirm</span>
                    </Button>
                </div>
            </form>
            <div>
                <h3 class="font-semibold dark:text-white text-base pb-3 border-b dark:border-gray-700">Preview</h3>
                <div
                    v-if="previewTitle === '' && previewContents === ''"
                    class="flex flex-col items-center justify-center mt-12"
                >
                    <img src="/assets/no_data.png" class="w-80" alt="no preview">
                    <div class="dark:text-gray-400 mt-4">No preview</div>
                </div>
                <div v-else class="pt-8">
                    <h3 class="font-semibold text-sm dark:text-white">{{ previewTitle }}</h3>
                    <div class="mt-5 dark:text-gray-400 prose max-w-none leading-3 text-xs" v-html="previewContents"></div>
                </div>
            </div>
        </div>
    </Modal>

</template>

<style>
.filepond--panel-root {
    background-color: #4D5761;
}

.filepond--drop-label {
    color: #9DA4AE;
}

.filepond--label-action {
    color: white;
    text-decoration-color: white;
}

[data-filepond-item-state*='error'] .filepond--item-panel,
[data-filepond-item-state*='invalid'] .filepond--item-panel {
    background-color: #F04438;
}

[data-filepond-item-state='processing-complete'] .filepond--item-panel {
    background-color: #039855;
}
</style>
