<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white flex flex-col">
        <!-- Background Medical Image -->
        <div class="fixed inset-0 z-0 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 to-teal-900/10"></div>
            <img 
                src="images/medical-bg.jpg"
                alt="Medical equipment background"
                class="w-full h-full object-cover opacity-10"
            />
            <!-- Subtle grid pattern overlay -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zMCA2MGMxNi41NjkgMCAzMC0xMy40MzEgMzAtMzBTNDYuNTY5IDAgMzAgMCAwIDEzLjQzMSAwIDMwczEzLjQzMSAzMCAzMCAzem0wLTJjMTUuNDY0IDAgMjgtMTIuNTM2IDI4LTI4UzQ1LjQ2NCAyIDMwIDIgMiAxNC41MzYgMiAzMHMyLjUzNiAyOCAyOCAyOHoiIGZpbGw9IiMwMDczY2MiIGZpbGwtb3BhY2l0eT0iMC4wMyIvPjwvZz48L3N2Zz4=')]"></div>
        </div>

        <!-- Simple Header -->
        <header class="relative z-10 py-4">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <span class="ml-2 text-lg font-bold text-gray-800">MediCare Pro</span>
                    </a>
                    <div class="ml-auto">
                        <a href="/register" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Create account
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content - Centered Login -->
        <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                <!-- Status Message -->
                <div v-if="status" class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-sm font-medium text-green-700 text-center">{{ status }}</p>
                </div>

                <!-- Login Card -->
                <div class="relative">
                    <!-- Gradient Border Effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-teal-400 rounded-2xl blur opacity-20"></div>
                    
                    <!-- Main Card -->
                    <div class="relative bg-white rounded-xl shadow-xl p-8 border border-gray-100">
                        <!-- Logo and Title -->
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-xl mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
                            <p class="mt-2 text-sm text-gray-600">Sign in to access your medical management dashboard</p>
                        </div>

                        <!-- Login Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Email Field -->
                            <div>
                                <InputLabel for="email" value="Email Address" class="block text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="pl-10 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        v-model="form.email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="you@example.com"
                                    />
                                </div>
                                <InputError class="mt-1" :message="form.errors.email" />
                            </div>

                            <!-- Password Field -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <InputLabel for="password" value="Password" class="block text-sm font-medium text-gray-700" />
                                    <a v-if="canResetPassword" href="/forgot-password" class="text-xs text-blue-600 hover:text-blue-500 font-medium">
                                        Forgot password?
                                    </a>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="password"
                                        type="password"
                                        class="pl-10 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        v-model="form.password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                    />
                                </div>
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <InputLabel for="remember" value="Remember me" class="ml-2 text-sm text-gray-600" />
                            </div>

                            <!-- Submit Button -->
                            <PrimaryButton
                                class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 border-transparent text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="!form.processing">Sign in to dashboard</span>
                                <span v-else class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Signing in...
                                </span>
                            </PrimaryButton>
                        </form>

                        <!-- Divider -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <p class="text-center text-sm text-gray-600">
                                Don't have an account?
                                <a href="/register" class="ml-1 font-medium text-blue-600 hover:text-blue-500">
                                    Create one now
                                </a>
                            </p>
                        </div>

                        <!-- Trust Badges -->
                        <div class="mt-6 flex items-center justify-center space-x-4">
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Secure
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                HIPAA Compliant
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back to Home Link -->
                <div class="mt-6 text-center">
                    <a href="/" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to homepage
                    </a>
                </div>
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="relative z-10 py-4">
            <div class="max-w-7xl mx-auto px-4">
                <p class="text-center text-xs text-gray-500">
                    &copy; 2024 MediCare Pro. For authorized medical professionals only.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Form input focus styles */
input:focus {
    outline: none;
    ring: 2px;
    ring-color: #3b82f6;
}

/* Subtle floating animation for the card */
@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.relative {
    animation: float 6s ease-in-out infinite;
}
</style>