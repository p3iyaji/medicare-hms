<template>
    <div class="flex-1 pt-5 pb-4 overflow-y-auto">
        <div class="flex-shrink-0 flex items-center px-4">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <span class="ml-3 text-xl font-bold text-gray-900">MediCare Pro</span>
        </div>
        <nav class="mt-8 px-2 space-y-1">
            <template v-for="item in navigation" :key="item.name">
                <a
                    v-if="item.visible"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-blue-50 text-blue-700 border-blue-500'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent',
                        'group flex items-center px-3 py-2 text-base font-medium rounded-md border-l-4'
                    ]"
                >
                    <span class="mr-4">
                        <component
                            :is="item.icon"
                            :class="[
                                item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 h-6 w-6'
                            ]"
                            aria-hidden="true"
                        />
                    </span>
                    {{ item.name }}
                </a>
            </template>
        </nav>
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
    navigation: Array,
})
</script>