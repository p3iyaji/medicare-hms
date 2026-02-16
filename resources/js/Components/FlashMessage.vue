<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { XMarkIcon } from "@heroicons/vue/20/solid";
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const show = ref(false);
const timeout = ref(null);

const flash = computed(() => page.props.flash || {});

const type = computed(() => {
    if (flash.value.success) return "success";
    if (flash.value.error) return "error";
    if (flash.value.warning) return "warning";
    if (flash.value.info) return "info";
    return null;
});

const message = computed(() => {
    return (
        flash.value.success ||
        flash.value.error ||
        flash.value.warning ||
        flash.value.info ||
        ""
    );
});

const icon = computed(() => {
    switch (type.value) {
        case "success":
            return CheckCircleIcon;
        case "error":
            return ExclamationCircleIcon;
        case "warning":
            return ExclamationTriangleIcon;
        case "info":
            return InformationCircleIcon;
        default:
            return null;
    }
});

const bgColor = computed(() => {
    switch (type.value) {
        case "success":
            return "bg-green-50 border-green-400 text-green-800";
        case "error":
            return "bg-red-50 border-red-400 text-red-800";
        case "warning":
            return "bg-yellow-50 border-yellow-400 text-yellow-800";
        case "info":
            return "bg-blue-50 border-blue-400 text-blue-800";
        default:
            return "bg-gray-50 border-gray-400 text-gray-800";
    }
});

const iconColor = computed(() => {
    switch (type.value) {
        case "success":
            return "text-green-400";
        case "error":
            return "text-red-400";
        case "warning":
            return "text-yellow-400";
        case "info":
            return "text-blue-400";
        default:
            return "text-gray-400";
    }
});

watch(
    flash,
    (newVal) => {
        if (newVal.success || newVal.error || newVal.warning || newVal.info) {
            show.value = true;

            // Auto-hide after 5 seconds
            if (timeout.value) clearTimeout(timeout.value);
            timeout.value = setTimeout(() => {
                show.value = false;
            }, 5000);
        }
    },
    { deep: true },
);

const close = () => {
    show.value = false;
    if (timeout.value) clearTimeout(timeout.value);
};
</script>

<template>
    <div
        v-if="show && message"
        class="fixed bottom-20 right-4 z-[20000] max-w-md w-full animate-slide-in"
    >
        <div :class="['border-l-4 p-4 rounded-r-lg shadow-lg', bgColor]">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <component
                        :is="icon"
                        :class="['h-5 w-5', iconColor]"
                        aria-hidden="true"
                    />
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">
                        {{ message }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button
                        @click="close"
                        :class="[
                            'inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                            iconColor,
                        ]"
                    >
                        <span class="sr-only">Close</span>
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}
</style>
