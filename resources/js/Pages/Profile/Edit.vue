<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import UpdatePhoneForm from "./Partials/UpdatePhoneForm.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ProfilePhotoUpload from "./Partials/ProfilePhotoUpload.vue";
import MedicalProfileForm from "./Partials/MedicalProfileForm.vue";
import EmergencyContactForm from "./Partials/EmergencyContactForm.vue";
import HealthInsurance from "./Partials/HealthInsurance.vue";
import PersonalInfoForm from "./Partials/PersonalInfoForm.vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },

    profile: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });
const user = computed(() => usePage().props.auth.user);

// Active tab state
const activeTab = ref("personal");

// Custom completion calculation
const customCompletionCalculator = (user, profile) => {
    let completion = 0;
    const totalFields = 16;

    const userFields = [
        "first_name",
        "last_name",
        "email",
        "phone",
        "date_of_birth",
        "gender",
        "user_type",
    ];
    const completedUserFields = userFields.filter(
        (field) => user[field] && user[field].toString().trim() !== ""
    ).length;

    const profileFields = [
        "blood_type",
        "height",
        "weight",
        "emergency_contact_name",
        "emergency_contact_phone",
        "emergency_contact_relationship",
        "insurance_provider",
        "insurance_policy_number",
        "allergies",
        "chronic_conditions",
    ];
    const completedProfileFields = profileFields.filter(
        (field) => profile[field] && profile[field].toString().trim() !== ""
    ).length;

    const hasProfileImage = user.profile_image ? 1 : 0;

    const completedFields =
        completedUserFields + completedProfileFields + hasProfileImage;
    completion = Math.round((completedFields / totalFields) * 100);

    return Math.min(completion, 100);
};

const handlePersonalInfoUpdated = () => {
    console.log("Personal info updated - refreshing data...");
    router.reload({
        only: ["user", "profile"],
        preserveScroll: true,
        preserveState: true,
    });
};

// Handle photo update events
const handlePhotoUpdated = () => {
    console.log("Photo updated - refresh user data if needed");
};

const handlePhotoDeleted = () => {
    console.log("Photo deleted - refresh user data if needed");
};

const handleCompletionUpdated = (completion) => {
    console.log("Profile completion updated:", completion + "%");
};

// Handle medical profile
const handleMedicalProfileUpdated = () => {
    console.log("Medical profile updated - refreshing data...");
    router.reload({
        only: ["profile"],
        preserveScroll: true,
        preserveState: true,
    });
};

const handleEmergencyContactUpdated = () => {
    console.log("Emergency contact profile updated - refreshing data ...");
    router.reload({
        only: ["profile"],
        preserveScroll: true,
        preserveState: true,
    });
};

const handleInsuranceInfoUpdated = () => {
    console.log("Insurance info updated - refreshing data ...");
    router.reload({
        only: ["profile"],
        preserveScroll: true,
        preserveState: true,
    });
};

