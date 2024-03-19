<script setup>
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {ref, watch, watchEffect} from "vue";
import Button from "@/Components/Button.vue";
import AddTnCSetting from "@/Pages/Configuration/TnCSetting/AddTnCSetting.vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import {ArrowLeftIcon, ArrowRightIcon, SearchIcon} from "@heroicons/vue/outline";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {AddIcon, CloudDownloadIcon, MemberDetailIcon, EditIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import Loading from "@/Components/Loading.vue";
import Modal from "@/Components/Modal.vue";
import Tooltip from "@/Components/Tooltip.vue";
import TipTapEditor from "@/Components/TipTapEditor.vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import { usePermission } from "@/Composables/permissions";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const { hasRole, hasPermission } = usePermission();
const editTnCSettingModal = ref(false);
const editTnCSettingData = ref({});
const tncsettings = ref({data: []});
const search = ref('');
const date = ref('');
const isLoading = ref(false);
const currentPage = ref(1);
const { formatDateTime } = transactionFormat();
const tncSettingModal = ref(false);
const tncSettingDetail = ref();

const form = useForm({
    type: '',
    title: '',
    contents: '',
})

const previewTitle = ref('');
const previewContents = ref('');

watch(form, (watchFormSubject) => {
    previewTitle.value = watchFormSubject.title;
    previewContents.value = watchFormSubject.contents;
});

watch(
    [search, date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/configuration/getTnCSetting?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        tncsettings.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false
    }
}

getResults()

const handlePageChange = (newPage) => {
    if (newPage >= 1) {
        currentPage.value = newPage;
        const dateRange = date.value.split(' - ');
        getResults(currentPage.value, dateRange, search.value);
    }
};

watchEffect(() => {
    if (usePage().props.title !== null) {
        getResults();
    }
});

const paginationClass = [
    'bg-transparent border-0 dark:text-gray-400'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

const openTnCSettingModal = (tncsetting) => {
    tncSettingModal.value = true
    tncSettingDetail.value = tncsetting
}

const closeModal = () => {
    tncSettingModal.value = false;
    editTnCSettingModal.value = false;

}

const openEditModal = (tncsetting) => {
    editTnCSettingData.value = tncsetting;
    editTnCSettingModal.value = true;
    form.type = editTnCSettingData.value.type
    form.title = editTnCSettingData.value.title
    form.contents = editTnCSettingData.value.contents
};

const submit = () => {
    form.put(route('configuration.editTnCSetting', editTnCSettingData.value.id), {
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
    <div class="md:py-6 md:pl-5 w-full">
        <div class="flex justify-end" v-if="hasRole('admin') || hasPermission('AddNewTerms')">
            <AddTnCSetting />
        </div>
        <div class="py-4">
            <div class="p-5 rounded-xl dark:bg-gray-700">
                <h4 class="font-semibold dark:text-white">History</h4>
                <form>
                    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="w-full">
                            <InputIconWrapper class="md:col-span-2">
                                <template #icon>
                                    <SearchIcon aria-hidden="true" class="w-5 h-5" />
                                </template>
                                <Input withIcon id="search" type="text" class="block w-full border border-transparent" placeholder="Search" v-model="search" />
                            </InputIconWrapper>
                        </div>
                        <div class="md:w-2/3">
                            <vue-tailwind-datepicker
                                placeholder="Select dates"
                                :formatter="formatter"
                                separator=" - "
                                v-model="date"
                                input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                            />
                        </div>
                    </div>
                </form>
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <div v-if="isLoading" class="w-full flex justify-center my-8">
                        <Loading />
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
                            <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                            <tr>
                                <th Date="col" class="px-3 py-4">
                                    Date
                                </th>
                                <th scope="col" class="px-3 py-4">
                                    Title
                                </th>
                                <th scope="col" class="px-3 py-4">
                                    Updated By
                                </th>
                                <th scope="col" class="px-3 py-4">
                                    Preview
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="tncsettings.data.length === 0">
                                <th colspan="3" class="py-4 text-lg text-center">
                                    No History
                                </th>
                            </tr>
                            <tr
                                v-for="tncsetting in tncsettings.data"
                                class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
                            >
                                <td class="px-3 py-4">
                                    {{ formatDateTime(tncsetting.created_at) }}
                                </td>
                                <td class="px-3 py-4">
                                    {{ tncsetting.title }}
                                </td>
                                <td class="px-3 py-4">
                                    {{ tncsetting.user.name }}
                                </td>
                                <td class="px-3 py-4">
                                    <Tooltip content="View&nbsp;Details" placement="bottom" class="relative" v-if="hasRole('admin') || hasPermission('ViewTermsDetail')">
                                        <Button
                                            type="button"
                                            class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                                            variant="action"
                                            pill
                                            @click="openTnCSettingModal(tncsetting)"
                                        >
                                            <MemberDetailIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                            <span class="sr-only">View Details</span>
                                        </Button>
                                    </Tooltip>
                                    <Tooltip content="Edit&nbsp;T&C&nbsp;Setting" placement="bottom" class="relative" v-if="hasRole('admin') || hasPermission('EditTerms')">
                                        <Button
                                            type="button"
                                            class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                                            variant="action"
                                            pill
                                            @click="openEditModal(tncsetting)"
                                        >
                                            <EditIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                            <span class="sr-only">Edit T&C Setting</span>
                                        </Button>
                                    </Tooltip>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-center mt-4" v-if="!isLoading">
                        <TailwindPagination
                            :item-classes=paginationClass
                            :active-classes=paginationActiveClass
                            :data="tncsettings"
                            :limit=2
                            @pagination-change-page="handlePageChange"
                        >
                        <template #prev-nav>
                            <span class="flex gap-2"><ArrowLeftIcon class="w-5 h-5" /> <span class="hidden sm:flex">Previous</span></span>
                        </template>
                        <template #next-nav>
                            <span class="flex gap-2"><span class="hidden sm:flex">Next</span><ArrowRightIcon class="w-5 h-5" /></span>
                        </template>
                        </TailwindPagination>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :show="tncSettingModal" title="Details" @close="closeModal">
        <div class="text-xs dark:text-gray-400">{{ formatDateTime(tncSettingDetail.created_at) }}</div>
        <div class="my-5 dark:text-white">{{ tncSettingDetail.title }}</div>
        <div class="dark:text-gray-300 text-sm prose max-w-none leading-3" v-html="tncSettingDetail.contents"></div>
    </Modal>
    <Modal :show="editTnCSettingModal" title="Edit T&C Setting" max-width="6xl" @close="closeModal">
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
