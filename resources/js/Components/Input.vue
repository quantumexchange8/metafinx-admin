<script setup>
import { onMounted, ref } from 'vue'

defineProps({
    modelValue: [String, Number],
    withIcon: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['update:modelValue'])

const input = ref(null)

const focus = () => input.value?.focus()

defineExpose({
    input,
    focus
})

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus()
    }
})
</script>

<template>
    <input
        :class="[
            'py-2 rounded-lg placeholder:text-gray-400 text-gray-800',
            'focus:border-pink-700 focus:ring focus:ring-pink-500 focus:ring-offset-0 focus:ring-offset-white',
            'dark:bg-gray-600 dark:text-white',
            'disabled:bg-gray-100 dark:disabled:bg-gray-700 disabled:text-gray-300 dark:disabled:text-gray-400',
            {
                'px-4': !withIcon,
                'pl-11 pr-4': withIcon,
            },
        ]"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>
