<script setup>
import Button from "@/Components/Button.vue";
import {useForm} from "@inertiajs/vue3";
import {alertTriangle} from "@/Components/Icons/outline.jsx";
import Input from "@/Components/Input.vue";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";

const props = defineProps({
    investments: Object
})
const emit = defineEmits(['update:memberDetailModal']);

const form = useForm({
    investment_id: props.investments.id,
    remark: '',
})

const unsubPlan = () => {
    form.delete(route('member.unsubscribe_plan'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        },
    })
}

const closeModal = () => {
    emit('update:memberDetailModal', false);
}
</script>

<template>
    <form>
        <div>
            <alertTriangle aria-hidden="true" class="w-12 h-12" />
        </div>
        <div class="mt-5">
            <h1 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                Terminate investment plan
            </h1>
            <p class="dark:text-gray-400 text-sm">
                The selected investment plan is currently ongoing. Do you want to terminate investment plan for this member?
            </p>
        </div>
        <div class="flex flex-col w-full gap-2 py-4">
            <Label class="text-sm dark:text-white" for="remark" value="Remark" />
            <Input
                id="remark"
                type="text"
                placeholder="Enter remark (reason)"
                class="block w-full"
                :class="form.errors.remark ? 'border border-error-500 dark:border-error-500' : 'border border-gray-400 dark:border-gray-600'"
                v-model="form.remark"
            />
            <InputError :message="form.errors.remark" class="mt-2" />
        </div>
        <div class="mt-5 flex gap-3 justify-center">
            <Button
                type="button"
                variant="secondary"
                class="px-6 w-1/2 justify-center"
                @click="closeModal"
            >
                <span class="text-sm font-semibold">Cancel</span>
            </Button>
            <Button class="px-6 w-1/2 justify-center" variant="primary" @click.prevent="unsubPlan">
                <span class="text-sm font-semibold">Terminate</span>
            </Button>
        </div>
    </form>
</template>
