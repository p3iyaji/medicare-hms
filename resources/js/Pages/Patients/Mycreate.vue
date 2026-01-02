<script setup>
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, watch, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";

// Mobile tab selection
const selectedTab = ref(1);

const scrollToTab = (tabId) => {
    const tabElement = document.querySelector(`[data-tab="${tabId}"]`);
    if (tabElement) {
        tabElement.scrollIntoView({ behavior: "smooth", block: "start" });
    }
};

const props = defineProps({
    patient: {
        type: Object,
        default: null,
    },
    nationalities: {
        type: Array,
        default: () => [],
    },
    states: {
        type: Array,
        default: () => [],
    },
    ethnicRegions: {
        type: Array,
        default: () => [],
    },
    physicians: {
        type: Array,
        default: () => [],
    },
    genders: {
        type: Array,
        default: () => [],
    },
    titles: {
        type: Array,
        default: () => [],
    },
    bloodTypes: {
        type: Array,
        default: () => ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"],
    },
    genotypes: {
        type: Array,
        default: () => ["AA", "AS", "AC", "SC", "SS", "CC"],
    },
    religions: {
        type: Array,
        default: () => ["Christianity", "Islam", "Traditional", "Other"],
    },
    spokenLanguages: {
        type: Array,
        default: () => [
            "English",
            "French",
            "Spanish",
            "Portuguese",
            "Arabic",
            "Swahili",
        ],
    },
});

const isEditing = computed(() => props.patient !== null);
const pageTitle = computed(() =>
    isEditing.value ? "Edit Patient" : "Create New Patient"
);
const pageSubtitle = computed(() =>
    isEditing.value ? "Update patient information" : "Register a new patient"
);

const form = useForm({
    // Basic Information
    title: props.patient?.title || "",
    first_name: props.patient?.first_name || "",
    middle_name: props.patient?.middle_name || "",
    last_name: props.patient?.last_name || "",
    email: props.patient?.email || "",
    phone: props.patient?.phone || "",
    password: "",
    password_confirmation: "",

    // Demographics
    date_of_birth: props.patient?.date_of_birth || "",
    gender: props.patient?.gender || "",
    nationality_id: props.patient?.nationality_id || "",
    state_id: props.patient?.state_id || "",
    ethinic_region_id: props.patient?.ethinic_region_id || "",
    religion: props.patient?.religion || "",
    spoken_languages: props.patient?.spoken_languages || [],

    // Address
    region: props.patient?.region || "",
    county: props.patient?.county || "",
    district: props.patient?.district || "",
    residential_address: props.patient?.residential_address || "",

    // Occupation
    occupation: props.patient?.occupation || "",
    work_address: props.patient?.work_address || "",
    industry: props.patient?.industry || "",

    // Medical Profile
    blood_type: props.patient?.profile?.blood_type || "",
    genotype: props.patient?.profile?.genotype || "",
    height: props.patient?.profile?.height || "",
    weight: props.patient?.profile?.weight || "",
    allergies: props.patient?.profile?.allergies || [],
    chronic_conditions: props.patient?.profile?.chronic_conditions || [],

    // Emergency Contact
    emergency_contact_name:
        props.patient?.profile?.emergency_contact_name || "",
    emergency_contact_number:
        props.patient?.profile?.emergency_contact_number || "",
    emergency_contact_relationship:
        props.patient?.profile?.emergency_contact_relationship || "",
    emergency_contact_address:
        props.patient?.profile?.emergency_contact_address || "",
    same_as_users_address:
        props.patient?.profile?.same_as_users_address || false,

    // Insurance
    insurance_provider: props.patient?.profile?.insurance_provider || "",
    insurance_policy_number:
        props.patient?.profile?.insurance_policy_number || "",

    // Primary Physician
    primary_physician_id: props.patient?.profile?.primary_physician_id || "",

    // Account Settings
    is_active: props.patient?.is_active ?? true,
    is_verified: props.patient?.is_verified ?? true,
    mfa_enabled: props.patient?.mfa_enabled ?? false,
});

// Form tabs
const tabs = [
    { id: 1, name: "Basic Info" },
    { id: 2, name: "Demographics" },
    { id: 3, name: "Medical Profile" },
    { id: 4, name: "Emergency Contact" },
    { id: 5, name: "Account Settings" },
];

// Multi-select handling for arrays
const allergyInput = ref("");
const conditionInput = ref("");
const selectedLanguageInput = ref("");

