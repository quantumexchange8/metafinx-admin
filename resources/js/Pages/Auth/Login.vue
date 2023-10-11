<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { MailIcon, LoginIcon, KeyIcon, EyeOffIcon, EyeIcon } from '@heroicons/vue/outline'
import InputIconWrapper from '@/Components/InputIconWrapper.vue'
import Button from '@/Components/Button.vue'
import Checkbox from '@/Components/Checkbox.vue'
import GuestLayout from '@/Layouts/Guest.vue'
import Input from '@/Components/Input.vue'
import Label from '@/Components/Label.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import Caption from "@/Components/Auth/Caption.vue";
import InputError from "@/Components/InputError.vue";
import {ref} from "vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <GuestLayout title="Log in">

        <div class="text-left">
            <Caption
                title="Log in"
                caption="Welcome back! Please enter your details."
            />
        </div>
        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="space-y-2">
                    <Label for="email" value="Email" />
                    <InputIconWrapper>
                        <template #icon>
                            <MailIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon
                            id="email"
                            type="email"
                            :class="form.errors.email ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            class="block w-full"
                            placeholder="you@example.com"
                            v-model="form.email"
                            autofocus
                            autocomplete="username"
                        />
                    </InputIconWrapper>
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <Label for="password" value="Password" />
                    <InputIconWrapper>
                        <template #icon>
                            <KeyIcon aria-hidden="true" class="w-5 h-5 dark:text-gray-400" />
                        </template>
                        <Input
                            withIcon
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            :class="form.errors.password ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                            class="block w-full"
                            placeholder="Enter password"
                            v-model="form.password"
                            autocomplete="current-password"
                        />
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                            @click="togglePasswordVisibility"
                        >
                            <template v-if="showPassword">
                                <EyeIcon aria-hidden="true" class="w-5 h-5" />
                            </template>
                            <template v-else>
                                <EyeOffIcon aria-hidden="true" class="w-5 h-5" />
                            </template>
                        </div>

                    </InputIconWrapper>
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ml-2 text-sm text-white">Remember for 30 days</span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-500 hover:underline">
                        Forgot your password?
                    </Link>
                </div>

                <div>
                    <Button class="justify-center gap-2 w-full" :disabled="form.processing" v-slot="{iconSizeClasses}">
                        <LoginIcon aria-hidden="true" :class="iconSizeClasses" />
                        <span>Log in</span>
                    </Button>
                </div>

            </div>
        </form>
    </GuestLayout>
</template>
