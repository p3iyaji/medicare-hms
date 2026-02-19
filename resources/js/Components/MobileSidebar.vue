<template>
    <div class="flex-1 pt-5 pb-4 overflow-y-auto">
        <!-- Logo Section -->
        <div class="flex-shrink-0 flex items-center px-4">
            <div
                class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center"
            >
                <svg
                    class="w-5 h-5 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                    />
                </svg>
            </div>
            <span class="ml-3 text-xl font-bold text-gray-900"
                >MediCare Pro</span
            >
        </div>

        <!-- Navigation -->
        <nav class="mt-8 px-2 space-y-1">
            <template v-for="item in navigation" :key="item.name">
                <!-- Regular Navigation Item -->
                <div v-if="!item.children && item.visible !== false">
                    <a
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-blue-50 text-blue-700 border-blue-500'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                            'group flex items-center px-3 py-2.5 text-base font-medium rounded-md border-l-4 transition-colors',
                        ]"
                    >
                        <span class="mr-4">
                            <component
                                :is="item.icon"
                                :class="[
                                    item.current
                                        ? 'text-blue-500'
                                        : 'text-gray-400 group-hover:text-gray-500',
                                    'flex-shrink-0 h-6 w-6',
                                ]"
                                aria-hidden="true"
                            />
                        </span>
                        <span class="flex-1 truncate">{{ item.name }}</span>

                        <!-- Badge for regular items -->
                        <span
                            v-if="item.badge"
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                        >
                            {{ item.badge }}
                        </span>
                    </a>
                </div>

                <!-- Dropdown Navigation Item -->
                <div
                    v-else-if="item.children && item.visible !== false"
                    class="relative"
                >
                    <button
                        @click="toggleDropdown(item.name)"
                        @keydown.enter="toggleDropdown(item.name)"
                        @keydown.space.prevent="toggleDropdown(item.name)"
                        :aria-expanded="openDropdown === item.name"
                        :aria-controls="`dropdown-${item.name}`"
                        :class="[
                            openDropdown === item.name
                                ? 'bg-blue-50 text-blue-700 border-blue-500'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                            'group w-full flex items-center justify-between px-3 py-2.5 text-base font-medium rounded-md border-l-4 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                        ]"
                    >
                        <div class="flex items-center min-w-0">
                            <span class="mr-4">
                                <component
                                    :is="item.icon"
                                    :class="[
                                        openDropdown === item.name
                                            ? 'text-blue-500'
                                            : 'text-gray-400 group-hover:text-gray-500',
                                        'flex-shrink-0 h-6 w-6',
                                    ]"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="truncate">{{ item.name }}</span>
                        </div>

                        <!-- Badge and Arrow -->
                        <div class="flex items-center ml-2">
                            <span
                                v-if="item.badge"
                                class="mr-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                {{ item.badge }}
                            </span>
                            <svg
                                :class="[
                                    openDropdown === item.name
                                        ? 'transform rotate-180'
                                        : '',
                                    'h-5 w-5 text-gray-400 transition-transform duration-200',
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform scale-95 opacity-0 -translate-y-1"
                        enter-to-class="transform scale-100 opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="transform scale-100 opacity-100 translate-y-0"
                        leave-to-class="transform scale-95 opacity-0 -translate-y-1"
                    >
                        <div
                            v-show="openDropdown === item.name"
                            :id="`dropdown-${item.name}`"
                            class="mt-2 ml-7 pl-4 border-l-2 border-gray-200 space-y-1"
                        >
                            <a
                                v-for="child in item.children"
                                :key="child.name"
                                :href="child.href"
                                :class="[
                                    isCurrentChild(child.href)
                                        ? 'text-blue-600 bg-blue-50'
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
                                    'block px-3 py-2.5 text-sm rounded-md transition-colors group',
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{
                                        child.name
                                    }}</span>
                                    <span
                                        v-if="child.badge"
                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                                    >
                                        {{ child.badge }}
                                    </span>
                                </div>
                                <div
                                    v-if="child.description"
                                    class="text-xs text-gray-500 mt-0.5 group-hover:text-gray-600"
                                >
                                    {{ child.description }}
                                </div>
                            </a>
                        </div>
                    </transition>
                </div>
            </template>
        </nav>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import {
    HomeIcon,
    CalendarIcon,
    DocumentTextIcon,
    UserGroupIcon,
    UserIcon,
    CreditCardIcon,
    ChartBarIcon,
    CogIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    navigation: Array,
});

const openDropdown = ref(null);
const currentPath = ref("");

// Initialize current path on client side only
onMounted(() => {
    if (typeof window !== "undefined") {
        currentPath.value = window.location.pathname;

        // Auto-open dropdown if a child is active
        autoOpenActiveDropdown();

        // Close dropdown when clicking outside
        document.addEventListener("click", handleClickOutside);
    }
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        document.removeEventListener("click", handleClickOutside);
    }
});

// Watch for path changes
watch(currentPath, () => {
    autoOpenActiveDropdown();
});

function autoOpenActiveDropdown() {
    if (typeof window === "undefined" || !props.navigation) return;

    // Find which dropdown contains the current path and auto-open it
    const activeDropdown = props.navigation.find((item) =>
        item.children?.some((child) =>
            currentPath.value.startsWith(child.href),
        ),
    );

    if (activeDropdown) {
        openDropdown.value = activeDropdown.name;
    }
}

function toggleDropdown(itemName) {
    openDropdown.value = openDropdown.value === itemName ? null : itemName;
}

function isCurrentChild(childHref) {
    if (typeof window === "undefined") return false;
    return currentPath.value.startsWith(childHref);
}

function handleClickOutside(event) {
    // Check if click is outside dropdown areas
    const dropdowns = document.querySelectorAll("[aria-expanded]");
    let clickedInside = false;

    dropdowns.forEach((dropdown) => {
        if (dropdown.contains(event.target)) {
            clickedInside = true;
        }
    });

    if (!clickedInside) {
        openDropdown.value = null;
    }
}
</script>

<style scoped>
/* Smooth scrolling for navigation */
nav {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

nav::-webkit-scrollbar {
    width: 4px;
}

nav::-webkit-scrollbar-track {
    background: #f7fafc;
}

nav::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 20px;
}

/* Ensure touch targets are large enough for mobile */
@media (max-width: 640px) {
    nav a,
    nav button {
        min-height: 48px;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .ml-7 {
        margin-left: 2rem;
    }
}
</style>