const addAllergy = () => {
    if (
        allergyInput.value.trim() &&
        !form.allergies.includes(allergyInput.value.trim())
    ) {
        form.allergies = [...form.allergies, allergyInput.value.trim()];
        allergyInput.value = "";
    }
};

const removeAllergy = (index) => {
    form.allergies.splice(index, 1);
};

const addCondition = () => {
    if (
        conditionInput.value.trim() &&
        !form.chronic_conditions.includes(conditionInput.value.trim())
    ) {
        form.chronic_conditions = [
            ...form.chronic_conditions,
            conditionInput.value.trim(),
        ];
        conditionInput.value = "";
    }
};

const removeCondition = (index) => {
    form.chronic_conditions.splice(index, 1);
};

const toggleLanguage = (language) => {
    const index = form.spoken_languages.indexOf(language);
    if (index > -1) {
        form.spoken_languages.splice(index, 1);
    } else {
        form.spoken_languages = [...form.spoken_languages, language];
    }
};

// Age calculation
const age = computed(() => {
    if (!form.date_of_birth) return null;
    const birthDate = new Date(form.date_of_birth);
    const today = new Date();
    let calculatedAge = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birthDate.getDate())
    ) {
        calculatedAge--;
    }
    return calculatedAge;
});

// BMI calculation
const bmi = computed(() => {
    if (!form.height || !form.weight) return null;
    const heightInMeters = parseFloat(form.height) / 100;
    const weight = parseFloat(form.weight);
    return (weight / (heightInMeters * heightInMeters)).toFixed(1);
});

const bmiCategory = computed(() => {
    if (!bmi.value) return "";
    const bmiValue = parseFloat(bmi.value);
    if (bmiValue < 18.5) return "Underweight";
    if (bmiValue < 25) return "Normal";
    if (bmiValue < 30) return "Overweight";
    return "Obese";
});

// Emergency address sync
const syncEmergencyAddress = () => {
    if (form.same_as_users_address) {
        form.emergency_contact_address = form.residential_address;
    }
};

watch(() => form.residential_address, syncEmergencyAddress);
watch(
    () => form.same_as_users_address,
    (value) => {
        if (value) {
            form.emergency_contact_address = form.residential_address;
        }
    }
);

// Form submission
const submitForm = () => {
    if (isEditing.value) {
        form.put(route("patients.update", props.patient.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            },
        });
    } else {
        form.post(route("patients.store"), {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            },
        });
    }
};

// Cancel handler
const handleCancel = () => {
    router.visit(route("patients.index"));
};

// Delete confirmation
const showDeleteModal = ref(false);
const confirmDelete = () => {
    if (props.patient) {
        router.delete(route("patients.destroy", props.patient.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                router.visit(route("patients.index"));
            },
        });
    }
};

// Password generation
const generatePassword = () => {
    const chars =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
    let password = "";
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.password = password;
    form.password_confirmation = password;
};

// Copy password to clipboard
const copyPassword = async () => {
    if (form.password) {
        await navigator.clipboard.writeText(form.password);
        // You could add a toast notification here
    }
};

// Patient number generation (if creating new)
const generatePatientNumber = () => {
    if (!isEditing.value) {
        const prefix = "PAT";
        const timestamp = Date.now().toString().slice(-6);
        const random = Math.floor(Math.random() * 1000)
            .toString()
            .padStart(3, "0");
        return `${prefix}${timestamp}${random}`;
    }
    return props.patient?.patient_no;
};
</script>

