<script setup>
import {transactionFormat} from "@/Composables/index.js";
import { Link, usePage } from '@inertiajs/vue3'
import KycAction from "@/Pages/Dashboard/Partials/KycAction.vue";
import { ChevronRightIcon } from '@heroicons/vue/solid'
import {ref, watchEffect} from "vue";
import Loading from "@/Components/Loading.vue";


const props = defineProps({
    pendingMemberCount: Number
})

const pendingMembers = ref({data: []});
const isLoading = ref(false);
const getResults = async () => {
    isLoading.value = true;
    try {
        let url = `/getPendingKyc`;

        const response = await axios.get(url);
        pendingMembers.value = response.data.pendingMembers;

    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false;
    }
}

getResults();

watchEffect(() => {
    if (usePage().props.title !== null) {
        getResults();
    }
});

</script>

<template>
    <div class="p-5 rounded-[10px] dark:bg-gray-700 relative">
        <div class="flex absolute -top-1.5 -left-1.5 bg-pink-500 rounded-full w-6 h-6 text-center justify-center text-xs p-1">
            {{ pendingMemberCount }}
        </div>
        <div class="flex justify-between text-xl font-semibold border-b dark:border-gray-600 pb-3">
            <h3>
                Pending KYC
            </h3>
            <Link :href="route('member.member_listing')" class="dark:bg-gray-500 dark:hover:bg-gray-600 rounded-full w-6 h-6">
                <ChevronRightIcon aria-hidden="true" class="w-6 h-6" />
            </Link>
        </div>
        <div class="relative overflow-x-auto mt-2">
            <div v-if="isLoading" class="w-full flex justify-center mt-8">
                <Loading />
            </div>
            <table v-else class="w-full text-sm text-left">
                <thead class="text-xs dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3">
                        Name
                    </th>
                    <th scope="col" class="px-4 py-3 text-center">
                        Action
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="pendingMembers.data.length === 0" >
                    <th colspan="2" class="py-4 text-lg">
                        <div class="flex flex-col dark:text-gray-400 mt-3 items-center">
                            <img src="/assets/no_data.png" class="w-60" alt="">
                            No data to show
                        </div>
                    </th>
                </tr>
                <tr v-for="member in pendingMembers.data" class="dark:odd:bg-gray-600 dark:even:bg-gray-700 dark:text-white">
                    <th scope="row" class="px-4 py-2">
                        <div class="inline-flex items-center justify-center gap-2">
                            <img :src="member.profile_photo_url ? member.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-10 h-10 rounded-full" alt="">
                            <div class="grid">
                                <div class="font-medium dark:text-white">
                                    {{ member.name }}
                                </div>
                                <div class="font-normal dark:text-gray-400">
                                    {{ member.email }}
                                </div>
                            </div>
                        </div>
                    </th>

                    <td class="px-4 py-2 text-center">
                        <KycAction
                            :member = "member"
                        />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
