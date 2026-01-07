<script setup>
import { router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    doctors: {
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

const genders = computed(() => page.props.medical.genders);

const welcomeMessage = computed(() => {
    return "Manage doctor records and accounts";
});

const createDoctor = () => {
    router.visit("/doctors/create");
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
const selectedDoctor = ref(null);

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
            "/doctors",
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
                only: ["Doctors", "filters"],
            }
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
    { deep: true }
);

const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    genderFilter.value = "";
    verifiedFilter.value = "";
    dateRangeFilter.value = "";
};

// Action handlers
const handleDeactivate = (doctor) => {
    selectedDoctor.value = doctor;
    showDeactivateModal.value = true;
};

const handleActivate = (doctor) => {
    selectedDoctor.value = doctor;
    showActivateModal.value = true;
};

const handleDelete = (doctor) => {
    selectedDoctor.value = doctor;
    showDeleteModal.value = true;
};

const confirmDeactivate = async () => {
    if (!selectedDoctor.value) return;

    processingAction.value = true;
    try {
        await router.put(
            `/doctors/${selectedDoctor.value.id}/deactivate`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeactivateModal.value = false;
                    selectedDoctor.value = null;
                },
                onFinish: () => {
                    processingAction.value = false;
                },
            }
        );
    } catch (error) {
        processingAction.value = false;
    }
};

const confirmActivate = async () => {
    if (!selectedDoctor.value) return;

    processingAction.value = true;
    try {
        await router.put(
            `/doctors/${selectedDoctor.value.id}/activate`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showActivateModal.value = false;
                    selectedDoctor.value = null;
                },
                onFinish: () => {
                    processingAction.value = false;
                },
            }
        );
    } catch (error) {
        processingAction.value = false;
    }
};

const confirmDelete = async () => {
    if (!selectedDoctor.value) return;

    processingAction.value = true;
    try {
        await router.delete(`/doctors/destroy/${selectedDoctor.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedDoctor.value = null;
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

const viewDoctor = (doctorId) => {
    router.visit(`/doctors/edit/${doctorId}`);
};
</script>

<template>
    <AppLayout title="Doctors Management" :subtitle="welcomeMessage">
        <template #headerActions>
            <PrimaryButton
                v-if="
                    auth.user?.user_type === 'admin' ||
                    auth.user?.user_type === 'doctor' ||
                    auth.user?.user_type === 'nurse'
                "
                @click="createDoctor"
            >
                Add New Doctor
            </PrimaryButton>
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
                                        (o) => o.value === statusFilter
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
                                        (o) => o.value === verifiedFilter
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

        <!-- doctors table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- table header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-500">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white">
                        Doctors: ({{ doctors.total }})
                    </h3>
                    <div class="text-sm text-white">
                        Showing {{ doctors.from }} to {{ doctors.to }} of
                        {{ doctors.total }} results
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
                                Doctor
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                <!-- Specialization Info -->
                                Specialization Info
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
                            v-for="doctor in doctors.data"
                            :key="doctor.id"
                            class="hover:bg-gray-50"
                        >
                            <!-- doctor Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            v-if="doctor.profile_image"
                                            class="h-10 w-10 rounded-full overflow-hidden"
                                        >
                                            <img
                                                :src="
                                                    '/storage/' +
                                                    doctor.profile_image
                                                "
                                                :alt="doctor.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-medium text-sm"
                                                >{{ doctor.initials }}</span
                                            >
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ doctor.title }}
                                            {{ doctor.first_name }}
                                            {{ doctor.last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <!-- ID: {{ doctor.doctor_no }} -->
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- specialization info -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="specialization in doctor.specializations"
                                        :key="specialization.id"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ specialization.name }}
                                        <template
                                            v-if="
                                                specialization.pivot
                                                    ?.years_of_experience
                                            "
                                        >
                                            ({{
                                                specialization.pivot
                                                    .years_of_experience
                                            }}yrs)
                                        </template>
                                    </span>
                                </div>
                            </td>

                            <!-- Contact Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ doctor.email }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ doctor.phone }}
                                </div>
                                <div
                                    v-if="doctor.last_login_at"
                                    class="text-xs text-gray-400 mt-1"
                                >
                                    Last login:
                                    {{ formatDate(doctor.last_login_at) }}
                                </div>
                            </td>

                            <!-- Demographics -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="capitalize">{{
                                        doctor.gender
                                    }}</span>
                                    <span
                                        v-if="doctor.age"
                                        class="text-gray-500"
                                    >
                                        • {{ doctor.age }} years</span
                                    >
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatDate(doctor.date_of_birth) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ doctor.nationality?.name || "N/A" }},
                                    {{ doctor.state?.name }},
                                    {{ doctor.region }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-2">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            doctor.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{
                                            doctor.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            doctor.is_verified
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-yellow-100 text-yellow-800',
                                        ]"
                                    >
                                        {{
                                            doctor.is_verified
                                                ? "Verified"
                                                : "Unverified"
                                        }}
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                            >
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="viewDoctor(doctor.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View
                                    </button>

                                    <template
                                        v-if="
                                            auth.user?.user_type === 'admin' ||
                                            auth.user?.user_type === 'doctor'
                                        "
                                    >
                                        <button
                                            @click="handleDeactivate(doctor)"
                                            v-if="doctor.is_active"
                                            class="text-yellow-600 hover:text-yellow-900"
                                        >
                                            Deactivate
                                        </button>
                                        <button
                                            @click="handleActivate(doctor)"
                                            v-else
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Activate
                                        </button>

                                        <button
                                            @click="handleDelete(doctor)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
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
                v-if="doctors.links.length > 3"
                class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Page {{ doctors.current_page }} of
                        {{ doctors.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <template
                            v-for="(link, index) in doctors.links"
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
            <div v-if="doctors.data.length === 0" class="text-center py-12">
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
                    No doctors found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        search || statusFilter || genderFilter || verifiedFilter
                            ? "Try adjusting your filters"
                            : "Get started by adding a new doctor."
                    }}
                </p>
                <div
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor'
                    "
                    class="mt-6"
                >
                    <PrimaryButton :href="doctors.create">
                        Add New Doctor
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
                        Deactivate Doctor Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to deactivate
                            <span class="font-semibold"
                                >{{ selectedDoctor?.title }}
                                {{ selectedDoctor?.first_name }}
                                {{ selectedDoctor?.last_name }}</span
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
                        Activate Doctor Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to activate
                            <span class="font-semibold"
                                >{{ selectedDoctor?.title }}
                                {{ selectedDoctor?.first_name }}
                                {{ selectedDoctor?.last_name }}</span
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
                        Delete Doctor Account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete
                            <span class="font-semibold"
                                >{{ selectedDoctor?.title }}
                                {{ selectedDoctor?.first_name }}
                                {{ selectedDoctor?.last_name }}</span
                            >'s account?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All doctor data will
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
