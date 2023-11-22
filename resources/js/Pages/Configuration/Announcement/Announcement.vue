<script setup>
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {ref, watch, watchEffect} from "vue";
import Button from "@/Components/Button.vue";
import AddAnnouncement from "@/Pages/Configuration/Announcement/AddAnnouncement.vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import {ArrowLeftIcon, ArrowRightIcon, SearchIcon} from "@heroicons/vue/outline";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import {AddIcon, CloudDownloadIcon, MemberDetailIcon} from "@/Components/Icons/outline.jsx";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import Loading from "@/Components/Loading.vue";
import Modal from "@/Components/Modal.vue";
import Tooltip from "@/Components/Tooltip.vue";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});

const announcements = ref({data: []});
const search = ref('');
const date = ref('');
const isLoading = ref(false);
const currentPage = ref(1);
const { formatDateTime } = transactionFormat();
const announcementModal = ref(false);
const announcementDetail = ref();

watch(
    [search, date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/configuration/getAnnouncement?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        announcements.value = response.data;
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

const openAnnouncementModal = (announcement) => {
    announcementModal.value = true
    announcementDetail.value = announcement
}

const closeModal = () => {
    announcementModal.value = false
}
</script>

<template>
    <div class="md:py-6 md:pl-5 w-full">
        <div class="flex justify-end">
            <AddAnnouncement />
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
                    <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
                        <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                        <tr>
                            <th scope="col" class="px-3 py-4">
                                Date
                            </th>
                            <th scope="col" class="px-3 py-4">
                                Subject
                            </th>
                            <th scope="col" class="px-3 py-4">
                                Action
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="announcements.data.length === 0">
                            <th colspan="3" class="py-4 text-lg text-center">
                                No History
                            </th>
                        </tr>
                        <tr
                            v-for="announcement in announcements.data"
                            class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
                        >
                            <td class="px-3 py-4">
                                {{ formatDateTime(announcement.created_at) }}
                            </td>
                            <td class="px-3 py-4">
                                {{ announcement.subject }}
                            </td>
                            <td class="px-3 py-4">
                                <Tooltip content="View Details" placement="bottom">
                                    <Button
                                        type="button"
                                        class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                                        variant="action"
                                        pill
                                        @click="openAnnouncementModal(announcement)"
                                    >
                                        <MemberDetailIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                        <span class="sr-only">View Details</span>
                                    </Button>
                                </Tooltip>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="flex justify-center mt-4" v-if="!isLoading">
                        <TailwindPagination
                            :item-classes=paginationClass
                            :active-classes=paginationActiveClass
                            :data="announcements"
                            :limit=2
                            @pagination-change-page="handlePageChange"
                        >
                            <template #prev-nav>
                                <span class="flex gap-2"><ArrowLeftIcon class="w-5 h-5" /> Previous</span>
                            </template>
                            <template #next-nav>
                                <span class="flex gap-2">Next <ArrowRightIcon class="w-5 h-5" /></span>
                            </template>
                        </TailwindPagination>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :show="announcementModal" title="Details" @close="closeModal">
        <div class="text-xs dark:text-gray-400">{{ formatDateTime(announcementDetail.created_at) }}</div>
        <div class="my-5" v-if="announcementDetail.image">
            <img class="rounded-lg w-full" :src="announcementDetail.image" alt="announcement image" />
        </div>
        <div class="my-5 dark:text-white">{{ announcementDetail.subject }}</div>
        <div class="dark:text-gray-300 text-sm prose leading-3" v-html="announcementDetail.details"></div>
    </Modal>
</template>
