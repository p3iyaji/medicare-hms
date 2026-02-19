<script setup>
import { router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {
    EyeIcon,
    ClipboardDocumentListIcon,
    PauseCircleIcon,
    PlayCircleIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    patients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            status: "",
            gender: "",
            verified: "",
            date_range: "",
        }),
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });

const lastVersion = ref(null);
let timer = null;
let interval = 4000;

const pollVersion = async () => {
    const res = await fetch("/patients/version");
    const data = await res.json();

    if (data.version > lastVersion.value) {
        interval = 4000; // active = fast refresh
        router.reload({ only: ["patients"], preserveScroll: true });
    } else {
        interval = Math.min(interval + 2000, 15000); // slow down when nothing changes
    }

    clearInterval(timer);
    timer = setInterval(pollVersion, interval);
};

onMounted(() => {
    timer = setInterval(pollVersion, 4000); //every 4s
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

const genders = computed(() => page.props.medical.genders);

const welcomeMessage = computed(() => {
    return "Manage patient records and accounts";
});

const canManagePatients = computed(() =>
    ["admin", "doctor"].includes(page.props.auth?.user?.user_type),
);

const createPatient = () => {
    router.visit("/patients/create");
};

// search and filters
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const genderFilter = ref(props.filters.gender || "");
const verifiedFilter = ref(props.filters.verified || "");
const dateRangeFilter = ref(props.filters.date_range || "");

// Modal state
const showDeactivateModal = ref(false);
const showActivateModal = ref(false);
const showDeleteModal = ref(false);
const selectedPatient = ref(null);

// Processing state
const processingAction = ref(false);

// Add computed property for active filters
const hasActiveFilters = computed(() => {
    return (
        search.value ||
        statusFilter.value ||
        genderFilter.value ||
        verifiedFilter.value ||
        dateRangeFilter.value
    );
});

// Format date range for display
const formatDateRangeForDisplay = (dateRange) => {
    if (!dateRange) return "";

    const dates = dateRange.split(" to ");
    if (dates.length !== 2) return dateRange;

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
        });
    };
    return `${formatDate(dates[0])} - ${formatDate(dates[1])}`;
};

// Apply filters with debounce
let filterTimeout = null;
const applyFilters = () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    filterTimeout = setTimeout(() => {
        router.get(
            "/patients",
            {
                search: search.value,
                status: statusFilter.value,
                gender: genderFilter.value,
                verified: verifiedFilter.value,
                date_range: dateRangeFilter.value,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ["patients", "filters"],
            },
        );
    }, 300); // 300ms debounce
};

// modal state

//Filter options
const statusOptions = [
    { value: "", label: "All Status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const verifiedOptions = [
    { value: "", label: "All verification" },
    { value: "verified", label: "verified" },
    { value: "unverified", label: "unverified" },
];

watch(
    [search, statusFilter, genderFilter, verifiedFilter, dateRangeFilter],
    () => {
        applyFilters();
    },
    { deep: true },
);

const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    genderFilter.value = "";
    verifiedFilter.value = "";
    dateRangeFilter.value = "";
};

// Action handlers
const handleDeactivate = (patient) => {
    selectedPatient.value = patient;
    showDeactivateModal.value = true;
};

const handleActivate = (patient) => {
    selectedPatient.value = patient;
    showActivateModal.value = true;
};

const handleDelete = (patient) => {
    selectedPatient.value = patient;
    showDeleteModal.value = true;
};

const confirmDeactivate = async () => {
    if (!selectedPatient.value) return;

    processingAction.value = true;
    try {
        await router.put(
            `/patients/${selectedPatient.value.id}/deactivate`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeactivateModal.value = false;
                    selectedPatient.value = null;
                },
                onFinish: () => {
                    processingAction.value = false;
                },
            },
        );
    } catch (error) {
        processingAction.value = false;
    }
};

const confirmActivate = async () => {
    if (!selectedPatient.value) return;

    processingAction.value = true;
    try {
        await router.put(
            `/patients/${selectedPatient.value.id}/activate`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showActivateModal.value = false;
                    selectedPatient.value = null;
                },
                onFinish: () => {
                    processingAction.value = false;
                },
            },
        );
    } catch (error) {
        processingAction.value = false;
    }
};

