<script setup>
import { toRefs, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator(value) {
            return ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'gray', 'transparent', 'action', 'opacity'].includes(value)
        },
    },
    type: {
        type: String,
        default: 'submit',
    },
    size: {
        type: String,
        default: 'base',
        validator(value) {
            return ['sm', 'base', 'lg'].includes(value)
        },
    },
    squared: {
        type: Boolean,
        default: false,
    },
    pill: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    iconOnly: {
        type: Boolean,
        default: false,
    },
    srText: {
        type: String || undefined,
        default: undefined,
    },
    external: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(['click'])

const { type, variant, size, squared, pill, href, iconOnly, srText, external } = props

const { disabled } = toRefs(props)

const baseClasses = [
    'inline-flex items-center transition-colors font-medium select-none disabled:cursor-not-allowed focus:outline-none',
]

const variantClasses = (variant) => ({
    'bg-pink-500 text-white hover:bg-pink-600 disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600': variant === 'primary',
    'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-[#1B2530] disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600':
        variant === 'secondary',
    'bg-success-600 hover:bg-success-700 text-white dark:disabled:bg-success-900 dark:disabled:text-success-700': variant === 'success',
    'bg-error-600 text-white hover:bg-error-700 dark:disabled:bg-error-900 dark:disabled:text-error-700': variant === 'danger',
    'bg-warning-400 text-white hover:bg-warning-500 disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600': variant === 'warning',
    'bg-gray-600 border border-gray-500 text-white disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600': variant === 'info',
    'bg-gray-400 hover:bg-gray-500 text-white dark:bg-gray-500 dark:hover:bg-gray-600 disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600':
        variant === 'gray',
    'text-gray-400 bg-transparent dark:hover:text-white disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600':
        variant === 'transparent',
    'bg-gray-400 hover:bg-gray-500 text-white dark:bg-gray-600 dark:hover:bg-gray-800 disabled:bg-gray-100 disabled:text-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-600':
        variant === 'action',
    'bg-[#ffffff33] bg-opacity-20 hover:bg-[#ffffff33] hover:bg-opacity-20 text-white dark:bg-[#ffffff33] dark:bg-opacity-20 dark:hover:bg-[#ffffff33] dark:hover:bg-opacity-20 disabled:bg-[#ffffff33] disabled:text-gray-300 dark:disabled:bg-[#ffffff33] dark:disabled:text-gray-600':
        variant === 'opacity',
})

const classes = computed(() => [
    ...baseClasses,
    iconOnly
        ? {
            'p-1.5': size === 'sm',
            'p-2': size === 'base',
            'p-3': size === 'lg',
        }
        : {
            'px-3 py-1 text-sm': size === 'sm',
            'px-4 py-2 text-base': size === 'base',
            'px-5 py-2 text-xl': size === 'lg',
        },
    variantClasses(variant),
    {
        'rounded-lg': !squared && !pill,
        'rounded-full': pill,
    },
    {
        'pointer-events-none opacity-50': href && disabled.value,
    },
])

const iconSizeClasses = [
    {
        'w-5 h-5': size === 'sm',
        'w-6 h-6': size === 'base',
        'w-7 h-7': size === 'lg',
    },
]

const handleClick = (e) => {
    if (disabled.value) {
        e.preventDefault()
        e.stopPropagation()
        return
    }
    emit('click', e)
}

const Tag = external ?  'a' : Link
</script>

<template>
    <component
        :is="Tag"
        v-if="href"
        :href="!disabled ? href : null"
        :class="classes"
        :aria-disabled="disabled.toString()"
    >
        <span
            v-if="srText"
            class="sr-only"
        >
            {{ srText }}
        </span>

        <slot :iconSizeClasses="iconSizeClasses" />
    </component>

    <button
        v-else
        :type="type"
        :class="classes"
        @click="handleClick"
        :disabled="disabled"
    >
        <span
            v-if="srText"
            class="sr-only"
        >
            {{ srText }}
        </span>

        <slot :iconSizeClasses="iconSizeClasses" />
    </button>
</template>
