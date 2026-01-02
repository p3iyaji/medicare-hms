<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <!-- Background Medical Image -->
        <div class="fixed inset-0 z-0 overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-r from-blue-900/20 to-teal-900/10"
            ></div>
            <img
                src="images/medical-bg.jpg"
                alt="Medical equipment background"
                class="w-full h-full object-cover opacity-20"
            />
            <!-- Subtle grid pattern overlay -->
            <div
                class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zMCA2MGMxNi41NjkgMCAzMC0xMy40MzEgMzAtMzBTNDYuNTY5IDAgMzAgMCAwIDEzLjQzMSAwIDMwczEzLjQzMSAzMCAzMCAzem0wLTJjMTUuNDY0IDAgMjgtMTIuNTM2IDI4LTI4UzQ1LjQ2NCAyIDMwIDIgMiAxNC41MzYgMiAzMHMyLjUzNiAyOCAyOCAyOHoiIGZpbGw9IiMwMDczY2MiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4=')]"
            ></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="flex justify-between items-center py-6 md:justify-start md:space-x-10"
                >
                    <!-- Logo -->
                    <div class="flex justify-start lg:w-0 lg:flex-1">
                        <a href="/" class="flex items-center">
                            <div
                                class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg"
                            >
                                <svg
                                    class="w-6 h-6 text-white"
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
                            <span class="ml-3 text-2xl font-bold text-gray-900"
                                >MediCare Pro</span
                            >
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div
                        class="hidden md:flex items-center justify-end md:flex-1 lg:w-0"
                    >
                        <a
                            href="/login"
                            class="whitespace-nowrap text-base font-medium text-gray-700 hover:text-blue-600"
                        >
                            Sign in
                        </a>
                        <a
                            href="/register"
                            class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700"
                        >
                            Get Started
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16">
                <div class="text-center">
                    <!-- Warning Icon -->
                    <div
                        class="mx-auto w-24 h-24 rounded-full bg-red-100 flex items-center justify-center mb-8"
                    >
                        <svg
                            class="w-12 h-12 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                    <!-- Main Heading -->
                    <h1
                        class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl"
                    >
                        <span class="block">Account</span>
                        <span class="block text-red-600">Deactivated</span>
                    </h1>

                    <!-- Subheading -->
                    <div class="mt-6 max-w-3xl mx-auto">
                        <p class="text-xl text-gray-600">
                            Your MediCare Pro account has been temporarily
                            deactivated.
                        </p>
                        <p class="mt-4 text-lg text-gray-500">
                            This could be due to one of the following reasons:
                        </p>
                    </div>

                    <!-- Reasons Cards -->
                    <div
                        class="mt-12 max-w-4xl mx-auto grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-yellow-500"
                        >
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-yellow-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Inactivity Period
                            </h3>
                            <p class="mt-2 text-gray-500">
                                Your account may have been inactive for an
                                extended period for security reasons.
                            </p>
                        </div>

                        <div
                            class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-orange-500"
                        >
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-orange-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Payment Issue
                            </h3>
                            <p class="mt-2 text-gray-500">
                                There might be an outstanding payment or
                                subscription renewal pending.
                            </p>
                        </div>

                        <div
                            class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-blue-500"
                        >
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Security Review
                            </h3>
                            <p class="mt-2 text-gray-500">
                                Your account is undergoing a routine security
                                review to ensure compliance.
                            </p>
                        </div>
                    </div>

                    <!-- Reactivation Instructions -->
                    <div class="mt-16 max-w-3xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-3xl p-8 border border-blue-100"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                How to Reactivate Your Account
                            </h2>

                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-bold text-sm"
                                                >1</span
                                            >
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            Contact Support
                                        </h3>
                                        <p class="mt-1 text-gray-600">
                                            Reach out to our support team with
                                            your account email and practice
                                            details.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-bold text-sm"
                                                >2</span
                                            >
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            Verify Identity
                                        </h3>
                                        <p class="mt-1 text-gray-600">
                                            Complete the identity verification
                                            process as per HIPAA compliance
                                            requirements.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-bold text-sm"
                                                >3</span
                                            >
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            Account Review
                                        </h3>
                                        <p class="mt-1 text-gray-600">
                                            Our team will review your account
                                            and guide you through the
                                            reactivation process.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="mt-12 flex flex-col sm:flex-row justify-center gap-4"
                    >
                        <a
                            href="mailto:support@medicarepro.com"
                            class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10"
                        >
                            <svg
                                class="mr-2 w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            Contact Support
                        </a>

                        <a
                            href="/login"
                            class="inline-flex items-center justify-center px-8 py-3 border border-blue-600 text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 md:py-4 md:text-lg md:px-10"
                        >
                            Return to Login
                        </a>
                    </div>

                    <!-- Support Information -->
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <div class="max-w-2xl mx-auto">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div
                                        class="text-lg font-semibold text-blue-600"
                                    >
                                        24/7 Support
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500">
                                        Email: support@medicarepro.com
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-lg font-semibold text-blue-600"
                                    >
                                        Emergency Line
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500">
                                        1-800-MED-PRO
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-lg font-semibold text-blue-600"
                                    >
                                        Response Time
                                    </div>
                                    <div class="mt-1 text-sm text-gray-500">
                                        Within 2 business hours
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 bg-gray-900 mt-20">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 bg-white rounded-lg flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-blue-600"
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
                            <span class="ml-3 text-xl font-bold text-white"
                                >MediCare Pro</span
                            >
                        </div>
                        <p class="mt-4 text-gray-400 max-w-md">
                            Comprehensive medical management solution designed
                            to streamline healthcare delivery and improve
                            patient outcomes.
                        </p>
                    </div>

                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-200 tracking-wider uppercase"
                        >
                            Support
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li>
                                <a
                                    href="mailto:support@medicarepro.com"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Email Support</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Help Center</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >System Status</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >HIPAA Compliance</a
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-200 tracking-wider uppercase"
                        >
                            Legal
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Terms of Service</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Data Security</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-base text-gray-400 hover:text-white"
                                    >Compliance</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t border-gray-700 pt-8">
                    <p class="text-base text-gray-400 text-center">
                        &copy; 2026 MediCare Pro. All rights reserved. HIPAA
                        compliant. Patient data secured.
                    </p>
                    <p class="text-sm text-gray-500 text-center mt-2">
                        For account reactivation assistance, contact our support
                        team immediately.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    reason: {
        type: String,
        default: null,
    },
    deactivationDate: {
        type: String,
        default: null,
    },
});
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Pulse animation for warning icon */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