const exportPDF = () => {
    window.open("/profile-export", "_blank");
};
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0"
            >
                <div class="mb-3 sm:mb-0">
                    <h2
                        class="text-xl sm:text-2xl font-bold leading-tight text-gray-900"
                    >
                        Profile Management
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Complete and manage your medical profile information
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    Last updated:
                    {{ new Date(user.updated_at).toLocaleDateString() }}
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-3 sm:px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col sm:flex-row sm:items-center mb-4 justify-end gap-3 sm:gap-0"
                >
                    <button
                        @click="exportPDF"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Export as PDF
                    </button>
                </div>

                <!-- Profile Header -->
                <div
                    class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 sm:mb-8"
                >
                    <div
                        class="bg-gradient-to-r from-blue-600 to-teal-500 px-4 sm:px-6 py-6 sm:py-8"
                    >
                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-4"
                        >
                            <div class="flex-shrink-0 self-center sm:self-auto">
                                <div
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white flex items-center justify-center shadow-lg"
                                >
                                    <svg
                                        v-if="!user.profile_image"
                                        class="w-8 h-8 sm:w-10 sm:h-10 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    <img
                                        v-else
                                        :src="'/storage/' + user.profile_image"
                                        alt="Profile"
                                        class="w-full h-full rounded-full object-cover"
                                    />
                                </div>
                            </div>
                            <div class="text-white text-center sm:text-left">
                                <h1
                                    class="text-xl sm:text-2xl font-bold truncate"
                                >
                                    {{ user.full_name }}
                                </h1>
                                <p class="text-blue-100 truncate">
                                    {{ user.email }}
                                </p>
                                <div
                                    class="mt-2 flex flex-wrap justify-center sm:justify-start gap-2"
                                >
                                    <span
                                        class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-800 bg-opacity-50"
                                    >
                                        {{
                                            user.user_type?.replace("_", " ") ||
                                            "Medical Professional"
                                        }}
                                    </span>
                                    <span
                                        v-if="user.is_verified"
                                        class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-green-800 bg-opacity-50"
                                    >
                                        <svg
                                            class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        Verified
                                    </span>
                                    <span
                                        v-if="user.mfa_enabled"
                                        class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-purple-800 bg-opacity-50"
                                    >
                                        <svg
                                            class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                        2FA Enabled
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation - Mobile Optimized -->
                <div class="mb-6 sm:mb-8">
                    <div class="border-b border-gray-200">
                        <nav
                            class="-mb-px flex space-x-2 sm:space-x-8 overflow-x-auto pb-1"
                        >
                            <button
                                @click="activeTab = 'personal'"
                                :class="[
                                    activeTab === 'personal'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-3 px-2 sm:py-4 sm:px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-1 sm:mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                <span class="hidden sm:inline"
                                    >Personal Info</span
                                >
                                <span class="sm:hidden">Personal</span>
                            </button>
                            <button
                                @click="activeTab = 'medical'"
                                :class="[
                                    activeTab === 'medical'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-3 px-2 sm:py-4 sm:px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-1 sm:mr-2"
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
                                <span class="hidden sm:inline"
                                    >Medical Profile</span
                                >
                                <span class="sm:hidden">Medical</span>
                            </button>
                            <button
                                @click="activeTab = 'emergency'"
                                :class="[
                                    activeTab === 'emergency'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-3 px-2 sm:py-4 sm:px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-1 sm:mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                                <span class="hidden sm:inline">Emergency</span>
                                <span class="sm:hidden">Emergency</span>
                            </button>
                            <button
                                @click="activeTab = 'insurance'"
                                :class="[
                                    activeTab === 'insurance'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-3 px-2 sm:py-4 sm:px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-1 sm:mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <span class="hidden sm:inline">Insurance</span>
                                <span class="sm:hidden">Insurance</span>
                            </button>
                            <button
                                @click="activeTab = 'security'"
                                :class="[
                                    activeTab === 'security'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-3 px-2 sm:py-4 sm:px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-1 sm:mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </svg>
                                <span class="hidden sm:inline">Security</span>
                                <span class="sm:hidden">Security</span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="space-y-6 sm:space-y-8">
                    <!-- Personal Information Tab -->
                    <div v-show="activeTab === 'personal'" class="space-y-6">
                        <!-- Header with Profile Completion -->
                        <div
                            class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-xl shadow-sm p-4 sm:p-6"
                        >
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4"
                            >
                                <div class="mb-3 sm:mb-0">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Personal Information
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Manage your personal details
                                    </p>
                                </div>
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3"
                                >
                                    <div class="w-full sm:w-auto">
                                        <div
                                            class="text-xs font-medium text-gray-500"
                                        >
                                            Last Updated
                                        </div>
                                        <div
                                            class="text-sm text-gray-900 truncate"
                                        >
                                            {{
                                                new Date(
                                                    user.updated_at
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Grid -->
                        <div class="space-y-6 lg:grid lg:grid-cols-3 lg:gap-6">
                            <!-- Left Column: Basic Info & Contact -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Personal Information Card -->
                                <div
                                    class="bg-white rounded-xl shadow-sm overflow-hidden"
                                >
                                    <div
                                        class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <h4
                                                class="text-base font-semibold text-gray-900 flex items-center"
                                            >
                                                <svg
                                                    class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                    />
                                                </svg>
                                                Basic Information
                                            </h4>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 font-medium"
                                            >
                                                Required
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4 sm:p-6">
                                        <PersonalInfoForm
                                            :user="user"
                                            @updated="handlePersonalInfoUpdated"
                                        />
                                    </div>
                                </div>

                                <!-- Contact Information Card -->
                                <div
                                    class="bg-white rounded-xl shadow-sm overflow-hidden"
                                >
                                    <div
                                        class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-white"
                                    >
                                        <h4
                                            class="text-base font-semibold text-gray-900 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 mr-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 8l7.89-5.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Contact Information
                                        </h4>
                                    </div>
                                    <div class="p-4 sm:p-6">
                                        <div class="space-y-4 sm:space-y-6">
                                            <!-- Email Section -->
                                            <div
                                                class="bg-gray-50 rounded-lg p-3 sm:p-4"
                                            >
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3"
                                                >
                                                    <div class="flex-1 min-w-0">
                                                        <div
                                                            class="flex items-center mb-1"
                                                        >
                                                            <svg
                                                                class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M3 8l7.89-5.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                                />
                                                            </svg>
                                                            <label
                                                                class="text-sm font-medium text-gray-700 truncate"
                                                                >Email
                                                                Address</label
                                                            >
                                                        </div>
                                                        <div
                                                            class="text-gray-900 font-medium truncate"
                                                            :title="user.email"
                                                        >
                                                            {{ user.email }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex-shrink-0 mt-2 sm:mt-0"
                                                    >
                                                        <span
                                                            v-if="
                                                                user.email_verified_at
                                                            "
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                        >
                                                            <svg
                                                                class="w-3 h-3 mr-1"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                            Verified
                                                        </span>
                                                        <span
                                                            v-else
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                                        >
                                                            <svg
                                                                class="w-3 h-3 mr-1"
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
                                                            Pending
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Phone Section -->
                                            <div
                                                class="bg-gray-50 rounded-lg p-3 sm:p-4"
                                            >
                                                <UpdatePhoneForm />
                                                <div
                                                    class="mt-3 flex items-center"
                                                >
                                                    <span
                                                        v-if="
                                                            user.phone_verified_at
                                                        "
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                    >
                                                        <svg
                                                            class="w-3 h-3 mr-1"
                                                            fill="currentColor"
                                                            viewBox="0 0 20 20"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>
                                                        Verified
                                                    </span>
                                                    <span
                                                        v-else-if="user.phone"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                                    >
                                                        Verification Required
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Profile Image & Status -->
                            <div class="space-y-6">
                                <!-- Profile Image Card -->
                                <div
                                    class="bg-white rounded-xl shadow-sm overflow-hidden"
                                >
                                    <div
                                        class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white"
                                    >
                                        <h4
                                            class="text-base font-semibold text-gray-900 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 mr-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Profile Image
                                        </h4>
                                    </div>
                                    <div class="p-4 sm:p-6">
                                        <ProfilePhotoUpload
                                            :user="user"
                                            :profile="profile"
                                            :completion-calculator="
                                                customCompletionCalculator
                                            "
                                            @photo-updated="handlePhotoUpdated"
                                            @photo-deleted="handlePhotoDeleted"
                                            @completion-updated="
                                                handleCompletionUpdated
                                            "
                                        />
                                    </div>
                                </div>

                                <!-- Account Status Card -->
                                <div
                                    class="bg-white rounded-xl shadow-sm overflow-hidden"
                                >
                                    <div
                                        class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white"
                                    >
                                        <h4
                                            class="text-base font-semibold text-gray-900 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 mr-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                                />
                                            </svg>
                                            Account Status
                                        </h4>
                                    </div>
                                    <div class="p-4 sm:p-6">
                                        <div class="space-y-3 sm:space-y-4">
                                            <!-- Account Status -->
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                            >
                                                <div
                                                    class="flex items-center min-w-0"
                                                >
                                                    <svg
                                                        class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 flex-shrink-0"
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
                                                    <div class="min-w-0">
                                                        <div
                                                            class="text-sm font-medium text-gray-700 truncate"
                                                        >
                                                            Account Status
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500 truncate"
                                                        >
                                                            Current account
                                                            state
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    v-if="user.is_active"
                                                    class="ml-2 inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800 flex-shrink-0"
                                                >
                                                    Active
                                                </span>
                                                <span
                                                    v-else
                                                    class="ml-2 inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-red-100 text-red-800 flex-shrink-0"
                                                >
                                                    Inactive
                                                </span>
                                            </div>

                                            <!-- User Type -->
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                            >
                                                <div
                                                    class="flex items-center min-w-0"
                                                >
                                                    <svg
                                                        class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 flex-shrink-0"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                        />
                                                    </svg>
                                                    <div class="min-w-0">
                                                        <div
                                                            class="text-sm font-medium text-gray-700 truncate"
                                                        >
                                                            User Type
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500 truncate"
                                                        >
                                                            Role in the system
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    class="ml-2 inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-100 text-blue-800 flex-shrink-0 truncate max-w-[120px]"
                                                >
                                                    {{
                                                        user.user_type_label ||
                                                        user.user_type?.replace(
                                                            "_",
                                                            " "
                                                        ) ||
                                                        "User"
                                                    }}
                                                </span>
                                            </div>

                                            <!-- 2FA Status -->
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                            >
                                                <div
                                                    class="flex items-center min-w-0"
                                                >
                                                    <svg
                                                        class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 flex-shrink-0"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                        />
                                                    </svg>
                                                    <div class="min-w-0">
                                                        <div
                                                            class="text-sm font-medium text-gray-700 truncate"
                                                        >
                                                            Two-Factor Auth
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500 truncate"
                                                        >
                                                            Extra security layer
                                                        </div>
                                                    </div>
                                                </div>
                                                <span
                                                    v-if="user.mfa_enabled"
                                                    class="ml-2 inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-purple-100 text-purple-800 flex-shrink-0"
                                                >
                                                    <svg
                                                        class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    Enabled
                                                </span>
                                                <span
                                                    v-else
                                                    class="ml-2 inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-800 flex-shrink-0"
                                                >
                                                    Disabled
                                                </span>
                                            </div>

                                            <!-- Last Login -->
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                            >
                                                <div
                                                    class="flex items-center min-w-0"
                                                >
                                                    <svg
                                                        class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 mr-2 sm:mr-3 flex-shrink-0"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 01118 0z"
                                                        />
                                                    </svg>
                                                    <div class="min-w-0">
                                                        <div
                                                            class="text-sm font-medium text-gray-700 truncate"
                                                        >
                                                            Last Login
                                                        </div>
                                                        <div
                                                            class="text-xs text-gray-500 truncate"
                                                        >
                                                            Recent account
                                                            activity
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="text-right flex-shrink-0 ml-2"
                                                >
                                                    <div
                                                        class="text-sm font-medium text-gray-900 truncate"
                                                    >
                                                        {{
                                                            user.last_login_at
                                                                ? new Date(
                                                                      user.last_login_at
                                                                  ).toLocaleDateString()
                                                                : "Never"
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            user.last_login_at
                                                        "
                                                        class="text-xs text-gray-500 truncate"
                                                    >
                                                        {{
                                                            new Date(
                                                                user.last_login_at
                                                            ).toLocaleTimeString(
                                                                [],
                                                                {
                                                                    hour: "2-digit",
                                                                    minute: "2-digit",
                                                                }
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div
                                            class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200"
                                        >
                                            <h5
                                                class="text-sm font-medium text-gray-900 mb-2 sm:mb-3"
                                            >
                                                Quick Actions
                                            </h5>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button
                                                    @click="
                                                        activeTab = 'security'
                                                    "
                                                    class="inline-flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out"
                                                >
                                                    <svg
                                                        class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                        />
                                                    </svg>
                                                    Security
                                                </button>
                                                <button
                                                    @click="
                                                        activeTab = 'medical'
                                                    "
                                                    class="inline-flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition duration-150 ease-in-out"
                                                >
                                                    <svg
                                                        class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
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
                                                    Medical
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Profile Tab -->
                    <div
                        v-show="activeTab === 'medical'"
                        class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-6"
                    >
                        <MedicalProfileForm
                            :profile="profile"
                            @updated="handleMedicalProfileUpdated"
                        />
                    </div>

                    <!-- Emergency Contact Tab -->
                    <div
                        v-show="activeTab === 'emergency'"
                        class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6"
                        >
                            Emergency Contact Information
                        </h3>
                        <EmergencyContactForm
                            :profile="profile"
                            :user-address="user.residential_address"
                            @updated="handleEmergencyContactUpdated"
                        />
                    </div>

                    <!-- Insurance Tab -->
                    <div
                        v-show="activeTab === 'insurance'"
                        class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6"
                        >
                            Insurance Information
                        </h3>
                        <HealthInsurance
                            :profile="profile"
                            @updated="handleInsuranceInfoUpdated"
                        />
                    </div>

                    <!-- Security Tab -->
                    <div
                        v-show="activeTab === 'security'"
                        class="space-y-4 sm:space-y-6"
                    >
                        <!-- Update Password -->
                        <div
                            class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-6"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6"
                            >
                                Security Settings
                            </h3>
                            <UpdatePasswordForm />

                            <!-- Two-Factor Authentication -->
                            <div
                                class="mt-6 sm:mt-8 pt-4 sm:pt-8 border-t border-gray-200"
                            >
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                >
                                    <div class="mb-2 sm:mb-0">
                                        <h4
                                            class="text-base font-medium text-gray-900"
                                        >
                                            Two-Factor Authentication
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Add an extra layer of security to
                                            your account
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span
                                            v-if="user.mfa_enabled"
                                            class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800"
                                        >
                                            <svg
                                                class="w-3 h-3 sm:w-4 sm:h-4 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            Enabled
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-800"
                                        >
                                            Disabled
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Account -->
                        <div
                            class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-6"
                        >
                            <DeleteUserForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for tabs */
nav::-webkit-scrollbar {
    height: 3px;
}

nav::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

nav::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 2px;
}

/* Smooth transitions for tab content */
[v-show] {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Prevent text overflow */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Better mobile touch targets */
@media (max-width: 640px) {
    button,
    [role="button"] {
        min-height: 44px;
    }

    select,
    input {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }
}

/* Improved mobile spacing */
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1.5rem;
    }
}

/* Ensure proper spacing on small screens */
@media (max-width: 640px) {
    .p-4 {
        padding: 1rem;
    }

    .space-y-4 > * + * {
        margin-top: 1rem;
    }
}
</style>
