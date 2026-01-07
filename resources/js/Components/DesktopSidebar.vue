<template>
    <div class="flex flex-col h-full">
        <!-- User Profile Summary -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div
                        class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center"
                    >
                        <span class="text-white font-medium">{{
                            auth.user?.initials || "U"
                        }}</span>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">
                        {{ auth.user?.name || "User" }}
                    </h3>
                    <p class="text-xs text-gray-500 capitalize">
                        {{ auth.user?.user_type_label || "User" }}
                    </p>
                    <div
                        v-if="auth.user?.email_verified"
                        class="mt-1 flex items-center text-xs text-gray-500"
                    >
                        <svg
                            class="flex-shrink-0 mr-1.5 h-4 w-4 text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Verified Account
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 pb-4 space-y-1 overflow-y-auto">
            <template v-for="item in navigation" :key="item.name">
                <!-- Regular Navigation Item -->
                <div v-if="!item.children">
                    <a
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-blue-50 text-blue-700 border-blue-600'
                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                            'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg border-l-4 transition-colors',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            :class="[
                                item.current
                                    ? 'text-blue-600'
                                    : 'text-gray-400 group-hover:text-gray-500',
                                'mr-3 flex-shrink-0 h-5 w-5',
                            ]"
                            aria-hidden="true"
                        />
                        <span class="truncate">{{ item.name }}</span>
                    </a>
                </div>

                <!-- Dropdown Navigation Item -->
                <div v-else class="relative">
                    <button
                        @click="toggleDropdown(item.name)"
                        :class="[
                            item.current || openDropdown === item.name
                                ? 'bg-blue-50 text-blue-700 border-blue-600'
                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                            'group w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg border-l-4 transition-colors',
                        ]"
                    >
                        <div class="flex items-center">
                            <component
                                :is="item.icon"
                                :class="[
                                    item.current || openDropdown === item.name
                                        ? 'text-blue-600'
                                        : 'text-gray-400 group-hover:text-gray-500',
                                    'mr-3 flex-shrink-0 h-5 w-5',
                                ]"
                                aria-hidden="true"
                            />
                            <span class="truncate">{{ item.name }}</span>
                        </div>
                        <svg
                            :class="[
                                openDropdown === item.name
                                    ? 'transform rotate-180'
                                    : '',
                                'ml-2 h-5 w-5 text-gray-400 transition-transform',
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
                    </button>

                    <!-- Dropdown Menu -->
                    <transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div
                            v-show="openDropdown === item.name"
                            class="mt-1 ml-3 pl-3 border-l-2 border-gray-200 space-y-1"
                        >
                            <a
                                v-for="child in item.children"
                                :key="child.name"
                                :href="child.href"
                                :class="[
                                    isCurrentChild(child.href)
                                        ? 'text-blue-600 bg-blue-50'
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
                                    'block px-3 py-2 text-sm rounded-md transition-colors',
                                ]"
                            >
                                <div class="font-medium">{{ child.name }}</div>
                                <div
                                    v-if="child.description"
                                    class="text-xs text-gray-500 mt-0.5"
                                >
                                    {{ child.description }}
                                </div>
                            </a>
                        </div>
                    </transition>
                </div>
            </template>
        </nav>

        <!-- Quick Stats -->
        <div class="p-6 border-t border-gray-200">
            <h4
                class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
                Quick Stats
            </h4>
            <div class="mt-3 space-y-3">
                <div
                    v-if="auth.user?.user_type === 'doctor'"
                    class="flex items-center justify-between"
                >
                    <span class="text-sm text-gray-600">Today's Patients</span>
                    <span class="text-sm font-semibold text-gray-900">0</span>
                </div>
                <div
                    v-if="auth.user?.user_type === 'patient'"
                    class="flex items-center justify-between"
                >
                    <span class="text-sm text-gray-600"
                        >Upcoming Appointments</span
                    >
                    <span class="text-sm font-semibold text-gray-900">0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Unread Messages</span>
                    <span class="text-sm font-semibold text-gray-900">0</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
    auth: Object,
    navigation: Array,
});

const openDropdown = ref(null);
const currentPath = ref("");

// Initialize current path on client side only
onMounted(() => {
    if (typeof window !== "undefined") {
        currentPath.value = window.location.pathname;
    }
});

function toggleDropdown(itemName) {
    openDropdown.value = openDropdown.value === itemName ? null : itemName;
}

function isCurrentChild(childHref) {
    if (typeof window === "undefined") return false;
    return currentPath.value.startsWith(childHref);
}
</script>
