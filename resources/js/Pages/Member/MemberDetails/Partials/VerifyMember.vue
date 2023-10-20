<script setup>
import Button from "@/Components/Button.vue";
import {useForm} from "@inertiajs/vue3";
import {alertTriangle} from "@/Components/Icons/outline.jsx";

const props = defineProps({
    member_details: Object
})
const emit = defineEmits(['update:memberDetailModal']);

const form = useForm({
    user_id: props.member_details.id,
})

const verifyMember = () => {
    form.patch(route('member.verify_member'), {
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
    <div>
        <alertTriangle aria-hidden="true" class="w-12 h-12" />
    </div>
    <div class="mt-5">
        <h1 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white" style="font-family: Montserrat,sans-serif">
            Verify member
        </h1>
        <p class="dark:text-gray-400 text-sm">
            Do you want to verify this member?
        </p>
    </div>
    <div class="mt-5 flex gap-3 justify-center">
        <Button variant="secondary" class="px-6 w-1/2 justify-center" @click="closeModal">
            <span class="text-sm font-semibold">Cancel</span>
        </Button>
        <Button class="px-6 w-1/2 justify-center" variant="primary" @click.prevent="verifyMember">
            <span class="text-sm font-semibold">Confirm</span>
        </Button>
    </div>
</template>