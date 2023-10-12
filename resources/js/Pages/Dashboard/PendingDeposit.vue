<script setup>
import {transactionFormat} from "@/Composables/index.js";

const props = defineProps({
    pendingDeposits: Object
})

const { formatAmount } = transactionFormat();

</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700">
        <div class="flex justify-between text-xl font-semibold border-b dark:border-gray-600 pb-3">
            <h3>
                Pending Deposit
            </h3>
        </div>
        <div class="relative overflow-x-auto mt-2">
            <table class="w-full text-sm text-left">
                <thead class="text-xs dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3">
                        Name
                    </th>
                    <th scope="col" class="px-4 py-3">
                        Amount
                    </th>
                    <th scope="col" class="px-4 py-3">
                        Status
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="pendingDeposit in props.pendingDeposits" class="dark:odd:bg-gray-600 dark:even:bg-gray-700 dark:text-white">
                    <th scope="row" class="px-4 py-2">
                        <div class="inline-flex items-center justify-center gap-2">
                            <img :src="pendingDeposit.user.profile_photo_url ? pendingDeposit.user.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-10 h-10 rounded-full" alt="">
                            <div class="grid">
                                <div class="font-medium dark:text-white">
                                    {{ pendingDeposit.user.name }}
                                </div>
                                <div class="font-normal dark:text-gray-400">
                                    {{ pendingDeposit.user.email }}
                                </div>
                            </div>
                        </div>
                    </th>
                    <td class="px-4 py-2">
                        $ {{ formatAmount(pendingDeposit.amount) }}
                    </td>
                    <td class="px-4 py-2">
                        {{ pendingDeposit.status }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
