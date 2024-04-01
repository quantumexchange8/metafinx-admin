<script setup>
import { RefreshIcon, SearchIcon, ArrowLeftIcon, ArrowRightIcon } from "@heroicons/vue/outline";
import {usePage} from "@inertiajs/vue3";
import {ref, watch, watchEffect, onMounted } from "vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import Button from "@/Components/Button.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import Loading from "@/Components/Loading.vue";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import {EditIcon} from "@/Components/Icons/outline.jsx";
import Tooltip from "@/Components/Tooltip.vue";
import Modal from "@/Components/Modal.vue";
import { useForm } from '@inertiajs/vue3'
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import BaseListbox from "@/Components/BaseListbox.vue";
import { usePermission } from "@/Composables/permissions";

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const { hasRole, hasPermission } = usePermission();
const stakingRewards = ref({data: []});
const search = ref('');
const month = ref('');
const date = ref('');
const currentPage = ref(1);
const isLoading = ref(false);
const refresh = ref(false);
const editModal = ref(false);
const stakingReward = ref();

const { formatDateTime } = transactionFormat();

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

watch(
    [search, month],
    debounce(([searchValue, monthValue]) => {
        getResults(1, searchValue, monthValue);
    }, 300)
);

const getResults = async (page = 1, search = '', month = '') => {
    isLoading.value = true
    try {
        let url = `/configuration/getStakingReward?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (month) {
            url += `&month=${month}`;
        }

        const response = await axios.get(url);
        stakingRewards.value = response.data;
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
    'bg-transparent border-0 dark:text-gray-400 dark:enabled:hover:text-white'
];

const paginationActiveClass = [
    'border dark:border-gray-600 dark:bg-gray-600 rounded-full text-[#FF9E23] dark:text-white'
];

watch(() => refresh.value, (newVal) => {
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        refresh.value = false;
    }
});

const openModal = (stakingRewards) => {
    form.percent = stakingRewards.percent;
    form.month = stakingRewards.month
    const releaseDate = new Date(stakingRewards.release_date);
    const year = releaseDate.getFullYear();
    const month = String(releaseDate.getMonth() + 1).padStart(2, '0');
    const day = String(releaseDate.getDate()).padStart(2, '0');
    form.date = `${year}-${month}-${day}`;
    stakingReward.value = stakingRewards;
    editModal.value = true;
    // console.log(form.date);
}

const closeModal = () => {
    editModal.value = false
}

const form = useForm({
    percent: '',
    month: '',
    date: '',
});

function clearField() {
    form.percent = '';
    form.month = '';
    form.date = '';
}

const submit = () => {
    form.put(route('configuration.editStakingReward', stakingReward.value.id), {
        onSuccess: () => {
            form.reset();
            clearField();
            closeModal();
        },
    });
}

const isStakingRewardReleased = (stakingReward) => {
    const releaseMonthIndex = months.findIndex(month => month.value === stakingReward.month);
    const currentMonthIndex = new Date().getMonth();

    return releaseMonthIndex <= currentMonthIndex;
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

// function generateDates(month) {
//     const index = months.findIndex(item => item.value === month);
//     if (index !== -1) {
//         const numDays = new Date(new Date().getFullYear(), index + 1, 0).getDate();
//         return Array.from({ length: numDays }, (_, i) => ({ value: `${i + 1}`, label: `${i + 1}` }));
//     }
// }

// watch(()=> form.month, (newValue) => {
//     form.date = '';
//     dates.value = generateDates(newValue);
// });

</script>

<template>
    <div class="p-5 flex flex-col gap-3 bg-white overflow-hidden md:overflow-visible rounded-xl shadow-md dark:bg-gray-700 w-full md:w-4/5">
        <div class="flex justify-between">
            <h4 class="font-semibold dark:text-white">History</h4>
            <RefreshIcon
                :class="{ 'animate-spin': isLoading }"
                class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white"
                aria-hidden="true"
                @click="refreshTable"
            />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <InputIconWrapper>
                    <template #icon>
                        <SearchIcon aria-hidden="true" class="w-5 h-5" />
                    </template>
                    <Input
                        withIcon
                        id="search"
                        type="text"
                        class="block w-full border border-gray-300 dark:border-gray-600"
                        placeholder="Search"
                        v-model="search"
                    />
                </InputIconWrapper>
            </div>
            <div class="md:col-span-2">
                <!-- <vue-tailwind-datepicker
                    id="coin_price_release_date"
                    placeholder="Select dates"
                    :formatter="formatter"
                    separator=" - "
                    v-model="date"
                    input-classes="py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white"
                /> -->
                <BaseListbox
                    id="month"
                    v-model="month"
                    :options="[ { value: '', label: 'All' }, ...months ]"
                    placeholder="Select Month"
                />
            </div>
        </div>
        <div class="relative overflow-x-auto sm:rounded-lg">
            <div v-if="isLoading" class="w-full flex justify-center my-8">
                <Loading />
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 table-fixed">
                    <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                    <tr>
                        <th scope="col" class="px-3 py-4">
                            Date
                        </th>
                        <th scope="col" class="px-3 py-4 text-center">
                            Release Date
                        </th>
                        <th scope="col" class="px-3 py-4 text-center">
                            Percent (%)
                        </th>
                        <th scope="col" class="px-3 py-4 text-center">
                            Month
                        </th>
                        <th scope="col" class="px-3 py-4 text-center">
                            Updated By
                        </th>
                        <th scope="col" class="px-3 py-4 text-center">
                            Action
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="stakingRewards.data.length === 0">
                        <th colspan="4" class="py-4 text-lg text-center">
                            No History
                        </th>
                    </tr>
                    <tr
                        v-for="stakingReward in stakingRewards.data"
                        class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <td class="px-3 py-4">
                            {{ formatDateTime(stakingReward.created_at) }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            {{ formatDateTime(stakingReward.release_date) }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            {{ stakingReward.percent }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            {{ stakingReward.month }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            {{ stakingReward.user.name }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            <Tooltip content="Edit&nbsp;Staking&nbsp;Reward" placement="bottom" class="relative" v-if="hasRole('admin') || hasPermission('EditStakingReward')">
                                <Button
                                    type="button"
                                    class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                                    :class="{ 'opacity-50': isStakingRewardReleased(stakingReward) }"
                                    variant="action"
                                    @click="openModal(stakingReward)"
                                    pill
                                    :disabled="isStakingRewardReleased(stakingReward)"
                                >
                                    <EditIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                    <span class="sr-only">Edit Staking Reward</span>
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
                    :data="stakingRewards"
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

    <Modal :show="editModal" title="Edit Staking Reward" @close="closeModal">
        <div v-if="stakingReward">
            <form @submit.prevent="submit" class="flex flex-col gap-8 w-full">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white" for="percent" value="Percent" />
                        <div class="md:col-span-3">
                            <Input
                                id="percent"
                                type="number"
                                min="0"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                :class="form.errors.percent ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                placeholder="0.00%"
                                autofocus
                                v-model="form.percent"
                            />
                            <InputError :message="form.errors.percent" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white" for="month" value="Month" />
                        <div class="md:col-span-3">
                            <BaseListbox
                                v-model="form.month"
                                :options="months"
                                placeholder="Select Month"
                            />
                            <InputError :message="form.errors.month" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white" for="date" value="Release Date" />
                        <div class="md:col-span-3">
                            <vue-tailwind-datepicker
                                id="date"
                                placeholder="Select dates"
                                :formatter="formatter"
                                separator=" - "
                                v-model="form.date"
                                as-single
                            />
                            <InputError :message="form.errors.date" class="mt-2" />
                        </div>
                    </div>

                </div>
                <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                    <Button
                    variant="secondary"
                    class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                    :disabled="form.processing"
                    @click.prevent="closeModal"
                    >
                        <span class="text-sm font-semibold">Cancel</span>
                    </Button>

                    <Button
                        variant="primary"
                        class="px-4 py-2 justify-center w-1/4 md:w-1/6"
                        :disabled="form.processing"
                        @click.prevent="submit"
                    >
                        <span class="text-sm font-semibold">Confirm</span>
                    </Button>
                </div>
            </form>
        </div>
    </Modal>

</template>
