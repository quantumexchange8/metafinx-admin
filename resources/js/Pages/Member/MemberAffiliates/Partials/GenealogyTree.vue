<script setup>
import {onMounted, onUnmounted, ref, watch, watchEffect} from 'vue';
import panzoom from '@panzoom/panzoom';
import Tooltip from "@/Components/Tooltip.vue";
import {LVL3Icon, Target02Icon, ZoomInIcon, ZoomOutIcon} from "@/Components/Icons/outline.jsx";
import Button from "@/Components/Button.vue";
import GenealogyChild from "@/Pages/Member/MemberAffiliates/Partials/GenealogyChild.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    RadioGroup,
    RadioGroupDescription,
    RadioGroupLabel,
    RadioGroupOption
} from '@headlessui/vue'
import {ArrowLeftIcon, ArrowRightIcon, ChevronDownIcon} from '@heroicons/vue/outline'
import axios from "axios";
import {TailwindPagination} from "laravel-vue-pagination";
import {transactionFormat} from "@/Composables/index.js";
import Modal from "@/Components/Modal.vue";
import Loading from "@/Components/Loading.vue";
import debounce from "lodash/debounce.js";

const binaryTree = ref({});
// Use refs to store functions
const handleZoomIn = ref(() => {});
const handleZoomOut = ref(() => {});
const handleRecenter = ref(() => {});
const isExpand = ref(false)
const root = ref({});
const { formatAmount, formatTime } = transactionFormat();

const props = defineProps({
    downline: Array,
    user: Object,
    search: String,
})

watch(
    () => props.search,
    debounce((searchValue) => {
        getResults(searchValue);
    }, 300)
);

const getResults = async (search = '') => {
    try {
        let url = `/member/getBinaryData/${props.user.id}`;

        if (search) {
            url += `?search=${search}`;
        }

        const response = await axios.get(url);
        binaryTree.value = response.data;
        root.value = response.data;
    } catch (error) {
        console.error(error);
    }
}

onMounted(() => {
    getResults();

    const element = binaryTree.value;

    if (element) {
        const pzInstance = panzoom(element, { canvas: true });
        const parent = element.parentElement;

        parent.addEventListener('wheel', pzInstance.zoomWithWheel);

        parent.addEventListener('wheel', function (event) {
            if (!event.shiftKey) return;
            pzInstance.zoomWithWheel(event);
        });

        // Define click functions here inside onMounted
        handleZoomIn.value = () => {
            pzInstance.zoomIn();
        };

        handleZoomOut.value = () => {
            pzInstance.zoomOut();
        };

        handleRecenter.value = () => {
            pzInstance.reset();
        };

    } else {
        console.error("Element with ID 'binary-tree' not found");
    }
});

watchEffect(() => {
    if (usePage().props.title !== null) {
        getResults();
    }
});

const handleExpand = () => {
    isExpand.value = !isExpand.value
}
const referralTableData = ref([]);
const isLoading = ref(false);
const currentPage = ref(1);
const search = ref('');
const getAffiliateResults = async (page = 1, search = '') => {
    isLoading.value = true;
    try {
        let url = `/member/getAvailableBinaryAffiliate/${props.user.id}?page=${page}`;

        if (search) {
            url += `&search=${search}`;
        }

        const response = await axios.get(url);
        referralTableData.value = response.data;
    } catch (error) {
        console.error(error);
        console.error("Error fetching data:", error.message);
    } finally {
        isLoading.value = false;
    }
};

getAffiliateResults();

</script>

<template>
    <div class="flex items-center self-stretch justify-between mt-8 mb-3">
        <div class="text-base font-normal text-gray-600 dark:text-gray-400">
            Note: Only add distributor that currently have no associated legs.
        </div>
        <div class="flex gap-3">
            <Tooltip content="Zoom In" placement="bottom">
                <Button
                    type="button"
                    class="flex justify-center w-8 h-8 relative focus:outline-none"
                    variant="gray"
                    @click="handleZoomIn"
                    pill
                >
                    <ZoomInIcon aria-hidden="true" class="w-4 h-4 absolute" />
                    <span class="sr-only">Zoom In</span>
                </Button>
            </Tooltip>
            <Tooltip content="Zoom Out" placement="bottom">
                <Button
                    type="button"
                    class="flex justify-center w-8 h-8 relative focus:outline-none"
                    variant="gray"
                    @click="handleZoomOut"
                    pill
                >
                    <ZoomOutIcon aria-hidden="true" class="w-4 h-4 absolute" />
                    <span class="sr-only">Zoom Out</span>
                </Button>
            </Tooltip>
            <Tooltip content="Recenter" placement="bottom">
                <Button
                    type="button"
                    class="flex justify-center w-8 h-8 relative focus:outline-none"
                    variant="gray"
                    @click="handleRecenter"
                    pill
                >
                    <Target02Icon aria-hidden="true" class="w-4 h-4 absolute" />
                    <span class="sr-only">Recenter</span>
                </Button>
            </Tooltip>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gray-900 bg-[radial-gradient(#333d4b_2px,transparent_1px)] [background-size:52px_52px] h-screen mb-12">
        <div ref="binaryTree" class="flex flex-col justify-center items-center">
            <div class="container mx-auto text-center pt-24">
                <div class="items-center justify-center flex">
                    <div class="text-center">
                        <div v-if="root.sponsor_name" class="flex flex-col justify-center items-center">
                            <div class="w-60 rounded-lg dark:bg-gray-700 flex gap-2 p-3 items-center z-20">
                                <img :src="root.sponsor_profile_photo ? root.sponsor_profile_photo : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                                <div class="flex flex-col gap-1">
                                    <div class="flex gap-2 items-center text-gray-900 dark:text-white">
                                        <div class="text-sm font-semibold">
                                            {{ root.sponsor_name }}
                                        </div>
                                        <div class="rounded-xl bg-warning-400 text-gray-800 font-normal text-xs px-2 text-center w-16">
                                           Sponsor
                                        </div>
                                    </div>
                                    <div class="text-left text-xs text-gray-600 dark:text-gray-400">
                                        {{ root.sponsor_email }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <GenealogyChild
                            :binaryTree="root"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