<template>
    <AppLayout :title="pageTitle" :subtitle="pageSubtitle">
        <template #headerActions>
            <div class="flex flex-wrap gap-2">
                <SecondaryButton @click="handleCancel" class="mr-2">
                    <span class="hidden sm:inline">Cancel</span>
                    <span class="sm:hidden">←</span>
                </SecondaryButton>
                <PrimaryButton @click="submitForm" :disabled="form.processing">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>
                        <span class="hidden sm:inline">{{
                            isEditing ? "Update" : "Create"
                        }}</span>
                        <span class="sm:hidden">✓</span>
                    </span>
                </PrimaryButton>
                <DangerButton
                    v-if="isEditing"
                    @click="showDeleteModal = true"
                    class="ml-2"
                >
                    <span class="hidden sm:inline">Delete</span>
                    <span class="sm:hidden">🗑️</span>
                </DangerButton>
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Patient Number Display -->
            <div
                v-if="isEditing"
                class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-blue-50"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0"
                >
                    <div class="flex items-center">
                        <svg
                            class="w-4 h-4 text-blue-600 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                            />
                        </svg>
                        <div>
                            <span
                                class="text-xs sm:text-sm font-medium text-gray-700"
                                >Patient ID:</span
                            >
                            <span
                                class="ml-1 sm:ml-2 font-semibold text-blue-700 text-sm sm:text-base"
                                >{{ patient.patient_no }}</span
                            >
                        </div>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-600">
                        Created
                        {{ new Date(patient.created_at).toLocaleDateString() }}
                    </div>
                </div>
            </div>

            <!-- Mobile Tab Navigation -->
            <div
                class="sm:hidden px-4 py-3 border-b border-gray-200 bg-gray-50"
            >
                <label for="mobile-tab" class="sr-only">Select tab</label>
                <select
                    id="mobile-tab"
                    v-model="selectedTab"
                    @change="scrollToTab($event.target.value)"
                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                >
                    <option v-for="tab in tabs" :key="tab.id" :value="tab.id">
                        {{ tab.name }}
                    </option>
                </select>
            </div>

            <!-- Wrap everything in TabGroup for desktop -->
            <TabGroup as="div">
                <!-- Desktop Tab Navigation -->
                <TabList
                    class="hidden sm:flex overflow-x-auto px-4 sm:px-6 scrollbar-hide border-b border-gray-200"
                >
                    <Tab
                        v-for="tab in tabs"
                        :key="tab.id"
                        v-slot="{ selected }"
                        as="template"
                    >
                        <button
                            :class="[
                                selected
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'py-3 px-2 sm:px-3 border-b-2 font-medium text-sm whitespace-nowrap flex-shrink-0',
                            ]"
                        >
                            {{ tab.name }}
                        </button>
                    </Tab>
                </TabList>

                <TabPanels>
                    <!-- Tab 1: Basic Information -->
                    <TabPanel class="px-4 sm:px-6 py-4 sm:py-6" :data-tab="1">
                        <div
                            class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 sm:gap-4 lg:gap-6"
                        >
                            <!-- Title -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="title"
                                    value="Title"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="title"
                                    v-model="form.title"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Title</option>
                                    <option
                                        v-for="titleOption in titles"
                                        :key="titleOption"
                                        :value="titleOption"
                                    >
                                        {{ titleOption }}
                                    </option>
                                </SelectInput>
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.title"
                                    >{{ form.errors.title }}</span
                                >
                            </div>

                            <!-- First Name -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="first_name"
                                    value="First Name *"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    required
                                    class="w-full text-sm sm:text-base"
                                />
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.first_name"
                                    >{{ form.errors.first_name }}</span
                                >
                            </div>

                            <!-- Middle Name -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="middle_name"
                                    value="Middle Name"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="middle_name"
                                    v-model="form.middle_name"
                                    type="text"
                                    class="w-full text-sm sm:text-base"
                                />
                            </div>

                            <!-- Last Name -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="last_name"
                                    value="Last Name *"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    required
                                    class="w-full text-sm sm:text-base"
                                />
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.last_name"
                                    >{{ form.errors.last_name }}</span
                                >
                            </div>

                            <!-- Email -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="email"
                                    value="Email Address *"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full text-sm sm:text-base"
                                />
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.email"
                                    >{{ form.errors.email }}</span
                                >
                            </div>

                            <!-- Phone -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="phone"
                                    value="Phone Number *"
                                    class="text-sm sm:text-base"
                                />
                                <div class="flex items-center">
                                    <span class="text-gray-500 mr-2">+</span>
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        required
                                        class="w-full text-sm sm:text-base"
                                    />
                                </div>
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.phone"
                                    >{{ form.errors.phone }}</span
                                >
                            </div>

                            <!-- Password Fields (only for new patients) -->
                            <template v-if="!isEditing">
                                <div class="space-y-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <InputLabel
                                            for="password"
                                            value="Password *"
                                            class="text-sm sm:text-base"
                                        />
                                        <button
                                            type="button"
                                            @click="generatePassword"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                        >
                                            Generate
                                        </button>
                                    </div>
                                    <div class="flex space-x-2">
                                        <TextInput
                                            id="password"
                                            v-model="form.password"
                                            type="text"
                                            required
                                            class="w-full text-sm sm:text-base"
                                        />
                                        <button
                                            v-if="form.password"
                                            type="button"
                                            @click="copyPassword"
                                            class="px-2 sm:px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded hover:bg-gray-50 whitespace-nowrap"
                                        >
                                            Copy
                                        </button>
                                    </div>
                                    <span
                                        class="text-xs text-red-600"
                                        v-if="form.errors.password"
                                        >{{ form.errors.password }}</span
                                    >
                                </div>

                                <div class="space-y-1">
                                    <InputLabel
                                        for="password_confirmation"
                                        value="Confirm Password *"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="text"
                                        required
                                        class="w-full text-sm sm:text-base"
                                    />
                                </div>
                            </template>
                        </div>
                    </TabPanel>

                    <!-- Tab 2: Demographics -->
                    <TabPanel class="px-4 sm:px-6 py-4 sm:py-6" :data-tab="2">
                        <div
                            class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 sm:gap-4 lg:gap-6"
                        >
                            <!-- Date of Birth -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="date_of_birth"
                                    value="Date of Birth *"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="date_of_birth"
                                    v-model="form.date_of_birth"
                                    type="date"
                                    required
                                    class="w-full text-sm sm:text-base"
                                />
                                <div
                                    v-if="age"
                                    class="text-xs sm:text-sm text-gray-600 mt-1 flex items-center"
                                >
                                    <svg
                                        class="w-4 h-4 mr-1 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"
                                        />
                                    </svg>
                                    Age: {{ age }} years
                                </div>
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.date_of_birth"
                                    >{{ form.errors.date_of_birth }}</span
                                >
                            </div>

                            <!-- Gender -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="gender"
                                    value="Gender *"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="gender"
                                    v-model="form.gender"
                                    required
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Gender</option>
                                    <option
                                        v-for="gender in genders"
                                        :key="gender"
                                        :value="gender"
                                    >
                                        {{ gender.replaceAll("_", " ") }}
                                    </option>
                                </SelectInput>
                                <span
                                    class="text-xs text-red-600"
                                    v-if="form.errors.gender"
                                    >{{ form.errors.gender }}</span
                                >
                            </div>

                            <!-- Nationality -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="nationality_id"
                                    value="Nationality"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="nationality_id"
                                    v-model="form.nationality_id"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Nationality</option>
                                    <option
                                        v-for="nationality in nationalities"
                                        :key="nationality.id"
                                        :value="nationality.id"
                                    >
                                        {{ nationality.name }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- State -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="state_id"
                                    value="State/Province"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="state_id"
                                    v-model="form.state_id"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select State</option>
                                    <option
                                        v-for="state in states"
                                        :key="state.id"
                                        :value="state.id"
                                    >
                                        {{ state.name }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Ethnic Region -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="ethinic_region_id"
                                    value="Ethnic Region"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="ethinic_region_id"
                                    v-model="form.ethinic_region_id"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">
                                        Select Ethnic Region
                                    </option>
                                    <option
                                        v-for="region in ethnicRegions"
                                        :key="region.id"
                                        :value="region.id"
                                    >
                                        {{ region.name }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Religion -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="religion"
                                    value="Religion"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="religion"
                                    v-model="form.religion"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Religion</option>
                                    <option
                                        v-for="religionOption in religions"
                                        :key="religionOption"
                                        :value="religionOption"
                                    >
                                        {{ religionOption }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Spoken Languages -->
                            <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                <InputLabel
                                    value="Spoken Languages"
                                    class="text-sm sm:text-base"
                                />
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2"
                                >
                                    <div
                                        v-for="language in spokenLanguages"
                                        :key="language"
                                        class="flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="'lang_' + language"
                                            :value="language"
                                            :checked="
                                                form.spoken_languages.includes(
                                                    language
                                                )
                                            "
                                            @change="toggleLanguage(language)"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <label
                                            :for="'lang_' + language"
                                            class="ml-2 text-xs sm:text-sm text-gray-700 truncate"
                                        >
                                            {{ language }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Region -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="region"
                                    value="Region"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="region"
                                    v-model="form.region"
                                    type="text"
                                    class="w-full text-sm sm:text-base"
                                />
                            </div>

                            <!-- County -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="county"
                                    value="County"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="county"
                                    v-model="form.county"
                                    type="text"
                                    class="w-full text-sm sm:text-base"
                                />
                            </div>

                            <!-- District -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="district"
                                    value="District"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="district"
                                    v-model="form.district"
                                    type="text"
                                    class="w-full text-sm sm:text-base"
                                />
                            </div>

                            <!-- Residential Address -->
                            <div class="space-y-1 md:col-span-2 lg:col-span-3">
                                <InputLabel
                                    for="residential_address"
                                    value="Residential Address"
                                    class="text-sm sm:text-base"
                                />
                                <textarea
                                    id="residential_address"
                                    v-model="form.residential_address"
                                    rows="2"
                                    class="w-full text-sm sm:text-base border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    placeholder="Enter full address"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Occupation Section -->
                        <div
                            class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-200"
                        >
                            <h4
                                class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-2 text-gray-400"
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
                                Occupation Information
                            </h4>
                            <div
                                class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 sm:gap-4 lg:gap-6"
                            >
                                <div class="space-y-1">
                                    <InputLabel
                                        for="occupation"
                                        value="Occupation"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="occupation"
                                        v-model="form.occupation"
                                        type="text"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="Job title"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <InputLabel
                                        for="industry"
                                        value="Industry"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="industry"
                                        v-model="form.industry"
                                        type="text"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="Industry sector"
                                    />
                                </div>

                                <div
                                    class="space-y-1 md:col-span-2 lg:col-span-3"
                                >
                                    <InputLabel
                                        for="work_address"
                                        value="Work Address"
                                        class="text-sm sm:text-base"
                                    />
                                    <textarea
                                        id="work_address"
                                        v-model="form.work_address"
                                        rows="2"
                                        class="w-full text-sm sm:text-base border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                        placeholder="Work location address"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Tab 3: Medical Profile -->
                    <TabPanel class="px-4 sm:px-6 py-4 sm:py-6" :data-tab="3">
                        <div
                            class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 sm:gap-4 lg:gap-6"
                        >
                            <!-- Blood Type -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="blood_type"
                                    value="Blood Type"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="blood_type"
                                    v-model="form.blood_type"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Blood Type</option>
                                    <option
                                        v-for="type in bloodTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ type }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Genotype -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="genotype"
                                    value="Genotype"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="genotype"
                                    v-model="form.genotype"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">Select Genotype</option>
                                    <option
                                        v-for="genotypeOption in genotypes"
                                        :key="genotypeOption"
                                        :value="genotypeOption"
                                    >
                                        {{ genotypeOption }}
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Height & Weight Group for mobile -->
                            <div
                                class="grid grid-cols-2 gap-3 sm:gap-4 md:col-span-1 lg:col-span-2"
                            >
                                <!-- Height -->
                                <div class="space-y-1">
                                    <InputLabel
                                        for="height"
                                        value="Height (cm)"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="height"
                                        v-model="form.height"
                                        type="number"
                                        step="0.1"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="170"
                                    />
                                </div>

                                <!-- Weight -->
                                <div class="space-y-1">
                                    <InputLabel
                                        for="weight"
                                        value="Weight (kg)"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="weight"
                                        v-model="form.weight"
                                        type="number"
                                        step="0.1"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="70"
                                    />
                                </div>
                            </div>

                            <!-- BMI Display -->
                            <div
                                v-if="bmi"
                                class="bg-gray-50 rounded-lg p-3 sm:p-4 md:col-span-2 lg:col-span-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div
                                            class="text-xs sm:text-sm font-medium text-gray-700"
                                        >
                                            Body Mass Index (BMI)
                                        </div>
                                        <div class="flex items-baseline mt-1">
                                            <div
                                                class="text-xl sm:text-2xl font-bold mr-2"
                                            >
                                                {{ bmi }}
                                            </div>
                                            <div
                                                :class="[
                                                    'text-xs sm:text-sm font-semibold px-2 py-1 rounded-full',
                                                    bmiCategory ===
                                                    'Underweight'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : bmiCategory ===
                                                          'Normal'
                                                        ? 'bg-green-100 text-green-800'
                                                        : bmiCategory ===
                                                          'Overweight'
                                                        ? 'bg-orange-100 text-orange-800'
                                                        : 'bg-red-100 text-red-800',
                                                ]"
                                            >
                                                {{ bmiCategory }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="hidden sm:block text-xs text-gray-500"
                                    >
                                        Height: {{ form.height || 0 }}cm<br />
                                        Weight: {{ form.weight || 0 }}kg
                                    </div>
                                </div>
                            </div>

                            <!-- Primary Physician -->
                            <div class="space-y-1 md:col-span-2 lg:col-span-3">
                                <InputLabel
                                    for="primary_physician_id"
                                    value="Primary Physician"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="primary_physician_id"
                                    v-model="form.primary_physician_id"
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">
                                        Select Primary Physician
                                    </option>
                                    <option
                                        v-for="physician in physicians"
                                        :key="physician.id"
                                        :value="physician.id"
                                    >
                                        {{ physician.title }}
                                        {{ physician.first_name }}
                                        {{ physician.last_name }}
                                        <template
                                            v-if="
                                                physician.specializations &&
                                                physician.specializations
                                                    .length > 0
                                            "
                                        >
                                            ({{
                                                physician.specializations
                                                    .map((s) => s.name)
                                                    .join(", ")
                                            }})
                                        </template>
                                    </option>
                                </SelectInput>
                            </div>

                            <!-- Allergies -->
                            <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                <div class="flex items-center justify-between">
                                    <InputLabel
                                        value="Allergies"
                                        class="text-sm sm:text-base"
                                    />
                                    <span class="text-xs text-gray-500"
                                        >Add one at a time</span
                                    >
                                </div>
                                <div class="flex space-x-2">
                                    <TextInput
                                        v-model="allergyInput"
                                        type="text"
                                        placeholder="Enter allergy"
                                        class="w-full text-sm sm:text-base"
                                        @keyup.enter="addAllergy"
                                    />
                                    <SecondaryButton
                                        @click="addAllergy"
                                        type="button"
                                        class="px-3 sm:px-4"
                                    >
                                        Add
                                    </SecondaryButton>
                                </div>
                                <div
                                    v-if="form.allergies.length > 0"
                                    class="flex flex-wrap gap-2 mt-2"
                                >
                                    <div
                                        v-for="(
                                            allergy, index
                                        ) in form.allergies"
                                        :key="index"
                                        class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm bg-red-50 text-red-700 border border-red-200"
                                    >
                                        {{ allergy }}
                                        <button
                                            @click="removeAllergy(index)"
                                            class="ml-1 text-red-600 hover:text-red-800"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Chronic Conditions -->
                            <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                <div class="flex items-center justify-between">
                                    <InputLabel
                                        value="Chronic Conditions"
                                        class="text-sm sm:text-base"
                                    />
                                    <span class="text-xs text-gray-500"
                                        >Add one at a time</span
                                    >
                                </div>
                                <div class="flex space-x-2">
                                    <TextInput
                                        v-model="conditionInput"
                                        type="text"
                                        placeholder="Enter condition"
                                        class="w-full text-sm sm:text-base"
                                        @keyup.enter="addCondition"
                                    />
                                    <SecondaryButton
                                        @click="addCondition"
                                        type="button"
                                        class="px-3 sm:px-4"
                                    >
                                        Add
                                    </SecondaryButton>
                                </div>
                                <div
                                    v-if="form.chronic_conditions.length > 0"
                                    class="flex flex-wrap gap-2 mt-2"
                                >
                                    <div
                                        v-for="(
                                            condition, index
                                        ) in form.chronic_conditions"
                                        :key="index"
                                        class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm bg-orange-50 text-orange-700 border border-orange-200"
                                    >
                                        {{ condition }}
                                        <button
                                            @click="removeCondition(index)"
                                            class="ml-1 text-orange-600 hover:text-orange-800"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Tab 4: Emergency Contact -->
                    <TabPanel class="px-4 sm:px-6 py-4 sm:py-6" :data-tab="4">
                        <div
                            class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 sm:gap-4 md:gap-6"
                        >
                            <!-- Emergency Contact Name -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="emergency_contact_name"
                                    value="Emergency Contact Name *"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="emergency_contact_name"
                                    v-model="form.emergency_contact_name"
                                    type="text"
                                    required
                                    class="w-full text-sm sm:text-base"
                                />
                            </div>

                            <!-- Relationship -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="emergency_contact_relationship"
                                    value="Relationship *"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="emergency_contact_relationship"
                                    v-model="
                                        form.emergency_contact_relationship
                                    "
                                    required
                                    class="w-full text-sm sm:text-base"
                                >
                                    <option value="">
                                        Select Relationship
                                    </option>
                                    <option value="Spouse">Spouse</option>
                                    <option value="Parent">Parent</option>
                                    <option value="Child">Child</option>
                                    <option value="Sibling">Sibling</option>
                                    <option value="Friend">Friend</option>
                                    <option value="Other">Other</option>
                                </SelectInput>
                            </div>

                            <!-- Emergency Contact Number -->
                            <div class="space-y-1">
                                <InputLabel
                                    for="emergency_contact_number"
                                    value="Emergency Phone Number *"
                                    class="text-sm sm:text-base"
                                />
                                <div class="flex items-center">
                                    <span class="text-gray-500 mr-2">+</span>
                                    <TextInput
                                        id="emergency_contact_number"
                                        v-model="form.emergency_contact_number"
                                        type="tel"
                                        required
                                        class="w-full text-sm sm:text-base"
                                    />
                                </div>
                            </div>

                            <!-- Same as user's address checkbox -->
                            <div
                                class="flex items-center space-y-1 md:col-span-2"
                            >
                                <input
                                    id="same_as_users_address"
                                    v-model="form.same_as_users_address"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label
                                    for="same_as_users_address"
                                    class="ml-2 text-sm text-gray-700"
                                >
                                    Same as patient's residential address
                                </label>
                            </div>

                            <!-- Emergency Contact Address -->
                            <div class="space-y-1 md:col-span-2">
                                <InputLabel
                                    for="emergency_contact_address"
                                    value="Emergency Contact Address *"
                                    class="text-sm sm:text-base"
                                />
                                <textarea
                                    id="emergency_contact_address"
                                    v-model="form.emergency_contact_address"
                                    rows="2"
                                    required
                                    :disabled="form.same_as_users_address"
                                    class="w-full text-sm sm:text-base border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm disabled:bg-gray-50 disabled:text-gray-500"
                                    placeholder="Emergency contact full address"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Insurance Information -->
                        <div
                            class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-200"
                        >
                            <h4
                                class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-2 text-gray-400"
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
                                Insurance Information
                            </h4>
                            <div
                                class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 sm:gap-4 md:gap-6"
                            >
                                <div class="space-y-1">
                                    <InputLabel
                                        for="insurance_provider"
                                        value="Insurance Provider"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="insurance_provider"
                                        v-model="form.insurance_provider"
                                        type="text"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="Insurance company name"
                                    />
                                </div>

                                <div class="space-y-1">
                                    <InputLabel
                                        for="insurance_policy_number"
                                        value="Policy Number"
                                        class="text-sm sm:text-base"
                                    />
                                    <TextInput
                                        id="insurance_policy_number"
                                        v-model="form.insurance_policy_number"
                                        type="text"
                                        class="w-full text-sm sm:text-base"
                                        placeholder="Policy/ID number"
                                    />
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Tab 5: Account Settings -->
                    <TabPanel class="px-4 sm:px-6 py-4 sm:py-6" :data-tab="5">
                        <div
                            class="space-y-6 sm:space-y-0 sm:grid sm:grid-cols-1 md:grid-cols-2 sm:gap-4 md:gap-6"
                        >
                            <!-- Account Status -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="is_active"
                                    value="Account Status"
                                    class="text-sm sm:text-base"
                                />
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input
                                            id="is_active_true"
                                            v-model="form.is_active"
                                            :value="true"
                                            type="radio"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <label
                                            for="is_active_true"
                                            class="ml-3 text-sm text-gray-700"
                                        >
                                            <div class="flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2"
                                                >
                                                    Active
                                                </span>
                                                <span class="text-gray-600"
                                                    >Patient can log in</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input
                                            id="is_active_false"
                                            v-model="form.is_active"
                                            :value="false"
                                            type="radio"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <label
                                            for="is_active_false"
                                            class="ml-3 text-sm text-gray-700"
                                        >
                                            <div class="flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2"
                                                >
                                                    Inactive
                                                </span>
                                                <span class="text-gray-600"
                                                    >Patient cannot log in</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Verification Status -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="is_verified"
                                    value="Verification Status"
                                    class="text-sm sm:text-base"
                                />
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input
                                            id="is_verified_true"
                                            v-model="form.is_verified"
                                            :value="true"
                                            type="radio"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <label
                                            for="is_verified_true"
                                            class="ml-3 text-sm text-gray-700"
                                        >
                                            <div class="flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2"
                                                >
                                                    Verified
                                                </span>
                                                <span class="text-gray-600"
                                                    >Account is verified</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input
                                            id="is_verified_false"
                                            v-model="form.is_verified"
                                            :value="false"
                                            type="radio"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <label
                                            for="is_verified_false"
                                            class="ml-3 text-sm text-gray-700"
                                        >
                                            <div class="flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2"
                                                >
                                                    Unverified
                                                </span>
                                                <span class="text-gray-600"
                                                    >Account needs
                                                    verification</span
                                                >
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- MFA Enabled -->
                            <div class="space-y-2 md:col-span-2">
                                <div class="flex items-center justify-between">
                                    <InputLabel
                                        for="mfa_enabled"
                                        value="Multi-Factor Authentication"
                                        class="text-sm sm:text-base"
                                    />
                                    <div
                                        class="relative inline-block w-10 mr-2 align-middle select-none"
                                    >
                                        <input
                                            type="checkbox"
                                            name="mfa_enabled"
                                            id="mfa_enabled"
                                            v-model="form.mfa_enabled"
                                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                            :class="
                                                form.mfa_enabled
                                                    ? 'right-0 border-blue-600'
                                                    : 'left-0 border-gray-300'
                                            "
                                        />
                                        <label
                                            for="mfa_enabled"
                                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"
                                            :class="
                                                form.mfa_enabled
                                                    ? 'bg-blue-600'
                                                    : 'bg-gray-300'
                                            "
                                        ></label>
                                    </div>
                                </div>
                                <p
                                    class="text-xs sm:text-sm text-gray-600 mt-1"
                                >
                                    When enabled, patient will need to verify
                                    login via email or SMS code for enhanced
                                    security.
                                </p>
                            </div>

                            <!-- User Type (hidden, set to patient) -->
                            <input
                                type="hidden"
                                v-model="form.user_type"
                                value="patient"
                            />
                        </div>

                        <!-- Form Errors Summary -->
                        <div
                            v-if="form.hasErrors"
                            class="mt-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-md"
                        >
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.928-.833-2.698 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"
                                    />
                                </svg>
                                <div>
                                    <h4
                                        class="text-sm font-medium text-red-800 mb-1"
                                    >
                                        Please fix the following errors:
                                    </h4>
                                    <ul
                                        class="text-xs sm:text-sm text-red-700 space-y-1"
                                    >
                                        <li
                                            v-for="(
                                                error, field
                                            ) in form.errors"
                                            :key="field"
                                            class="flex items-start"
                                        >
                                            <span class="font-medium mr-1"
                                                >{{
                                                    field.replace("_", " ")
                                                }}:</span
                                            >
                                            <span>{{ error }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </TabPanel>
                </TabPanels>
            </TabGroup>

            <!-- Mobile Form Actions Footer -->
            <div
                class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-10"
            >
                <div class="flex space-x-2">
                    <SecondaryButton @click="handleCancel" class="flex-1">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="submitForm"
                        :disabled="form.processing"
                        class="flex-1"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>
                            {{ isEditing ? "Update" : "Create" }} Patient
                        </span>
                    </PrimaryButton>
                </div>
            </div>

            <!-- Desktop Form Actions Footer -->
            <div
                class="hidden sm:block px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0"
                >
                    <div class="text-xs sm:text-sm text-gray-600">
                        <span v-if="isEditing"
                            >Last updated:
                            {{
                                new Date(
                                    patient.updated_at
                                ).toLocaleDateString()
                            }}</span
                        >
                        <span v-else
                            ><span class="text-red-600">*</span> Required
                            fields</span
                        >
                    </div>
                    <div
                        class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3"
                    >
                        <SecondaryButton
                            @click="handleCancel"
                            class="w-full sm:w-auto"
                        >
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            @click="submitForm"
                            :disabled="form.processing"
                            class="w-full sm:w-auto"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else
                                >{{
                                    isEditing ? "Update" : "Create"
                                }}
                                Patient</span
                            >
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Spacer for mobile fixed footer -->
            <div class="h-20 sm:h-0"></div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-4 sm:p-6">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full"
                >
                    <svg
                        class="w-6 h-6 text-red-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Delete Patient Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete
                            <span class="font-semibold"
                                >{{ patient?.title }} {{ patient?.first_name }}
                                {{ patient?.last_name }}</span
                            >'s account?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All patient data will
                            be permanently removed.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-6 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3"
                >
                    <SecondaryButton
                        @click="showDeleteModal = false"
                        class="w-full sm:w-auto"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        class="w-full sm:w-auto"
                    >
                        Delete Account
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Custom toggle switch for MFA */
.toggle-checkbox:checked {
    right: 0;
    border-color: #2563eb;
}

.toggle-checkbox:checked + .toggle-label {
    background-color: #2563eb;
}

/* Hide scrollbar for tab navigation */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    :deep(input[type="date"]),
    :deep(input[type="tel"]),
    :deep(input[type="email"]),
    :deep(input[type="text"]),
    :deep(input[type="number"]),
    :deep(textarea),
    :deep(select) {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }

    :deep(.space-y-1 > * + *) {
        margin-top: 0.25rem;
    }

    :deep(.space-y-2 > * + *) {
        margin-top: 0.5rem;
    }

    :deep(.space-y-4 > * + *) {
        margin-top: 1rem;
    }
}

/* Better touch targets on mobile */
@media (max-width: 768px) {
    :deep(button),
    :deep(a),
    :deep(input[type="checkbox"]),
    :deep(input[type="radio"]) {
        min-height: 44px;
        min-width: 44px;
    }

    :deep(select) {
        min-height: 44px;
    }
}
</style>
