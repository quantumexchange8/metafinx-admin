<script setup>
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import InputIconWrapper from "@/Components/InputIconWrapper.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {PhoneIcon, IdNoIcon} from "@/Components/Icons/outline.jsx";
import { MailIcon, KeyIcon, EyeOffIcon, EyeIcon } from '@heroicons/vue/outline';
import BaseListbox from "@/Components/BaseListbox.vue";
import InputError from "@/Components/InputError.vue";
import Combobox from "@/Components/Combobox.vue";
import Label from "@/Components/Label.vue";

const props = defineProps({
    member_details: Object,
    upline_member: Object,
    settingRank: Array,
})
const emit = defineEmits(['update:memberDetailModal']);

const form = useForm({
    user_id: props.member_details.id,
    name: props.member_details.name,
    phone: props.member_details.phone,
    email: props.member_details.email,
    identity_number: props.member_details.identity_number,
    password: '',
    rank: props.member_details.setting_rank_id,
    upline_id: {value: props.upline_member.id, label: props.upline_member.name},
})

const showPassword = ref(false);

function loadUsers(query, setOptions) {
    fetch('/member/getAllUsers?query=' + query + '&id=' + props.member_details.id)
        .then(response => response.json())
        .then(results => {
            setOptions(
                results.map(user => {
                    return {
                        value: user.id,
                        label: user.name,
                        img: user.profile_photo
                    }
                })
            )
        });
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.patch(route('member.edit_member'), {
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
    <form>
        <div class="flex flex-col gap-4 md:gap-8 mt-3 mb-8">
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <Label class="text-sm dark:text-white" for="name" value="Name" />
                <div class="md:col-span-3">
                    <Input
                    id="name"
                    type="text"
                    class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600 px-3 py-0"
                    :class="form.errors.name ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                    />
                    <InputError :message="form.errors.name" class="mt-1 col-span-4" />
                </div>

            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <Label class="text-sm dark:text-white" for="phone" value="Phone Number" />
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
                <Label class="text-sm dark:text-white" for="email" value="Email" />
                <div class="md:col-span-3">
                    <InputIconWrapper class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                        <template #icon>
                            <MailIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon id="email" type="email" class="w-full"  placeholder="Email" v-model="form.email" disabled autocomplete="email"
                            :class="form.errors.email ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.email" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <Label class="text-sm dark:text-white" for="identity_number" value="Identity Number" />
                <div class="md:col-span-3">
                    <InputIconWrapper class="flex flex-row items-center gap-3 w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600">
                        <template #icon>
                            <IdNoIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon id="identity_number" type="text" class="w-full"  placeholder="Identity Number" v-model="form.identity_number" autocomplete="identity_number"
                            :class="form.errors.identity_number ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.identity_number" class="mt-1 col-span-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <Label class="text-sm dark:text-white" for="password" value="Password" />
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
                        class="w-full rounded-lg text-base text-black dark:text-white dark:bg-gray-600"
                        v-model="form.rank"
                        :options="settingRank"
                        :error="form.errors.rank"
                        placeholder="Please Select"
                    />
                </div>
            </div>
            <div class="flex flex-col gap-1 md:grid md:grid-cols-4">
                <span class="text-sm dark:text-white">Upline</span>
                <div class="md:col-span-3">
                    <Combobox
                        :load-options="loadUsers"
                        v-model="form.upline_id"
                        :error="form.errors.upline_id"
                        image
                    />
                </div>
            </div>
        </div>
        <div class="flex pt-8 gap-3 justify-end border-t dark:border-gray-700">
            <Button
                type="button"
                variant="secondary"
                class="px-4 py-2 justify-center"
                @click="closeModal"
            >
                <span class="text-sm font-semibold">Cancel</span>
            </Button>
            <Button
                variant="primary"
                class="px-4 py-2 justify-center"
                :disabled="form.processing"
                @click.prevent="submit"
            >
                <span class="text-sm font-semibold">Confirm</span>
            </Button>
        </div>
    </form>
</template>
