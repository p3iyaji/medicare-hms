<!-- Create a new component: DropdownMenu.vue -->
<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import { ChevronDownIcon } from "@heroicons/vue/20/solid";
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
    appointment: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["view", "notes", "complete", "cancel", "delete"]);

const buttonRef = ref(null);
const menuStyle = ref({});

const updateMenuPosition = () => {
    if (buttonRef.value) {
        const rect = buttonRef.value.getBoundingClientRect();
        menuStyle.value = {
            position: "fixed",
            top: `${rect.bottom + window.scrollY}px`,
            left: `${rect.left + window.scrollX}px`,
            zIndex: 9999,
            width: "12rem",
        };
    }
};

onMounted(() => {
    window.addEventListener("scroll", updateMenuPosition);
    window.addEventListener("resize", updateMenuPosition);
});

onUnmounted(() => {
    window.removeEventListener("scroll", updateMenuPosition);
    window.removeEventListener("resize", updateMenuPosition);
});
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton
                ref="buttonRef"
                @click="updateMenuPosition"
                class="inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <ChevronDownIcon class="h-4 w-4" />
            </MenuButton>
        </div>

        <teleport to="body">
            <MenuItems
                :style="menuStyle"
                class="rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            >
                <div class="py-1">
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="emit('view', appointment.id)"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900'
                                    : 'text-gray-700',
                                'flex w-full items-center px-4 py-2 text-sm',
                            ]"
                        >
                            <EyeIcon class="mr-3 h-5 w-5 text-gray-400" />
                            View Details
                        </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="emit('notes', appointment)"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900'
                                    : 'text-gray-700',
                                'flex w-full items-center px-4 py-2 text-sm',
                            ]"
                        >
                            <DocumentTextIcon
                                class="mr-3 h-5 w-5 text-gray-400"
                            />
                            Add/View Notes
                        </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="emit('complete', appointment.id)"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900'
                                    : 'text-gray-700',
                                'flex w-full items-center px-4 py-2 text-sm',
                            ]"
                        >
                            <CheckCircleIcon
                                class="mr-3 h-5 w-5 text-gray-400"
                            />
                            Mark Complete
                        </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="emit('cancel', appointment.id)"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900'
                                    : 'text-gray-700',
                                'flex w-full items-center px-4 py-2 text-sm',
                            ]"
                        >
                            <XCircleIcon class="mr-3 h-5 w-5 text-gray-400" />
                            Cancel
                        </button>
                    </MenuItem>
                    <div class="border-t border-gray-100"></div>
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="emit('delete', appointment)"
                            :class="[
                                active
                                    ? 'bg-red-50 text-red-700'
                                    : 'text-red-600',
                                'flex w-full items-center px-4 py-2 text-sm',
                            ]"
                        >
                            <TrashIcon class="mr-3 h-5 w-5 text-red-400" />
                            Delete
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </teleport>
    </Menu>
</template>
