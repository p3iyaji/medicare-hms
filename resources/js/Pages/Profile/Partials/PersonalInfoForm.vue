<!-- resources/js/Pages/Profile/Partials/PersonalInfoForm.vue -->
<script setup>
import { ref, computed, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/components/InputError.vue";
import { useForm, router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const genders = computed(() => usePage().props.medical.genders);
const titles = computed(() => usePage().props.medical.titles);
const religions = computed(() => usePage().props.medical.religions);
const nationalities = usePage().props.nationalities;
const states = usePage().props.states;

const emit = defineEmits(["updated"]);

const page = usePage();
const isEditing = ref(false);
const activeSection = ref("basic"); // 'basic', 'contact', 'professional', 'location'

// Initialize form with user data
const form = useForm({
    // Basic Info
    title: props.user.title || "",
    first_name: props.user.first_name || "",
    middle_name: props.user.middle_name || "",
    last_name: props.user.last_name || "",
    date_of_birth: props.user.date_of_birth || "",
    gender: props.user.gender || "",

    // Contact Info
    email: props.user.email || "",
    phone: props.user.phone || "",
    user_type: props.user.user_type || "",

    // Professional Info
    occupation: props.user.occupation || "",
    work_address: props.user.work_address || "",
    industry: props.user.industry || "",
    religion: props.user.religion || "",
    spoken_languages: props.user.spoken_languages
        ? props.user.spoken_languages.join("\n")
        : "",

    // Location Info
    residential_address: props.user.residential_address || "",
    region: props.user.region || "",
    county: props.user.county || "",
    district: props.user.district || "",

    // Foreign keys (will be handled separately)
    nationality_id: props.user.nationality_id || "",
    state_id: props.user.state_id || "",
    ethinic_region_id: props.user.ethinic_region_id || "",
});

// Computed properties
const fullName = computed(() => {
    return [form.first_name, form.middle_name, form.last_name]
        .filter((name) => name)
        .join(" ");
});

const initials = computed(() => {
    let initials = "";
    if (form.first_name) initials += form.first_name.charAt(0).toUpperCase();
    if (form.last_name) initials += form.last_name.charAt(0).toUpperCase();
    return initials || "U";
});

const age = computed(() => {
    if (!form.date_of_birth) return null;
    const birthDate = new Date(form.date_of_birth);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birthDate.getDate())
    ) {
        age--;
    }
    return age;
});

// Get shared data from Inertia
const medical = computed(() => page.props.medical || {});

// Section navigation
const sections = [
    { id: "basic", label: "Basic Info", icon: "user", color: "blue" },
    // { id: 'contact', label: 'Contact', icon: 'mail', color: 'green' },
    {
        id: "professional",
        label: "Professional info",
        icon: "briefcase",
        color: "purple",
    },
    {
        id: "location",
        label: "Residential address",
        icon: "map-pin",
        color: "green",
    },
];

// Start editing
const startEditing = () => {
    isEditing.value = true;
    activeSection.value = "basic";
    resetForm();
};

// Reset form data
const resetForm = () => {
    Object.keys(form.data()).forEach((key) => {
        if (key in props.user) {
            if (key === "spoken_languages" && props.user[key]) {
                form[key] = props.user[key].join(", ");
            } else {
                form[key] = props.user[key] || "";
            }
        }
    });
    form.clearErrors();
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    activeSection.value = "basic";
    form.clearErrors();
};

// Submit form
const submitForm = () => {
    // Convert spoken_languages from comma-separated string to array
    const formData = {
        ...form.data(),
        spoken_languages: form.spoken_languages
            ? form.spoken_languages
                  .split(",")
                  .map((lang) => lang.trim())
                  .filter((lang) => lang !== "")
            : [],
    };

    form.transform(() => formData).patch("profile", {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditing.value = false;
            activeSection.value = "basic";
            emit("updated");

            // Reload page to get updated data
            router.reload({
                only: ["user"],
                preserveScroll: true,
                preserveState: true,
            });
        },
        onError: (errors) => {
            console.error("Update failed:", errors);
        },
    });
};

