<script setup>
import AffiliateChild from "@/Pages/Member/MemberAffiliates/Partials/AffiliateChild.vue";
import {ref, watch} from "vue";
import debounce from "lodash/debounce.js";
import {Rank1Icon, LVL1Icon, LVL2Icon, LVL3Icon, LVL4Icon} from "@/Components/Icons/outline.jsx"
import Input from "@/Components/Input.vue";
import {SearchIcon} from "@heroicons/vue/outline";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";

const props = defineProps({
    user: Object,
    search: String
})

const search = ref(props.search);
let root = ref({});
const isLoading = ref(false);

watch(
    () => props.search,
    debounce((searchValue) => {
        isLoading.value = true;
        getResults(searchValue);
    }, 300)
);

const getResults = async (search = '') => {
    isLoading.value = true;
    try {
        let url = `/member/getTreeData/${props.user.id}`;

        if (search) {
            url += `?search=${search}`;
        }

        const response = await axios.get(url);
        root.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false;
    }
}

getResults();

</script>

<template>
    <div class="flex pt-2 gap-3 md:gap-10">
        <div class="inline-flex items-center gap-3">
            <LVL1Icon class="h-12" />
            <div class="font-semibold text-sm">
                LVL 1
            </div>
        </div>
        <div class="inline-flex items-center gap-3">
            <LVL2Icon class="h-12" />
            <div class="font-semibold text-sm">
                LVL 2
            </div>
        </div>
        <div class="inline-flex items-center gap-3">
            <LVL3Icon class="h-12" />
            <div class="font-semibold text-sm">
                LVL 3
            </div>
        </div>
    </div>
    <AffiliateChild
        :node="root"
        :isLoading="isLoading"
        class="pt-8 overflow-x-auto"
    />
</template>
