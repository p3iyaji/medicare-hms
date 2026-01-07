<script setup>
import { ref, onMounted } from "vue";

const model = defineModel({
    type: [String, Number],
    default: "",
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-500"
        v-model="model"
        ref="input"
    />
</template>
