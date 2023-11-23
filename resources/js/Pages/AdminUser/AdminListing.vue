<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import Button from "@/Components/Button.vue";
import {AddIcon, UserSquareIcon, WarningIcon} from "@/Components/Icons/outline.jsx";
import { MailIcon, CogIcon, TrashIcon } from '@heroicons/vue/outline';
import {ref} from "vue";
import Modal from "@/Components/Modal.vue";
import DeleteMember from "@/Pages/Member/Partials/DeleteMember.vue";
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    admins: Object,
})

const deleteAdminModal = ref(false);
const subAdmin = ref({});

const openDeleteAdminModal = (admin) => {
    deleteAdminModal.value = true;
    subAdmin.value = admin;
}

const closeModal = () => {
    deleteAdminModal.value = false
}

const form = useForm({
    id: '',
})

const deleteAdmin = () => {
    form.id = subAdmin.value.id;
    form.delete(route('admin_user.deleteSubAdmin'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    })
}

</script>

<template>
    <AuthenticatedLayout title="Admin User">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight">
                        Admin User
                    </h2>
                    <p class="text-base font-normal dark:text-gray-400">
                        Manage your admin users and their permissions here.
                    </p>
                </div>
                <div class="justify-end">
                    <Button
                        type="button"
                        class="justify-center px-3 py-2 gap-2 grow focus:outline-none"
                        variant="primary"
                        :href="route('admin_user.add_sub_admin')"
                    >
                        <AddIcon aria-hidden="true" class="w-5 h-5" />
                        <span class="text-sm">Add Sub Admin</span>
                    </Button>
                </div>
            </div>
        </template>

        <div class="my-8 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div
                v-for="admin in props.admins"
                class="p-5 dark:bg-gray-700 rounded-xl"
            >
                <div class="flex">
                    <div class="flex flex-col gap-2 justify-center items-center w-64">
                        <img :src="admin.profile_photo_url ? admin.profile_photo_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-[60px] h-[60px] rounded-full" alt="">
                        <div class="text-sm font-semibold dark:text-white">
                            {{ admin.name }}
                        </div>
                    </div>
                    <div class="inline-block h-auto min-h-[3em] w-0.5 self-stretch bg-dark-eval-4 dark:bg-gray-600 opacity-100 mx-5 my-1"></div>
                    <div class="flex flex-col gap-3 w-full">
                        <div class="inline-flex items-center gap-3">
                            <MailIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                            <div class="text-sm font-normal">
                                {{ admin.email }}
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-3">
                            <UserSquareIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                            <div class="text-sm font-normal">
                                {{ admin.role }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <Button
                                type="button"
                                variant="gray"
                                class="justify-center px-3 py-2 gap-2 grow focus:outline-none"
                                @click="openDeleteAdminModal(admin)"
                            >
                                <TrashIcon aria-hidden="true" class="w-5 h-5" />
                                <span class="text-sm font-semibold">Delete</span>
                            </Button>
                            <Button
                                type="button"
                                class="justify-center px-3 py-2 gap-2 grow focus:outline-none"
                                variant="primary"
                                :href="`/admin_user/edit_sub_admin/${admin.id}`"
                            >
                                <CogIcon aria-hidden="true" class="w-5 h-5" />
                                <span class="text-sm">Edit</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="deleteAdminModal" title="Delete Sub-Admin" @close="closeModal" max-width="md">
            <div>
                <WarningIcon aria-hidden="true" class="w-12 h-12" />
            </div>
            <div class="mt-5">
                <h1 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                    Delete sub-admin
                </h1>
                <p class="dark:text-gray-400 text-sm">
                    Are you sure you want to delete this sub-admin? This action cannot be undone.
                </p>
            </div>
            <div class="mt-5 flex gap-3 justify-center">
                <Button variant="secondary" class="px-6 w-1/2 justify-center" @click="closeModal">
                    <span class="text-sm font-semibold">Cancel</span>
                </Button>
                <Button class="px-6 w-1/2 justify-center" variant="danger" @click.prevent="deleteAdmin">
                    <span class="text-sm font-semibold">Delete</span>
                </Button>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
