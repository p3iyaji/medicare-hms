<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left side: Logo & Search -->
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <a href="/dashboard" class="ml-2 lg:ml-0 flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold text-gray-900 hidden sm:block">Nova-MediCare Pro</span>
                    </a>
                </div>

                <!-- Right side: Notifications & User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-3 focus:outline-none">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">{{ auth.user?.initials || 'U' }}</span>
                                    </div>
                                </div>
                                <div class="ml-3 hidden lg:block">
                                    <div class="text-sm font-medium text-gray-900">{{ auth.user?.name || 'User' }}</div>
                                    <div class="text-xs text-gray-500 capitalize">{{ auth.user?.user_type_label || 'User' }}</div>
                                </div>
                            </div>
                        </button>

                        <!-- Dropdown menu -->
                        <div v-show="userMenuOpen" @click="userMenuOpen = false" class="fixed inset-0 z-10"></div>
                        <div v-show="userMenuOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-20">
                            <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Your Profile
                                </div>
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <form @submit.prevent="logout">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center">
                                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Sign out
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar (Mobile) -->
        <div v-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
            <div v-show="sidebarOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <MobileSidebar :navigation="navigation" />
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-64 flex flex-col flex-1 pt-16">
            <!-- Page Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
                            <p v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</p>
                        </div>
                        <div class="flex space-x-3">
                            <slot name="headerActions"></slot>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Sidebar (Desktop) -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:pt-16 lg:border-r lg:border-gray-200 lg:bg-gray-50">
            <DesktopSidebar :auth="auth" :navigation="navigation" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import DesktopSidebar from '@/Components/DesktopSidebar.vue'
import MobileSidebar from '@/Components/MobileSidebar.vue'

const props = defineProps({
    title: String,
    subtitle: String,
})

const page = usePage()
const auth = computed(() => page.props.auth || { user: null })

const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

// Navigation based on user type - using simple hrefs
const navigation = computed(() => {
    const baseNav = [
        { 
            name: 'Dashboard', 
            href: '/dashboard', 
            icon: 'HomeIcon', 
            current: window.location.pathname === '/dashboard',
            visible: true
        },
    ]

    // Check if user exists and has user_type
    if (!auth.value.user || !auth.value.user.user_type) {
        return baseNav.filter(item => item.visible)
    }

    const userType = auth.value.user.user_type
    
    if (userType === 'patient') {
        baseNav.push(
            { name: 'My Appointments', href: '/appointments', icon: 'CalendarIcon', current: window.location.pathname.startsWith('/appointments'), visible: true },
            { name: 'Medical Records', href: '/medical-records', icon: 'DocumentTextIcon', current: window.location.pathname.startsWith('/medical-records'), visible: true },
            { name: 'Billing', href: '/billing', icon: 'CreditCardIcon', current: window.location.pathname.startsWith('/billing'), visible: true }
        )
    }

    if (['doctor', 'nurse'].includes(userType)) {
        baseNav.push(
            { name: 'Patients', href: '/patients', icon: 'UserGroupIcon', current: window.location.pathname.startsWith('/patients'), visible: true },
            { name: 'Appointments', href: '/appointments', icon: 'CalendarIcon', current: window.location.pathname.startsWith('/appointments'), visible: true },
            { name: 'Medical Records', href: '/medical-records', icon: 'DocumentTextIcon', current: window.location.pathname.startsWith('/medical-records'), visible: true }
        )
    }

    if (userType === 'admin') {
        baseNav.push(
            { name: 'Patients', href: '/patients', icon: 'UserGroupIcon', current: window.location.pathname.startsWith('/patients'), visible: true },
            { name: 'Doctors', href: '/doctors', icon: 'UserIcon', current: window.location.pathname.startsWith('/doctors'), visible: true },
            { name: 'Appointments', href: '/appointments', icon: 'CalendarIcon', current: window.location.pathname.startsWith('/appointments'), visible: true },
            { name: 'Medical Records', href: '/medical-records', icon: 'DocumentTextIcon', current: window.location.pathname.startsWith('/medical-records'), visible: true },
            { name: 'Reports', href: '/reports', icon: 'ChartBarIcon', current: window.location.pathname.startsWith('/reports'), visible: true },
            { name: 'Settings', href: '/settings', icon: 'CogIcon', current: window.location.pathname.startsWith('/settings'), visible: true }
        )
    }

    return baseNav.filter(item => item.visible)
})

function logout() {
    router.post('/logout')
}
</script>