// Get icon component
const getIcon = (iconName) => {
    const icons = {
        user: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
        mail: "M3 8l7.89-5.26a2 2 0 012.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
        briefcase:
            "M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
        "map-pin":
            "M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z",
        edit: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
    };
    return icons[iconName] || icons.user;
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    >
        <!-- Header -->
        <div
            class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white"
        >
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3"
            >
                <div class="mb-2 sm:mb-0">
                    <h3
                        class="text-base sm:text-lg font-semibold text-gray-900 flex items-center"
                    >
                        <svg
                            class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="getIcon('user')"
                            />
                        </svg>
                        Personal Information
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                        Manage your personal details and profile
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        v-if="!isEditing"
                        @click="startEditing"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 shadow-sm hover:shadow flex-shrink-0"
                    >
                        <svg
                            class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="getIcon('edit')"
                            />
                        </svg>
                        <span class="hidden xs:inline">Edit Information</span>
                        <span class="xs:hidden">Edit</span>
                    </button>
                    <button
                        v-if="isEditing"
                        @click="cancelEditing"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-medium rounded-lg transition-all duration-200 ease-in-out flex-shrink-0"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-6">
            <!-- Edit Mode -->
            <form
                v-if="isEditing"
                @submit.prevent="submitForm"
                class="space-y-4 sm:space-y-6"
            >
                <!-- Section Navigation - Mobile Optimized -->
                <div class="mb-4 sm:mb-6">
                    <div
                        class="flex overflow-x-auto pb-2 space-x-1 -mx-4 sm:-mx-6 px-4 sm:px-6"
                    >
                        <button
                            v-for="section in sections"
                            :key="section.id"
                            @click="activeSection = section.id"
                            type="button"
                            :class="[
                                'flex-shrink-0 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg flex items-center space-x-1 sm:space-x-2 transition-all duration-200 text-xs sm:text-sm',
                                activeSection === section.id
                                    ? `bg-${section.color}-100 text-${section.color}-800 border border-${section.color}-200 font-medium`
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100',
                            ]"
                        >
                            <svg
                                class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="getIcon(section.icon)"
                                />
                            </svg>
                            <span class="font-medium whitespace-nowrap">{{
                                section.label
                            }}</span>
                        </button>
                    </div>
                </div>

                <!-- Section Content -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Basic Information Section -->
                    <div
                        v-show="activeSection === 'basic'"
                        class="space-y-4 sm:space-y-6 animate-fadeIn"
                    >
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6"
                        >
                            <!-- Name Fields -->
                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="title"
                                        value="Title"
                                        class="text-xs sm:text-sm"
                                    />
                                    <select
                                        id="title"
                                        v-model="form.title"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                    >
                                        <option value="">Select Title</option>
                                        <option
                                            v-for="title in titles"
                                            :key="title"
                                            :value="title"
                                        >
                                            {{ title }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel
                                        for="first_name"
                                        value="First Name"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.first_name,
                                        }"
                                        required
                                    />
                                    <InputError
                                        :message="form.errors.first_name"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="middle_name"
                                        value="Middle Name"
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="middle_name"
                                        v-model="form.middle_name"
                                        type="text"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.middle_name,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.middle_name"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="last_name"
                                        value="Last Name"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="last_name"
                                        v-model="form.last_name"
                                        type="text"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.last_name,
                                        }"
                                        required
                                    />
                                    <InputError
                                        :message="form.errors.last_name"
                                        class="text-xs"
                                    />
                                </div>
                            </div>

                            <!-- Personal Details -->
                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="date_of_birth"
                                        value="Date of Birth"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.date_of_birth,
                                        }"
                                        required
                                    />
                                    <div
                                        v-if="age"
                                        class="mt-1 text-xs sm:text-sm text-gray-600"
                                    >
                                        Age: {{ age }} years old
                                    </div>
                                    <InputError
                                        :message="form.errors.date_of_birth"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="gender"
                                        value="Gender"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.gender,
                                        }"
                                        required
                                    >
                                        <option value="">Select Gender</option>
                                        <option
                                            v-for="gender in genders || []"
                                            :key="gender"
                                            :value="gender"
                                        >
                                            {{ gender.replaceAll("_", " ") }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.gender"
                                        class="text-xs"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        for="user_type"
                                        value="User Type"
                                        class="text-xs sm:text-sm"
                                    />
                                    <select
                                        id="user_type"
                                        v-model="form.user_type"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.user_type,
                                        }"
                                    >
                                        <option value="">
                                            Select User Type
                                        </option>
                                        <option
                                            v-for="(
                                                label, value
                                            ) in medical.user_types || {}"
                                            :key="value"
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.user_type"
                                        class="text-xs"
                                    />
                                </div>

                                <!-- Name Preview -->
                                <div
                                    class="bg-blue-50 border border-blue-100 rounded-xl p-3 sm:p-4 mt-4 sm:mt-6"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div class="min-w-0 flex-1 mr-3">
                                            <p
                                                class="text-xs font-medium text-blue-800 uppercase tracking-wide"
                                            >
                                                Name Preview
                                            </p>
                                            <p
                                                class="text-base sm:text-lg font-bold text-blue-900 mt-1 truncate"
                                            >
                                                {{ fullName || "Your Name" }}
                                            </p>
                                            <p
                                                class="text-xs sm:text-sm text-blue-700 mt-1"
                                            >
                                                Initials: {{ initials }}
                                            </p>
                                        </div>
                                        <div
                                            class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"
                                        >
                                            <span
                                                class="text-blue-800 font-bold text-sm sm:text-lg"
                                                >{{ initials }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div
                        v-show="activeSection === 'contact'"
                        class="space-y-4 sm:space-y-6 animate-fadeIn"
                    >
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6"
                        >
                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="email"
                                        value="Email Address"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300': form.errors.email,
                                        }"
                                        readonly
                                    />
                                    <InputError
                                        :message="form.errors.email"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="phone"
                                        value="Phone Number"
                                        required
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300': form.errors.phone,
                                        }"
                                        readonly
                                    />
                                    <InputError
                                        :message="form.errors.phone"
                                        class="text-xs"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="user_type"
                                        value="User Type"
                                        class="text-xs sm:text-sm"
                                    />
                                    <select
                                        id="user_type"
                                        v-model="form.user_type"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.user_type,
                                        }"
                                    >
                                        <option value="">
                                            Select User Type
                                        </option>
                                        <option
                                            v-for="(
                                                label, value
                                            ) in medical.user_types || {}"
                                            :key="value"
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.user_type"
                                        class="text-xs"
                                    />
                                </div>

                                <!-- Contact Status -->
                                <div
                                    class="bg-green-50 border border-green-100 rounded-xl p-3 sm:p-4 mt-4 sm:mt-6"
                                >
                                    <p
                                        class="text-xs font-medium text-green-800 uppercase tracking-wide mb-2"
                                    >
                                        Contact Status
                                    </p>
                                    <div class="space-y-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xs sm:text-sm text-green-700 truncate"
                                                >Email Verification</span
                                            >
                                            <span
                                                v-if="user.email_verified_at"
                                                class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2"
                                            >
                                                Verified
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full flex-shrink-0 ml-2"
                                            >
                                                Pending
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xs sm:text-sm text-green-700 truncate"
                                                >Phone Verification</span
                                            >
                                            <span
                                                v-if="user.phone_verified_at"
                                                class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex-shrink-0 ml-2"
                                            >
                                                Verified
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full flex-shrink-0 ml-2"
                                            >
                                                Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div
                        v-show="activeSection === 'professional'"
                        class="space-y-4 sm:space-y-6 animate-fadeIn"
                    >
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6"
                        >
                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="occupation"
                                        value="Occupation"
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="occupation"
                                        v-model="form.occupation"
                                        type="text"
                                        class="mt-1 w-full rounded-md text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.occupation,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.occupation"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="work_address"
                                        value="Work Address"
                                        class="text-xs sm:text-sm"
                                    />
                                    <textarea
                                        id="work_address"
                                        v-model="form.work_address"
                                        rows="3"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.work_address,
                                        }"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.work_address"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="industry"
                                        value="Industry"
                                        class="text-xs sm:text-sm"
                                    />
                                    <TextInput
                                        id="industry"
                                        v-model="form.industry"
                                        type="text"
                                        class="mt-1 w-full rounded-md text-sm sm:text-base"
                                    />
                                    <InputError
                                        :message="form.errors.industry"
                                        class="text-xs"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="religion"
                                        value="Religion"
                                        class="text-xs sm:text-sm"
                                    />
                                    <select
                                        id="religion"
                                        v-model="form.religion"
                                        class="mt-1 w-full rounded-md text-sm sm:text-base"
                                    >
                                        <option value="">
                                            Select religion
                                        </option>
                                        <option
                                            v-for="religion in religions || []"
                                            :key="religion"
                                            :value="religion"
                                        >
                                            {{ religion }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.religion"
                                        class="text-xs"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="spoken_languages"
                                        value="Spoken languages (one per line)"
                                        class="text-xs sm:text-sm"
                                    />
                                    <textarea
                                        id="spoken_languages"
                                        v-model="form.spoken_languages_text"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        :class="{
                                            'border-red-300':
                                                form.errors.spoken_languages,
                                        }"
                                        placeholder="English
                                        French
                                        Spanish"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.spoken_languages"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.spoken_languages }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Enter each spoken language on a new line
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information Section -->
                    <div
                        v-show="activeSection === 'location'"
                        class="space-y-4 sm:space-y-6 animate-fadeIn"
                    >
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6"
                        >
                            <div class="space-y-3 sm:space-y-4">
                                <div>
                                    <InputLabel
                                        for="residential_address"
                                        value="Residential Address"
                                        class="text-xs sm:text-sm"
                                    />
                                    <textarea
                                        id="residential_address"
                                        v-model="form.residential_address"
                                        rows="4"
                                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 transition duration-200 text-sm sm:text-base"
                                        :class="{
                                            'border-red-300':
                                                form.errors.residential_address,
                                        }"
                                    >
                                            user.residential_address
                                        </textarea
                                    >
                                    <InputError
                                        :message="
                                            form.errors.residential_address
                                        "
                                        class="text-xs"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-1 gap-3 sm:gap-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="nationality_id"
                                            value="Nationality"
                                            class="text-xs sm:text-sm"
                                        />
                                        <select
                                            id="nationality_id"
                                            v-model="form.nationality_id"
                                            class="mt-1 w-full text-sm sm:text-base rounded-md"
                                            :class="{
                                                'border-red-300':
                                                    form.errors.nationality_id,
                                            }"
                                        >
                                            <option>Select Nationality</option>
                                            <option
                                                v-for="nationality in nationalities"
                                                :key="nationality.id"
                                                :value="nationality.id"
                                            >
                                                {{ nationality.name }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                form.errors.nationality_id
                                            "
                                            class="text-xs"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="state_id"
                                            value="State"
                                            class="text-xs sm:text-sm"
                                        />
                                        <select
                                            id="state_id"
                                            v-model="form.state_id"
                                            class="mt-1 w-full rounded-lg text-sm sm:text-base"
                                            :class="{
                                                'border-red-300':
                                                    form.errors.state_id,
                                            }"
                                        >
                                            <option>Select state</option>
                                            <option
                                                v-for="state in states"
                                                :key="state.id"
                                                :value="state.id"
                                            >
                                                {{ state.name }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="form.errors.state_id"
                                            class="text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="pt-4 sm:pt-6 border-t border-gray-200">
                    <div
                        class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4"
                    >
                        <div class="text-xs sm:text-sm text-gray-500">
                            <span class="font-medium">Tip:</span> Complete all
                            sections for better profile completion
                        </div>
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <button
                                type="button"
                                @click="cancelEditing"
                                class="px-3 py-2 sm:px-4 sm:py-2.5 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 flex-shrink-0"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow flex-shrink-0"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin -ml-1 mr-2 h-3 w-3 sm:h-4 sm:w-4 text-white flex-shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <svg
                                    v-else
                                    class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <span class="truncate">{{
                                    form.processing
                                        ? "Saving..."
                                        : "Save Changes"
                                }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Read-only View -->
            <div v-else class="space-y-4 sm:space-y-6">
                <!-- Summary Cards - Mobile Optimized -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Basic Info Card -->
                    <div
                        class="bg-blue-50 border border-blue-100 rounded-xl p-3 sm:p-4 hover:shadow-sm transition-shadow duration-200"
                    >
                        <div class="flex items-center mb-2 sm:mb-3">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"
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
                            </div>
                            <div class="min-w-0 flex-1">
                                <span
                                    class="text-xs sm:text-sm font-semibold text-blue-800 truncate"
                                    >Basic Info</span
                                >
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <div
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate"
                                >
                                    Full Name
                                </div>
                                <div
                                    class="text-sm font-medium text-gray-900 truncate mt-0.5"
                                    :title="user.full_name || 'Not set'"
                                >
                                    {{ user.full_name || "Not set" }}
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                    >
                                        Gender
                                    </div>
                                    <div
                                        class="text-sm text-gray-900 truncate mt-0.5"
                                    >
                                        {{
                                            user.gender
                                                ? user.gender.replace("_", " ")
                                                : "Not set"
                                        }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                    >
                                        Age
                                    </div>
                                    <div
                                        class="text-sm text-gray-900 truncate mt-0.5"
                                    >
                                        {{
                                            user.age
                                                ? user.age + " yrs"
                                                : "Not set"
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="user.date_of_birth">
                                <div
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Date of Birth
                                </div>
                                <div
                                    class="text-sm text-gray-900 truncate mt-0.5"
                                >
                                    {{
                                        new Date(
                                            user.date_of_birth
                                        ).toLocaleDateString()
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Card -->
                    <div
                        class="bg-green-50 border border-green-100 rounded-xl p-3 sm:p-4 hover:shadow-sm transition-shadow duration-200"
                    >
                        <div class="flex items-center mb-2 sm:mb-3">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 sm:w-5 sm:h-5 text-green-600"
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
                            </div>
                            <div class="min-w-0 flex-1">
                                <span
                                    class="text-xs sm:text-sm font-semibold text-green-800 truncate"
                                    >Contact</span
                                >
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <div
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Email
                                </div>
                                <div
                                    class="text-sm font-semibold text-green-900 truncate mt-0.5"
                                    :title="user.email || 'Email not set'"
                                >
                                    {{ user.email || "Email not set" }}
                                </div>
                                <div class="text-xs">
                                    <span
                                        :class="
                                            user.email_verified_at
                                                ? 'text-green-600 font-medium'
                                                : 'text-yellow-600'
                                        "
                                    >
                                        {{
                                            user.email_verified_at
                                                ? "✓ Verified"
                                                : "Pending verification"
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Phone
                                </div>
                                <div
                                    class="text-sm text-green-700 truncate mt-0.5"
                                    :title="user.phone || 'Phone not set'"
                                >
                                    {{ user.phone || "Phone not set" }}
                                </div>
                                <div class="text-xs">
                                    <span
                                        :class="
                                            user.phone_verified_at
                                                ? 'text-green-600 font-medium'
                                                : 'text-yellow-600'
                                        "
                                    >
                                        {{
                                            user.phone_verified_at
                                                ? "✓ Verified"
                                                : "Pending verification"
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Information - Mobile Optimized -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Work Details -->
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-center">
                            <div
                                class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-3 h-3 sm:w-4 sm:h-4 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <h4
                                class="text-sm sm:text-base font-semibold text-gray-900"
                            >
                                Work Details
                            </h4>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <dl
                                class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3"
                            >
                                <div
                                    v-if="user.occupation"
                                    class="space-y-1 sm:col-span-2"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Occupation
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900 truncate"
                                        :title="user.occupation"
                                    >
                                        {{ user.occupation }}
                                    </dd>
                                </div>

                                <div v-if="user.industry" class="space-y-1">
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Industry
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 truncate"
                                        :title="user.industry"
                                    >
                                        {{ user.industry }}
                                    </dd>
                                </div>

                                <div
                                    v-if="user.user_type_label"
                                    class="space-y-1"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        User Type
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 truncate"
                                        :title="user.user_type_label"
                                    >
                                        {{ user.user_type_label }}
                                    </dd>
                                </div>

                                <div v-if="user.religion" class="space-y-1">
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Religion
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 truncate"
                                        :title="user.religion"
                                    >
                                        {{ user.religion }}
                                    </dd>
                                </div>

                                <div
                                    v-if="user.spoken_languages?.length"
                                    class="space-y-1 sm:col-span-2"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Languages
                                    </dt>
                                    <dd
                                        class="flex flex-wrap gap-1 sm:gap-1.5 mt-1"
                                    >
                                        <span
                                            v-for="(
                                                language, index
                                            ) in user.spoken_languages"
                                            :key="index"
                                            class="px-1.5 py-0.5 sm:px-2 sm:py-1 text-xs font-medium bg-white border border-gray-200 rounded-md truncate max-w-[120px] sm:max-w-none"
                                            :title="language"
                                        >
                                            {{ language }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-center">
                            <div
                                class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-3 h-3 sm:w-4 sm:h-4 text-purple-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </div>
                            <h4
                                class="text-sm sm:text-base font-semibold text-gray-900"
                            >
                                Addresses
                            </h4>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <dl
                                class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3"
                            >
                                <div
                                    v-if="user.work_address"
                                    class="space-y-1 sm:col-span-2"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Work Address
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 break-words line-clamp-2"
                                    >
                                        {{ user.work_address }}
                                    </dd>
                                </div>

                                <div
                                    v-if="user.residential_address"
                                    class="space-y-1 sm:col-span-2"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Home Address
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 break-words line-clamp-2"
                                    >
                                        {{ user.residential_address }}
                                    </dd>
                                </div>

                                <div
                                    v-if="
                                        user.region ||
                                        user.county ||
                                        user.district
                                    "
                                    class="space-y-1 sm:col-span-2"
                                >
                                    <dt
                                        class="text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Location
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900 truncate"
                                        :title="
                                            [
                                                user.region,
                                                user.county,
                                                user.district,
                                            ]
                                                .filter(Boolean)
                                                .join(', ')
                                        "
                                    >
                                        {{
                                            [
                                                user.region,
                                                user.county,
                                                user.district,
                                            ]
                                                .filter(Boolean)
                                                .join(", ")
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Animation for section transitions */
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

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

/* Custom scrollbar for section navigation */
::-webkit-scrollbar {
    height: 3px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .flex-col-reverse {
        flex-direction: column-reverse;
    }

    /* Better line clamping for mobile */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
}

/* Extra small screen optimizations */
@media (max-width: 400px) {
    .space-y-4 > * + * {
        margin-top: 0.75rem;
    }

    .p-3 {
        padding: 0.75rem;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    .text-sm {
        font-size: 0.8rem;
    }
}

/* Prevent long text from breaking layout */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Better word breaking for addresses */
.break-words {
    word-break: break-word;
    overflow-wrap: break-word;
}

/* Ensure form elements are tap-friendly on mobile */
button,
select,
input,
textarea {
    min-height: 44px;
}

/* Fix for iOS zoom on input focus */
@media (max-width: 768px) {
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="date"],
    select,
    textarea {
        font-size: 16px;
    }
}
</style>