const confirmDelete = async () => {
    if (!selectedPatient.value) return;

    processingAction.value = true;
    try {
        await router.delete(`/patients/destroy/${selectedPatient.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedPatient.value = null;
            },
            onFinish: () => {
                processingAction.value = false;
            },
        });
    } catch (error) {
        processingAction.value = false;
    }
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const viewPatient = (patientId) => {
    router.visit(`/patients/edit/${patientId}`);
};

const viewMedicalRecord = (patient) => {
    router.visit(`/patients/${patient.id}/medical-record`);
};
</script>

<template>
    <AppLayout title="Patients Management" :subtitle="welcomeMessage">
        <template #headerActions>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <PrimaryButton
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor' ||
                        auth.user?.user_type === 'nurse'
                    "
                    @click="createPatient"
                    class="w-full sm:w-auto text-center"
                >
                    Add New Patient
                </PrimaryButton>
            </div>
        </template>
        <template>
            <!-- Your main layout -->
            <div class="flex flex-col">
                <!-- Header with actions -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4"
                >
                    <h1 class="text-xl font-semibold">Patients</h1>

                    <!-- This is where your slot is being used -->
                    <slot name="headerActions">
                        <!-- Your button will appear here -->
                    </slot>
                </div>

                <!-- Rest of your content -->
            </div>
        </template>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="flex justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                <SecondaryButton
                    @click="clearFilters"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Clear all filters
                </SecondaryButton>
            </div>
            <div class="p-2">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4"
                >
                    <!-- search -->
                    <div>
                        <InputLabel for="search" value="Search" />
                        <TextInput
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Name, email, phone..."
                            class="block w-full"
                        />
                    </div>

                    <!-- status filter -->
                    <div>
                        <InputLabel for="status" value="Status" />
                        <SelectInput id="status" v-model="statusFilter">
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- gender filter -->
                    <div>
                        <InputLabel for="gender" value="Gender" />
                        <SelectInput id="gender" v-model="genderFilter">
                            <option value="">Select gender</option>
                            <option
                                v-for="gender in genders"
                                :key="gender"
                                :value="gender"
                            >
                                {{ gender.replaceAll("_", " ") }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- verification filter -->
                    <div>
                        <InputLabel for="verified" value="Verification" />
                        <SelectInput id="verified" v-model="verifiedFilter">
                            <option value="">Select verification</option>
                            <option
                                v-for="option in verifiedOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </SelectInput>
                    </div>
                    <!-- Date Range Filter -->
                    <div class="md:col-span-2 lg:col-span-2">
                        <InputLabel
                            for="date_range"
                            value="Registration Date"
                        />
                        <DateRangePicker
                            id="date_range"
                            v-model="dateRangeFilter"
                            class="w-full"
                            placeholder="Select date range"
                        />
                    </div>
                </div>
                <!-- Active Filters Display -->
                <div
                    v-if="hasActiveFilters"
                    class="mt-4 pt-4 border-t border-gray-200"
                >
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700"
                            >Active filters:</span
                        >
                        <div class="flex flex-wrap gap-2">
                            <!-- Search Filter Badge -->
                            <div
                                v-if="search"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800"
                            >
                                Search: "{{ search }}"
                                <button
                                    @click="search = ''"
                                    class="ml-1 text-blue-600 hover:text-blue-800"
                                >
                                    ×
                                </button>
                            </div>

                            <!-- Status Filter Badge -->
                            <div
                                v-if="statusFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800"
                            >
                                {{
                                    statusOptions.find(
                                        (o) => o.value === statusFilter,
                                    )?.label
                                }}
                                <button
                                    @click="statusFilter = ''"
                                    class="ml-1 text-green-600 hover:text-green-800"
                                >
                                    ×
                                </button>
                            </div>

                            <!-- Gender Filter Badge -->
                            <div
                                v-if="genderFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-purple-100 text-purple-800"
                            >
                                {{ genderFilter.replaceAll("_", "") }}
                                <button
                                    @click="genderFilter = ''"
                                    class="ml-1 text-purple-600 hover:text-purple-800"
                                >
                                    ×
                                </button>
                            </div>

                            <!-- Verification Filter Badge -->
                            <div
                                v-if="verifiedFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800"
                            >
                                {{
                                    verifiedOptions.find(
                                        (o) => o.value === verifiedFilter,
                                    )?.label
                                }}
                                <button
                                    @click="verifiedFilter = ''"
                                    class="ml-1 text-yellow-600 hover:text-yellow-800"
                                >
                                    ×
                                </button>
                            </div>

                            <!-- Date Range Filter Badge -->
                            <div
                                v-if="dateRangeFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800"
                            >
                                Date:
                                {{ formatDateRangeForDisplay(dateRangeFilter) }}
                                <button
                                    @click="dateRangeFilter = ''"
                                    class="ml-1 text-indigo-600 hover:text-indigo-800"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- patients table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- table header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-500">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white">
                        Patients: ({{ patients.total }})
                    </h3>
                    <div class="text-sm text-white">
                        Showing {{ patients.from }} to {{ patients.to }} of
                        {{ patients.total }} results
                    </div>
                </div>
            </div>
            <!-- table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Patient
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Contact Info
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Demographics
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="patient in patients.data"
                            :key="patient.id"
                            class="hover:bg-gray-50"
                        >
                            <!-- Patient Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            v-if="patient.profile_image"
                                            class="h-10 w-10 rounded-full overflow-hidden"
                                        >
                                            <img
                                                :src="
                                                    '/storage/' +
                                                    patient.profile_image
                                                "
                                                :alt="patient.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-medium text-sm"
                                                >{{ patient.initials }}</span
                                            >
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ patient.title }}
                                            {{ patient.first_name }}
                                            {{ patient.last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ patient.patient_no }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ patient.email }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ patient.phone }}
                                </div>
                                <div
                                    v-if="patient.last_login_at"
                                    class="text-xs text-gray-400 mt-1"
                                >
                                    Last login:
                                    {{ formatDate(patient.last_login_at) }}
                                </div>
                            </td>

                            <!-- Demographics -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="capitalize">{{
                                        patient.gender
                                    }}</span>
                                    <span
                                        v-if="patient.age"
                                        class="text-gray-500"
                                    >
                                        • {{ patient.age }} years</span
                                    >
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatDate(patient.date_of_birth) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ patient.nationality?.name || "N/A" }},
                                    {{ patient.state?.name }},
                                    {{ patient.region }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-2">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            patient.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{
                                            patient.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            patient.is_verified
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-yellow-100 text-yellow-800',
                                        ]"
                                    >
                                        {{
                                            patient.is_verified
                                                ? "Verified"
                                                : "Unverified"
                                        }}
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-3 py-2 text-sm font-medium">
                                <div class="flex items-center gap-1.5">
                                    <!-- View -->
                                    <button
                                        @click="viewPatient(patient.id)"
                                        class="p-1.5 rounded hover:bg-blue-50 text-blue-600"
                                        title="View patient"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </button>

                                    <template v-if="canManagePatients">
                                        <!-- Medical Record -->
                                        <button
                                            @click="viewMedicalRecord(patient)"
                                            class="p-1.5 rounded hover:bg-indigo-50 text-indigo-600"
                                            title="Medical record"
                                        >
                                            <ClipboardDocumentListIcon
                                                class="w-4 h-4"
                                            />
                                        </button>

                                        <!-- Activate / Deactivate -->
                                        <button
                                            v-if="patient.is_active"
                                            @click="handleDeactivate(patient)"
                                            class="p-1.5 rounded hover:bg-yellow-50 text-yellow-600"
                                            title="Deactivate patient"
                                        >
                                            <PauseCircleIcon class="w-4 h-4" />
                                        </button>

                                        <button
                                            v-else
                                            @click="handleActivate(patient)"
                                            class="p-1.5 rounded hover:bg-green-50 text-green-600"
                                            title="Activate patient"
                                        >
                                            <PlayCircleIcon class="w-4 h-4" />
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="handleDelete(patient)"
                                            class="p-1.5 rounded hover:bg-red-50 text-red-600"
                                            title="Delete patient"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div
                v-if="patients.links.length > 3"
                class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Page {{ patients.current_page }} of
                        {{ patients.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <template
                            v-for="(link, index) in patients.links"
                            :key="index"
                        >
                            <a
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 rounded-md text-sm font-medium',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'text-gray-700 hover:bg-gray-100',
                                ]"
                                v-html="link.label"
                            >
                            </a>
                            <span
                                v-else
                                class="px-3 py-1 text-gray-500"
                                v-html="link.label"
                            >
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="patients.data.length === 0" class="text-center py-12">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
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
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No patients found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        search || statusFilter || genderFilter || verifiedFilter
                            ? "Try adjusting your filters"
                            : "Get started by adding a new patient."
                    }}
                </p>
                <div
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor'
                    "
                    class="mt-6"
                >
                    <PrimaryButton :href="route('patients.create')">
                        Add New Patient
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Deactivate Confirmation Modal -->
        <Modal :show="showDeactivateModal" @close="showDeactivateModal = false">
            <div class="p-6">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full"
                >
                    <svg
                        class="w-6 h-6 text-yellow-600"
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
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Deactivate Patient Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to deactivate
                            <span class="font-semibold"
                                >{{ selectedPatient?.title }}
                                {{ selectedPatient?.first_name }}
                                {{ selectedPatient?.last_name }}</span
                            >'s account?
                        </p>
                        <p class="text-sm text-gray-600 mt-2">
                            They will no longer be able to log in or access the
                            system.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showDeactivateModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDeactivate"
                        :disabled="processingAction"
                    >
                        <span v-if="processingAction">Processing...</span>
                        <span v-else>Deactivate Account</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Activate Confirmation Modal -->
        <Modal :show="showActivateModal" @close="showActivateModal = false">
            <div class="p-6">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full"
                >
                    <svg
                        class="w-6 h-6 text-green-600"
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
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Activate Patient Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to activate
                            <span class="font-semibold"
                                >{{ selectedPatient?.title }}
                                {{ selectedPatient?.first_name }}
                                {{ selectedPatient?.last_name }}</span
                            >'s account?
                        </p>
                        <p class="text-sm text-gray-600 mt-2">
                            They will regain access to the system.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showActivateModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="confirmActivate"
                        :disabled="processingAction"
                    >
                        <span v-if="processingAction">Processing...</span>
                        <span v-else>Activate Account</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
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
                                >{{ selectedPatient?.title }}
                                {{ selectedPatient?.first_name }}
                                {{ selectedPatient?.last_name }}</span
                            >'s account?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All patient data will
                            be permanently removed.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        :disabled="processingAction"
                    >
                        <span v-if="processingAction">Processing...</span>
                        <span v-else>Delete Account</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
