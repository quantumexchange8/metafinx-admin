<script setup>
import {ref} from "vue";
import {RefreshIcon} from "@heroicons/vue/outline";

const isLoading = ref(false);
const refresh = ref(false);

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

</script>

<template>
    <div class="flex flex-col gap-5 w-full mb-14">
        <div class="flex justify-between pb-3 border-b border-gray-700">
            <h3 class="text-base font-semibold">Earning Information</h3>
            <RefreshIcon
            :class="{ 'animate-spin': isLoading }"
            class="flex-shrink-0 w-5 h-5 cursor-pointer dark:text-white ml-auto self-center"
            aria-hidden="true"
            @click="refreshTable"
            />
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Monthly Return</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Divended</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Ticket Bonus</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Referral Earning</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Affiliate Earning</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
        <div class="flex items-center gap-8">
            <p class="text-sm font-semibold text-gray-400 w-full">Total Dividend Earning</p>
            <p class="text-sm dark:text-white w-full">$ 0.00</p>
        </div>
    </div>
</template>