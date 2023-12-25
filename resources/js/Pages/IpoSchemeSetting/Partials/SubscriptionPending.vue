<script setup>
import Input from "@/Components/Input.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {SearchIcon, RefreshIcon, ArrowLeftIcon, ArrowRightIcon} from "@heroicons/vue/outline";
import {ref, watch, watchEffect} from "vue";
import {alertTriangle, MemberDetailIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";
import Loading from "@/Components/Loading.vue";
import {TailwindPagination} from "laravel-vue-pagination";
import debounce from "lodash/debounce.js";
import {transactionFormat} from "@/Composables/index.js";
import Modal from "@/Components/Modal.vue";
import {useForm, usePage} from "@inertiajs/vue3";

const props = defineProps({
    refresh: Boolean,
    isLoading: Boolean,
    search: String,
    date: String,
    exportStatus: Boolean,
})

const formatter = ref({
    date: 'YYYY-MM-DD',
    month: 'MM'
});
const subscriptions = ref({data: []});
const search = ref('');
const date = ref('');
const historyLoading = ref(props.isLoading);
const historyRefresh = ref(props.refresh);
const currentPage = ref(1);
const { formatDateTime, formatAmount, formatType } = transactionFormat();
const subscriptionDetailModal = ref(false);
const approvalType = ref('');
const modalComponent = ref(null);
const subscriptionDetail = ref();
const emit = defineEmits(['update:loading', 'update:refresh', 'update:export']);

watch(
    [() => props.search, () => props.date],
    debounce(([searchValue, dateValue]) => {
        getResults(1, searchValue, dateValue);
    }, 300)
);

const getResults = async (page = 1, search = '', date = '') => {
    historyLoading.value = true
    try {
        let url = `/ipo_scheme/getPendingSubscription?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (date) {
            url += `&date=${date}`;
        }

        const response = await axios.get(url);
        subscriptions.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        historyLoading.value = false
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

watch(() => props.refresh, (newVal) => {
    historyRefresh.value = newVal;
    if (newVal) {
        // Call the getResults function when refresh is true
        getResults();
        emit('update:refresh', false);
    }
});

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

const form = useForm({
    id: ''
});


const openSubscriptionDetailModal = (subscription,  componentType) => {
    subscriptionDetailModal.value = true
    subscriptionDetail.value = subscription
    if (componentType === 'approve') {
        approvalType.value = 'approve'
        modalComponent.value = 'EBMI Approval';
    } else if (componentType === 'view') {
        modalComponent.value = 'Investment Details';
    }
}

const submitForm = () => {
    form.id = subscriptionDetail.value.id;

    form.post(route('ipo_scheme_setting.approveEbmi'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
}

const closeModal = () => {
    subscriptionDetailModal.value = false
}
</script>

<template>
    <div class="relative overflow-x-auto sm:rounded-lg">
        <div v-if="historyLoading" class="w-full flex justify-center my-8">
            <Loading />
        </div>
        <table v-else class="w-[650px] md:w-full text-sm text-left text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50 dark:bg-transparent dark:text-gray-400 border-b dark:border-gray-600">
            <tr>
                <th scope="col" class="px-3 py-2.5">
                    Name
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Date
                </th>
                <th scope="col" class="px-3 py-2.5">
                    ID Number
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Amount
                </th>
                <th scope="col" class="px-3 py-2.5">
                    Status
                </th>
                <th scope="col" class="px-3 py-2.5 text-center">
                    Action
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="subscriptions.data.length === 0">
                <th colspan="5" class="py-4 text-lg text-center">
                    No History
                </th>
            </tr>
            <tr
                v-for="subscription in subscriptions.data"
                class="bg-white dark:bg-transparent text-xs font-normal text-gray-900 dark:text-white border-b dark:border-gray-600 dark:hover:bg-gray-600"
            >
                <td class="px-3 py-2.5 inline-flex items-center gap-2">
                    <img :src="subscription.user.profile_photo_url ? subscription.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                    {{ subscription.user.name }}
                </td>
                <td class="px-3 py-2.5">
                    {{ formatDateTime(subscription.created_at, false) }}
                </td>
                <td class="px-3 py-2.5">
                    {{ subscription.subscription_id }}
                </td>
                <td class="px-3 py-2.5">
                    $ {{ formatAmount(subscription.amount) }}
                </td>
                <td class="px-3 py-2.5 uppercase">
                    {{ formatType(subscription.status) }}
                </td>
                <td class="px-3 py-2.5 uppercase">
                    <div class="flex justify-center gap-3">
                        <Button
                            variant="success"
                            size="sm"
                            class="text-xs"
                            @click="openSubscriptionDetailModal(subscription, 'approve')"
                        >
                            Approve
                        </Button>
                        <Button
                            type="button"
                            class="justify-center px-4 pt-2 mx-1 w-8 h-8 focus:outline-none"
                            variant="action"
                            pill
                            @click="openSubscriptionDetailModal(subscription, 'view')"
                        >
                            <MemberDetailIcon aria-hidden="true" class="w-5 h-5 absolute" />
                            <span class="sr-only">View Details</span>
                        </Button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flex justify-center mt-4" v-if="!historyLoading">
            <TailwindPagination
                :item-classes=paginationClass
                :active-classes=paginationActiveClass
                :data="subscriptions"
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

    <Modal :show="subscriptionDetailModal" :title="modalComponent" @close="closeModal" max-width="lg">
        <div v-if="modalComponent === 'EBMI Approval'">
            <div class="px-2 space-y-2">
                <alertTriangle />
                <h2 class="text-xl font-semibold dark:text-white pt-5">Approve EBMI</h2>
                <div class="text-sm font-normal dark:text-gray-400">
                    Do you want to approve EBMI?
                </div>
            </div>
            <div class="pt-5 px-2 grid grid-cols-2 gap-4">
                <Button type="button" variant="secondary" class="px-6 justify-center" @click="closeModal">
                    Cancel
                </Button>
                <Button class="px-6 justify-center" @click.prevent="submitForm">Confirm</Button>
            </div>
        </div>

        <div v-if="modalComponent === 'Investment Details'">
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Name</span>
                <span class="text-black dark:text-white py-2">{{ subscriptionDetail.user.name }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Email</span>
                <span class="text-black dark:text-white py-2">{{ subscriptionDetail.user.email }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">ID Number</span>
                <span class="text-black dark:text-white py-2">{{ subscriptionDetail.subscription_id }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Start Date</span>
                <span class="text-black dark:text-white py-2">{{ formatDateTime(subscriptionDetail.created_at, false) }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Valid Thru</span>
                <span class="text-black dark:text-white py-2">{{ formatDateTime(subscriptionDetail.expired_date, false) }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Investment Plan</span>
                <span class="text-black dark:text-white py-2">{{ subscriptionDetail.investment_plan.name.en }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Amount</span>
                <span class="text-black dark:text-white py-2">$ {{ subscriptionDetail.amount }}</span>
            </div>
            <div class="grid grid-cols-3 items-center">
                <span class="col-span-1 text-sm font-semibold dark:text-gray-400">Investment Status</span>
                <span class="text-black dark:text-white py-2">{{ formatType(subscriptionDetail.status) }}</span>
            </div>
        </div>
    </Modal>

</template>
