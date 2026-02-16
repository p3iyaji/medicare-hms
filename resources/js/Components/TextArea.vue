<script setup>
import { ref, onMounted } from "vue";

const model = defineModel({
    type: [String, Number],
    default: "",
});

const textarea = ref(null);

onMounted(() => {
    if (textarea.value.hasAttribute("autofocus")) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value.focus() });
</script>

<template>
    <textarea
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-500"
        v-model="model"
        rows="5"
        ref="textarea"
    />
</template>
