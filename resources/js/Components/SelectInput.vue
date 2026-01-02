<!-- resources/js/Components/SelectInput.vue -->
<template>
    <select
        :class="[
            'border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm',
            error
                ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                : '',
            'block w-full',
        ]"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        v-bind="$attrs"
    >
        <slot />
    </select>
</template>

<script setup>
defineProps({
    modelValue: [String, Number, Boolean],
    error: String,
});

defineEmits(["update:modelValue"]);
</script>
