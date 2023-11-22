<script setup>
import {ref, watch} from "vue";
import Button from "@/Components/Button.vue";
import Modal from "@/Components/Modal.vue";
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Input from "@/Components/Input.vue";
import {useForm} from "@inertiajs/vue3";
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

const addAnnouncementModal = ref(false);

const openAddAnnouncementModal = () => {
    addAnnouncementModal.value = true
}

const closeModal = () => {
    addAnnouncementModal.value = false
}

const previewSubject = ref('');
const previewDetails = ref('');
const previewImage = ref([]);

const form = useForm({
    receiver_type: 'everyone',
    receiver: null,
    subject: '',
    details: '',
    image: '',
})

watch(form, (watchFormSubject) => {
    previewSubject.value = watchFormSubject.subject;
    previewDetails.value = watchFormSubject.details;
});

const selectedReceivers = ref();

function loadUsers(query, setOptions) {
    fetch('/member/getAllUsers?query=' + query)
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

const submit = () => {
    form.receiver = selectedReceivers.value;
    form.post(route('configuration.addAnnouncement'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
            selectedReceivers.value = [];
        },
    })
}

const handleImageLoad = (response) => {
    return form.image = response
}

const handleImageRevert = (uniqueId, load, error) => {
    axios.post('/configuration/upload/image-revert', {
        image: form.image,
    });
    load();
    form.image = ''
}

</script>

<template>
    <Button
        type="button"
        variant="primary"
        @click="openAddAnnouncementModal"
    >
        Add New Announcement
    </Button>

    <Modal :show="addAnnouncementModal" title="Add new announcement" max-width="6xl" @close="closeModal">
        <div class="grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-5 w-full">
            <form
                @submit.prevent="submit"
                class="flex flex-col gap-5"
            >
                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="receiver_type" value="Trigger member" />
                    <div class="flex gap-x-12">
                        <div class="flex">
                            <input type="radio" name="receiver_type" v-model="form.receiver_type" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-pink-500 dark:bg-gray-800 dark:border-gray-400 dark:checked:bg-pink-500 dark:checked:border-pink-500 dark:focus:ring-offset-gray-800" id="hs-radio-group-1" value="everyone">
                            <label for="hs-radio-group-1" class="text-sm text-gray-300 ml-2 dark:text-white">Everyone</label>
                        </div>

                        <div class="flex">
                            <input type="radio" name="receiver_type" v-model="form.receiver_type" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-pink-500 dark:bg-gray-800 dark:border-gray-400 dark:checked:bg-pink-500 dark:checked:border-pink-500 dark:focus:ring-offset-gray-800" id="hs-radio-group-2" value="specific_member">
                            <label for="hs-radio-group-2" class="text-sm text-gray-500 ml-2 dark:text-white">Specific Member</label>
                        </div>
                    </div>
                    <InputError :message="form.errors.receiver_type" class="mt-2" />
                </div>
                <div class="space-y-2">
                    <Combobox
                        multiple
                        :disabled="form.receiver_type==='everyone'"
                        placeholder="Please Select"
                        :load-options="loadUsers"
                        v-model="selectedReceivers"
                        :error="form.errors.receiver"
                    />
                    <InputError :message="form.errors.receiver" class="mt-2" />
                </div>
                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="subject" value="Subject" />
                    <Input
                        id="subject"
                        type="text"
                        placeholder="Enter subject"
                        class="block w-full"
                        :class="form.errors.subject ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.subject"
                    />
                    <InputError :message="form.errors.subject" class="mt-2" />
                </div>
                <div class="space-y-2">
                    <Label class="text-sm dark:text-white" for="detail" value="Details" />
                    <TipTapEditor
                        v-model="form.details"
                    />
                    <InputError :message="form.errors.details" class="mt-2" />
                </div>
                <div class="space-y-2">
                    <Label class="dark:text-white" for="image" value="Attachment" />
                    <file-pond
                        name="image"
                        ref="pond"
                        v-bind:allow-multiple="false"
                        accepted-file-types="image/png, image/jpeg, image/jpg"
                        v-bind:server="{
                                url: '',
                                timeout: 7000,
                                process: {
                                    url: '/configuration/upload/tmp_img',
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $page.props.csrf_token
                                    },
                                    withCredentials: false,
                                    onload: handleImageLoad,
                                    onerror: () => {}
                                },
                                revert: handleImageRevert
                            }"
                        v-bind:files="previewImage"
                    />
                    <InputError :message="form.errors.image" class="mt-2" />
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
                    v-if="previewSubject === '' && previewDetails === '' && form.image === ''"
                    class="flex flex-col items-center justify-center mt-12"
                >
                    <img src="/assets/no_data.png" class="w-80" alt="no preview">
                    <div class="dark:text-gray-400 mt-4">No preview</div>
                </div>
                <div v-else class="pt-8">
                    <div v-if="form.image !== ''" class="py-5 flex justify-center w-full">
                        <img class="rounded-lg h-56 w-full object-cover" :src="`/storage/${form.image}`" alt="">
                    </div>
                    <h3 class="font-semibold text-sm dark:text-white">{{ previewSubject }}</h3>
                    <div class="mt-5 dark:text-gray-400 prose leading-3 text-xs" v-html="previewDetails"></div>
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
