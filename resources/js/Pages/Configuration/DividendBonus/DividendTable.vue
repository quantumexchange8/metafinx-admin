<script setup>
import { RefreshIcon, SearchIcon, ArrowLeftIcon, ArrowRightIcon } from "@heroicons/vue/outline";
import {usePage} from "@inertiajs/vue3";
import {ref, watch, watchEffect} from "vue";
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

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const dividends = ref({data: []});
const search = ref('');
const date = ref('');
const currentPage = ref(1);
const isLoading = ref(false);
const refresh = ref(false);
const editModal = ref(false);
const dividendBonus = ref();

const { formatAmount, formatDate, formatDateTime, formatDatePicker } = transactionFormat();

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

watch(
    [search, date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    isLoading.value = true
    try {
        let url = `/configuration/getDividendBonus?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        dividends.value = response.data;
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

const openModal = (dividendBonusId, dividends) => {
    form.amount = dividends.amount;
    form.date = formatDatePicker(dividends.release_date);
    dividendBonus.value = dividends;
    editModal.value = true;
    // console.log(form.date);
}

const closeModal = () => {
    editModal.value = false
}

const form = useForm({
    amount: '',
    date: '',
});

function clearField() {
    form.amount = '';
    form.date = '';
}

const submit = () => {
    form.put(route('configuration.editDividendBonus', dividendBonus.value.id), {
        onSuccess: () => {
            form.reset();
            clearField();
            closeModal();
        },
    });
}

const isDividendReleased = (dividend) => {
    const releaseDate = new Date(dividend.release_date);
    const currentDate = new Date();

    return releaseDate <= currentDate;
}
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
                <vue-tailwind-datepicker
                    id="dividend_bonus_release_date"
                    placeholder="Select date"
                    as-single
                    :input-classes="form.errors.date ? 'py-2.5 border-error-500 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-error-500 dark:bg-gray-600 dark:text-white' :
                    'py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white'"
                    v-model="form.date"
                />
            </div>
        </div>
        <div class="relative overflow-x-auto sm:rounded-lg">
            <div v-if="isLoading" class="w-full flex justify-center my-8">
                <Loading />
            </div>
            <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 table-fixed">
                <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
                <tr>
                    <th scope="col" class="px-3 py-4">
                        Date
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Release Date
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Total Members Involved
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Amount
                    </th>
                    <th scope="col" class="px-3 py-4 text-center">
                        Action
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="dividends.data.length === 0">
                    <th colspan="4" class="py-4 text-lg text-center">
                        No History
                    </th>
                </tr>
                <tr
                    v-for="dividend in dividends.data"
                    class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    <td class="px-3 py-4">
                        {{ formatDateTime(dividend.created_at) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        {{ formatDateTime(dividend.release_date, false) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        {{ (dividend.total_Member) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        ${{ formatAmount(dividend.amount) }}
                    </td>
                    <td class="px-3 py-4 text-center">
                        <Tooltip content="Edit Dividend Bonus" placement="bottom">
                            <Button
                                type="button"
                                class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                                :class="{ 'opacity-50': isDividendReleased(dividend) }"
                                variant="action"
                                @click="openModal(dividend.id, dividend)"
                                pill
                                :disabled="isDividendReleased(dividend)"
                            >
                                <EditIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                <span class="sr-only">Edit Dividend Bonus</span>
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
                    :data="dividends"
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

    <Modal :show="editModal" title="Edit Dividend Bonus" @close="closeModal">
        <div v-if="dividendBonus">
            <form @submit.prevent="submit" class="flex flex-col gap-8 w-full">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white" for="dividend_bonus_amount" value="Dividend Bonus Amount" />
                        <div class="md:col-span-3">
                            <Input
                                id="dividend_bonus_amount"
                                type="number"
                                min="0"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                :class="form.errors.amount ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                                placeholder="$ 0.00"
                                autofocus
                                v-model="form.amount"
                            />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                        <Label class="text-sm dark:text-white" for="dividend_bonus_release_date" value="Dividend Bonus Release Date" />
                        <div class="md:col-span-3">
                            <vue-tailwind-datepicker
                                id="dividend_bonus_release_date"
                                placeholder="Select date"
                                :formatter="formatter"
                                as-single
                                :input-classes="form.errors.date ? 'py-2.5 border-error-500 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-error-500 dark:bg-gray-600 dark:text-white' :
                                'py-2.5 border-gray-400 w-full rounded-lg text-sm placeholder:text-base dark:placeholder:text-gray-400 focus:border-gray-400 focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-600 dark:text-white'"
                                v-model="form.date"
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
