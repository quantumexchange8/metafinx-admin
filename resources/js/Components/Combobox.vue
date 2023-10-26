<script setup>
import {ref, computed, watch} from 'vue'
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue'
import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/solid'
import debounce from "lodash/debounce.js";

defineEmits(['update:modelValue'])
const props = defineProps({
    modelValue: Object,
    options: {
        type: Array,
        default: () => [],
    },
    loadOptions: Function,
    error: String,
})

const options = ref(props.options);
const isLoading = ref(false);

let query = ref('')

const debouncedLoadOptions = debounce((q) => {
    if (props.loadOptions) {
        isLoading.value = true;
        props.loadOptions(q, (results) => {
            options.value = results;

            if (
                props.modelValue &&
                !options.value.some((o) => {
                    return o.value === props.modelValue?.value;
                })
            ) {
                options.value.unshift(props.modelValue);
            }
            isLoading.value = false;
        });
    }
}, 300); // Adjust the debounce delay as needed

watch(query, () => {
    debouncedLoadOptions(query.value);
}, { immediate: true });

let filteredOptions = computed(() =>
    query.value === ''
        ? options.value
        : options.value.filter((option) =>
            option.label
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(query.value.toLowerCase().replace(/\s+/g, ''))
        )
)
</script>

<template>
    <Combobox
        by="value"
        :model-value="props.modelValue"
        @update:model-value="value => $emit('update:modelValue', value)"
    >
        <div class="relative">
            <div
                class="relative w-full cursor-default overflow-hidden rounded-lg bg-white dark:bg-gray-600 text-left"
                :class="[
                    { 'border border-error-500': error }
                ]"
            >
                <ComboboxInput
                    class="w-full rounded-lg py-2 pr-10 leading-5 dark:bg-gray-600 text-gray-900 dark:text-white focus:border-pink-500 focus-visible:ring-2 focus-visible:ring-pink-500 border-2 border-transparent"
                    :displayValue="(option) => option.label"
                    @change="query = $event.target.value"
                    autocomplete="off"
                />
                <ComboboxButton
                    class="absolute inset-y-0 right-0 flex items-center pr-2"
                >
                    <ChevronDownIcon
                        class="h-5 w-5 text-gray-400"
                        aria-hidden="true"
                    />
                </ComboboxButton>
            </div>
            <TransitionRoot
                leave="transition ease-in duration-100"
                leaveFrom="opacity-100"
                leaveTo="opacity-0"
                @after-leave="query = ''"
            >
                <ComboboxOptions
                    class="absolute z-50 mt-1 max-h-24 w-full overflow-auto rounded-md bg-white dark:bg-gray-600 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                >
                    <div
                        v-if="filteredOptions.length === 0 && !isLoading"
                        class="relative cursor-default select-none py-2 px-4 text-gray-700 dark:text-white"
                    >
                        Nothing found.
                    </div>
                    <div
                        v-if="isLoading"
                        class="relative cursor-default select-none py-2 px-4 text-gray-700 dark:text-white"
                    >
                        Loading...
                    </div>

                    <template v-if="!isLoading">
                        <ComboboxOption
                            v-for="option in filteredOptions"
                            as="template"
                            :key="option.value"
                            :value="option"
                            v-slot="{ selected, active }"
                        >
                            <li
                                class="relative inline-flex items-center gap-2 w-full cursor-default select-none py-2 pl-3 pr-4"
                                :class="{
                                      'bg-gray-500 text-white': active,
                                      'text-white': !active,
                                      }"
                            >
                                <img :src="option.img ? option.img : 'https://img.freepik.com/free-icon/user_318-159711.jpg'" class="w-8 h-8 rounded-full" alt="">
                                <span
                                    class="block truncate"
                                    :class="{ 'font-medium': selected, 'font-normal': !selected }"
                                >
                                    {{ option.label }}
                                </span>
                                <span
                                    v-if="selected"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3"
                                    :class="{ 'text-white': active, 'text-gray-200': !active }"
                                >
                                    <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                </span>
                            </li>
                        </ComboboxOption>
                    </template>
                </ComboboxOptions>
            </TransitionRoot>
            <div class="text-sm text-error-500 mt-2" v-if="props.error">{{ props.error }}</div>
        </div>
    </Combobox>
</template>
