<script setup>
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {PhoneIcon} from "@/Components/Icons/outline.jsx";
import { MailIcon, KeyIcon, EyeOffIcon, EyeIcon } from '@heroicons/vue/outline';
import BaseListbox from "@/Components/BaseListbox.vue";
import InputError from "@/Components/InputError.vue";

const emit = defineEmits(['update:memberDetailModal']);

const form = useForm({
    name: "",
    phone: "",
    email: "",
    password: "",
    ranking: "",
    refCode: "",
})

const showPassword = ref(false);
const rankList = [
    {value:1, label:"Member"}, 
    {value:2, label:"Rank 1"},
    {value:3, label:"Rank 2"},
    {value:4, label:"Rank 3"},
];

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};



const submit = () => {
    form.post(route('member.addMember'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    })
}

const closeModal = () => {
    emit('update:memberDetailModal', false);
}
</script>

<template>
    <form
        @submit.prevent="submit"
    >
        <div class="flex flex-col gap-4 md:gap-8 mb-8">
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Name</span>
                <div class="md:col-span-3">
                    <Input
                    id="name"
                    type="text"
                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                    placeholder="Enter full name"
                    v-model="form.name"
                    autofocus
                    :class="form.errors.name ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                    autocomplete="name"
                    
                    />
                    <InputError :message="form.errors.name" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Phone Number</span>
                <div class="md:col-span-3">
                    <InputIconWrapper class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                        <template #icon>
                            <PhoneIcon aria-hidden="true" class="w-5 h-5" />
                        </template>
                        <Input
                            withIcon id="phone" type="text" class="w-full"  placeholder="Phone" v-model="form.phone" autocomplete="phone"
                            :class="form.errors.phone ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.phone" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Email</span>
                <div class="md:col-span-3">
                    <InputIconWrapper class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                        <template #icon>
                            <MailIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon id="email" type="email" class="w-full"  placeholder="Email" v-model="form.email" autocomplete="email"
                            :class="form.errors.email ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.email" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Password</span>
                <div class="md:col-span-3">
                    <InputIconWrapper class="relative flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                        <template #icon>
                            <KeyIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            :class="form.errors.password ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            class="block w-full"
                            placeholder="Minimum 8 characters"
                            v-model="form.password"
                            autocomplete="current-password"
                        />
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                            @click="togglePasswordVisibility"
                        >
                            <template v-if="showPassword">
                                <EyeIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                            </template>
                            <template v-else>
                                <EyeOffIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                            </template>
                        </div>
                    </InputIconWrapper>
                    <InputError :message="form.errors.password" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Ranking</span>
                <div class="md:col-span-3">
                    <BaseListbox
                        id="rankID"
                        class="w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600"
                        :class="form.errors.ranking? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        v-model="form.ranking"
                        :options="rankList"
                        placeholder = "Please Select"
                    />
                    <InputError :message="form.errors.ranking" class="mt-1 col-span-4" />
                </div>          
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Referral Code</span>
                <div class="md:col-span-3">
                    <Input
                    id="refCode"
                    type="text"
                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                    placeholder="Optional"
                    v-model="form.refCode"                    
                    />
                </div>
            </div>
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
</template>