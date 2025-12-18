<template>
    <Transition
        enter-active-class="transform transition ease-in-out duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transform transition ease-in-out duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div v-show="open" class="fixed inset-y-0 right-0 z-50 w-96 bg-white shadow-xl">
            <div class="h-full flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 flex items-center space-x-4">
                        <button class="px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-800">
                            Mark all as read
                        </button>
                        <button class="px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800">
                            Clear all
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="flex-1 overflow-y-auto">
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            :class="[
                                'px-6 py-4 hover:bg-gray-50 transition-colors duration-150',
                                notification.read ? 'bg-white' : 'bg-blue-50'
                            ]"
                        >
                            <div class="flex items-start">
                                <div :class="[
                                    'flex-shrink-0 rounded-lg p-2',
                                    getNotificationIconClass(notification.type)
                                ]">
                                    <component :is="getNotificationIcon(notification.type)" class="h-5 w-5 text-white" />
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                        <button
                                            v-if="!notification.read"
                                            @click="$emit('markAsRead', notification.id)"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                        >
                                            Mark as read
                                        </button>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ notification.message }}</p>
                                    <p class="mt-2 text-xs text-gray-500">{{ notification.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <Link :href="route('notifications.index')" class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        View all notifications
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
    CalendarIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    BellAlertIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    open: Boolean,
    notifications: Array,
})

defineEmits(['close', 'markAsRead'])

function getNotificationIcon(type) {
    switch(type) {
        case 'appointment': return CalendarIcon
        case 'lab': return DocumentTextIcon
        case 'alert': return ExclamationTriangleIcon
        default: return BellAlertIcon
    }
}

function getNotificationIconClass(type) {
    switch(type) {
        case 'appointment': return 'bg-blue-500'
        case 'lab': return 'bg-green-500'
        case 'alert': return 'bg-red-500'
        default: return 'bg-gray-500'
    }
}
</script>