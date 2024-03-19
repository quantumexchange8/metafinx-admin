<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ChevronRight} from "@/Components/Icons/outline.jsx";
import { MailIcon, KeyIcon, EyeOffIcon, EyeIcon } from '@heroicons/vue/outline';
import {Link, useForm} from '@inertiajs/vue3'
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {ref} from "vue";
import {transactionFormat} from "@/Composables/index.js";
import Button from "@/Components/Button.vue";

const props = defineProps({
    permissionsList: Object,
    admin_permissions: Object,
    admin: Object,
})

const enabled = ref(false)
const { formatType } = transactionFormat();
const showPassword = ref(false);
const form = useForm({
    id: props.admin.id,
    name: props.admin.name,
    position: props.admin.role,
    email: props.admin.email,
    password: "",
    permissionsList: props.admin_permissions,
})

const submit = () => {
    form.post(route('admin_user.editSubAdmin'), {
        onSuccess: () => {
            form.reset();
        },
    })
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <AuthenticatedLayout title="Edit Sub Admin">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-sm font-semibold dark:text-gray-400">
                    <Link class="dark:hover:text-white" :href="route('admin_user.admin_listing')">Admin User</Link>
                </h2>
                <ChevronRight aria-hidden="true" class="w-6 h-6" />
                <h2 class="text-sm font-semibold dark:text-white">
                    Edit Sub Admin
                </h2>
            </div>
        </template>

        <form class="mt-2 space-y-8">
            <div>
                <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full pb-3">
                    General Information
                </h3>
                <div class="mt-5 flex flex-col gap-5">
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="name" value="Name" />
                        <div class="md:col-span-2">
                            <Input
                                id="name"
                                type="text"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="Enter name"
                                v-model="form.name"
                                autofocus
                                :class="form.errors.name ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.name" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="position" value="Position" />
                        <div class="md:col-span-2">
                            <Input
                                id="position"
                                type="text"
                                class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                placeholder="Enter position"
                                v-model="form.position"
                                :class="form.errors.position ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            />
                            <InputError :message="form.errors.position" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="email" value="Email" />
                        <div class="md:col-span-2">
                            <InputIconWrapper class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                                <template #icon>
                                    <MailIcon aria-hidden="true" class="w-5 h-5 text-gray-400" />
                                </template>
                                <Input
                                    withIcon
                                    id="email"
                                    type="email"
                                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                                    placeholder="you@example.com"
                                    v-model="form.email"
                                    disabled
                                    :class="form.errors.email ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600 disabled:border-gray-300 dark:disabled:border-transparent'"
                                />
                            </InputIconWrapper>

                            <InputError :message="form.errors.email" class="mt-1 col-span-4" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 md:grid md:grid-cols-5">
                        <Label class="text-sm dark:text-white" for="password" value="Password" />
                        <div class="md:col-span-2">
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
                </div>
            </div>
            <div>
                <h3 class="text-base font-semibold dark:text-white border-b dark:border-gray-700 w-full pb-3">
                    Sub-Admin Permissions
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div
                        v-for="(permission_group, name) in props.permissionsList"
                        class="my-5 p-5 rounded-xl dark:bg-gray-700 space-y-3 w-full"
                    >
                        <h4 class="text-sm font-semibold dark:text-white pb-3 border-b dark:border-gray-600">
                            Allow {{ formatType(name) }} Access
                        </h4>
                        <div
                            v-for="permission in permission_group"
                            class="text-sm flex gap-8"
                        >
                            <div>
                                <label class="relative inline-flex items-center gap-8 mb-5 cursor-pointer">
                                    <input type="checkbox" v-model="form.permissionsList" :value="permission.name" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-success-500"></div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white"> {{ formatType(permission.name) }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
                <Button
                    variant="primary"
                    class="px-6 py-2 justify-center"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <span class="text-sm font-semibold">Confirm</span>
                </Button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
