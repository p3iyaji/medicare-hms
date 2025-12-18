<template>
    <div class="flex flex-col h-full">
        <!-- User Profile Summary -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center">
                        <span class="text-white font-medium">{{ auth.user?.initials || 'U' }}</span>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">{{ auth.user?.name || 'User' }}</h3>
                    <p class="text-xs text-gray-500 capitalize">{{ auth.user?.user_type_label || 'User' }}</p>
                    <div v-if="auth.user?.email_verified" class="mt-1 flex items-center text-xs text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verified Account
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 pb-4 space-y-1 overflow-y-auto">
            <template v-for="item in navigation" :key="item.name">
                <a
                    v-if="item.visible"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-blue-50 text-blue-700 border-blue-500'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                        'group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4'
                    ]"
                >
                    <span class="mr-3">
                        <component
                            :is="item.icon"
                            :class="[
                                item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 h-5 w-5'
                            ]"
                            aria-hidden="true"
                        />
                    </span>
                    {{ item.name }}
                </a>
            </template>
        </nav>

        <!-- Quick Stats -->
        <div class="p-6 border-t border-gray-200">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Stats</h4>
            <div class="mt-3 space-y-3">
                <div v-if="auth.user?.user_type === 'doctor'" class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Today's Patients</span>
                    <span class="text-sm font-semibold text-gray-900">0</span>
                </div>
                <div v-if="auth.user?.user_type === 'patient'" class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Upcoming Appointments</span>
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
import {
    HomeIcon,
    CalendarIcon,
    DocumentTextIcon,
    UserGroupIcon,
    UserIcon,
    CreditCardIcon,
    ChartBarIcon,
    CogIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    auth: Object,
    navigation: Array,
})
</script>