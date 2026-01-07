<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import { debounce } from "lodash";

const props = defineProps({
    scheduleExceptions: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            status: "",
            doctor: "",
            date_from: "",
            date_to: "",
        }),
    },
    doctors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });
const selectedException = ref(null);
const showDeleteModal = ref(false);
const processingAction = ref(false);
const showDateRangePicker = ref(false);

// Welcome message based on filters
const welcomeMessage = computed(() => {
    if (hasActiveFilters.value) {
        return `Managing filtered schedule exceptions (${scheduleExceptions.total} found)`;
    }
    return "Manage doctor schedule exceptions and special dates";
});

// Search and filters
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const doctorFilter = ref(props.filters.doctor || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");

// Computed properties
const hasActiveFilters = computed(() => {
    return (
        search.value ||
        statusFilter.value ||
        doctorFilter.value ||
        dateFrom.value ||
        dateTo.value
    );
});

const canManageExceptions = computed(() => {
    return ["admin", "doctor", "nurse"].includes(auth.value.user?.user_type);
});

// Status options
const statusOptions = [
    { value: "", label: "All Status", color: "gray" },
    { value: "available", label: "Available", color: "green" },
    { value: "unavailable", label: "Unavailable", color: "red" },
];

// Date presets
const datePresets = [
    { label: "Today", value: "today" },
    { label: "Tomorrow", value: "tomorrow" },
    { label: "This Week", value: "this_week" },
    { label: "Next Week", value: "next_week" },
    { label: "This Month", value: "this_month" },
];

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Format time for display
const formatTime = (time) => {
    if (!time) return "N/A";
    return new Date(`1970-01-01T${time}`).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

// Get status badge color
const getStatusColor = (isAvailable) => {
    return isAvailable === 1
        ? "bg-green-100 text-green-800 border-green-200"
        : "bg-red-100 text-red-800 border-red-200";
};

// Get status text
const getStatusText = (isAvailable) => {
    return isAvailable === 1 ? "Available" : "Unavailable";
};

// Check if exception is upcoming
const isUpcoming = (exceptionDate) => {
    if (!exceptionDate) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const exception = new Date(exceptionDate);
    return exception >= today;
};

// Check if exception is past
const isPast = (exceptionDate) => {
    if (!exceptionDate) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const exception = new Date(exceptionDate);
    return exception < today;
};

// Actions
const createSchedule = () => {
    router.visit("/schedule-exceptions/create");
};

const editSchedule = (exception) => {
    router.visit(`/schedule-exceptions/edit/${exception.id}`);
};

const handleDelete = (exception) => {
    selectedException.value = exception;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!selectedException.value) return;

    processingAction.value = true;
    try {
        await router.delete(
            `/schedule-exceptions/destroy/${selectedException.value.id}`,
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteModal.value = false;
                    selectedException.value = null;
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

// Apply date preset
const applyDatePreset = (preset) => {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    switch (preset) {
        case "today":
            dateFrom.value = today.toISOString().split("T")[0];
            dateTo.value = today.toISOString().split("T")[0];
            break;
        case "tomorrow":
            dateFrom.value = tomorrow.toISOString().split("T")[0];
            dateTo.value = tomorrow.toISOString().split("T")[0];
            break;
        case "this_week": {
            const firstDay = new Date(
                today.setDate(today.getDate() - today.getDay() + 1)
            );
            const lastDay = new Date(
                today.setDate(today.getDate() - today.getDay() + 7)
            );
            dateFrom.value = firstDay.toISOString().split("T")[0];
            dateTo.value = lastDay.toISOString().split("T")[0];
            break;
        }
        case "next_week": {
            const nextWeek = new Date(today);
            nextWeek.setDate(nextWeek.getDate() + 7);
            const firstDay = new Date(
                nextWeek.setDate(nextWeek.getDate() - nextWeek.getDay() + 1)
            );
            const lastDay = new Date(
                nextWeek.setDate(nextWeek.getDate() - nextWeek.getDay() + 7)
            );
            dateFrom.value = firstDay.toISOString().split("T")[0];
            dateTo.value = lastDay.toISOString().split("T")[0];
            break;
        }
        case "this_month": {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(
                today.getFullYear(),
                today.getMonth() + 1,
                0
            );
            dateFrom.value = firstDay.toISOString().split("T")[0];
            dateTo.value = lastDay.toISOString().split("T")[0];
            break;
        }
    }
};

// Clear filters
const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    doctorFilter.value = "";
    dateFrom.value = "";
    dateTo.value = "";
};

// Debounced filter application
const applyFilters = debounce(() => {
    router.get(
        "/schedule-exceptions",
        {
            search: search.value,
            status: statusFilter.value,
            doctor: doctorFilter.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ["scheduleExceptions", "filters"],
        }
    );
}, 300);

// Watch for filter changes
watch([search, statusFilter, doctorFilter, dateFrom, dateTo], applyFilters, {
    deep: true,
});

// Initialize
onMounted(() => {
    // Set default date range if none is set
    if (!dateFrom.value && !dateTo.value) {
        const today = new Date();
        const nextWeek = new Date(today);
        nextWeek.setDate(nextWeek.getDate() + 30);
        dateFrom.value = today.toISOString().split("T")[0];
        dateTo.value = nextWeek.toISOString().split("T")[0];
    }
});
</script>

<template>
    <AppLayout title="Schedule Exceptions" :subtitle="welcomeMessage">
        <template #headerActions>
            <div class="flex flex-col sm:flex-row gap-3">
                <PrimaryButton
                    v-if="canManageExceptions"
                    @click="createSchedule"
                    class="flex items-center gap-2"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        />
                    </svg>
                    Add Exception
                </PrimaryButton>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Exceptions
                        </p>
                        <p class="text-2xl font-semibold text-gray-900 mt-1">
                            {{ scheduleExceptions.total || 0 }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <svg
                            class="w-6 h-6 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Unavailable Days
                        </p>
                        <p class="text-2xl font-semibold text-red-600 mt-1">
                            {{
                                scheduleExceptions.data?.filter(
                                    (e) => e.is_available === 0
                                ).length || 0
                            }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-lg">
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
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Extra Availability
                        </p>
                        <p class="text-2xl font-semibold text-green-600 mt-1">
                            {{
                                scheduleExceptions.data?.filter(
                                    (e) => e.is_available === 1
                                ).length || 0
                            }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
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
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Upcoming
                        </p>
                        <p class="text-2xl font-semibold text-orange-600 mt-1">
                            {{
                                scheduleExceptions.data?.filter((e) =>
                                    isUpcoming(e.exception_date)
                                ).length || 0
                            }}
                        </p>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg">
                        <svg
                            class="w-6 h-6 text-orange-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Filter Exceptions
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Find specific schedule exceptions
                        </p>
                    </div>
                    <button
                        @click="clearFilters"
                        :disabled="!hasActiveFilters"
                        :class="[
                            'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                            hasActiveFilters
                                ? 'text-blue-700 bg-blue-50 hover:bg-blue-100'
                                : 'text-gray-400 bg-gray-50 cursor-not-allowed',
                        ]"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        Clear All
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Search -->
                    <div>
                        <InputLabel for="search" value="Search" class="mb-2" />
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <TextInput
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Search by doctor or reason..."
                                class="w-full pl-10"
                            />
                        </div>
                    </div>

                    <!-- Doctor Filter -->
                    <div>
                        <InputLabel for="doctor" value="Doctor" class="mb-2" />
                        <SelectInput
                            id="doctor"
                            v-model="doctorFilter"
                            class="w-full"
                        >
                            <option value="">All Doctors</option>
                            <option
                                v-for="doctor in doctors"
                                :key="doctor.id"
                                :value="doctor.id"
                            >
                                Dr. {{ doctor.last_name }},
                                {{ doctor.first_name }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <InputLabel for="status" value="Status" class="mb-2" />
                        <SelectInput
                            id="status"
                            v-model="statusFilter"
                            class="w-full"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Date Range Toggle -->
                    <div>
                        <InputLabel value="Date Range" class="mb-2" />
                        <button
                            @click="showDateRangePicker = !showDateRangePicker"
                            class="w-full flex items-center justify-between px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        >
                            <span class="text-sm">
                                {{
                                    dateFrom && dateTo
                                        ? `${formatDate(
                                              dateFrom
                                          )} - ${formatDate(dateTo)}`
                                        : "Select date range"
                                }}
                            </span>
                            <svg
                                class="w-5 h-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Date Range Picker -->
                <div
                    v-if="showDateRangePicker"
                    class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50"
                >
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Date Presets -->
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">
                                Quick Presets
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="preset in datePresets"
                                    :key="preset.value"
                                    @click="applyDatePreset(preset.value)"
                                    class="px-3 py-1.5 text-xs font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    {{ preset.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Custom Date Range -->
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel
                                        for="date_from"
                                        value="From Date"
                                        class="mb-2"
                                    />
                                    <TextInput
                                        id="date_from"
                                        v-model="dateFrom"
                                        type="date"
                                        class="w-full"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        for="date_to"
                                        value="To Date"
                                        class="mb-2"
                                    />
                                    <TextInput
                                        id="date_to"
                                        v-model="dateTo"
                                        type="date"
                                        class="w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div
                    v-if="hasActiveFilters"
                    class="mt-6 pt-6 border-t border-gray-200"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700"
                            >Active filters:</span
                        >
                        <div class="flex flex-wrap gap-2">
                            <!-- Search Filter Badge -->
                            <div
                                v-if="search"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-700 border border-blue-200"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                                <span class="max-w-[150px] truncate"
                                    >"{{ search }}"</span
                                >
                                <button
                                    @click="search = ''"
                                    class="ml-1 hover:text-blue-900"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Status Filter Badge -->
                            <div
                                v-if="statusFilter"
                                :class="[
                                    'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm border',
                                    statusFilter === 'available'
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-red-50 text-red-700 border-red-200',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4"
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
                                <span>{{
                                    statusFilter === "available"
                                        ? "Available"
                                        : "Unavailable"
                                }}</span>
                                <button
                                    @click="statusFilter = ''"
                                    class="ml-1 hover:opacity-75"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Date Range Badge -->
                            <div
                                v-if="dateFrom || dateTo"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm bg-purple-50 text-purple-700 border border-purple-200"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span
                                    >{{ formatDate(dateFrom) }} -
                                    {{ formatDate(dateTo) }}</span
                                >
                                <button
                                    @click="
                                        dateFrom = '';
                                        dateTo = '';
                                    "
                                    class="ml-1 hover:text-purple-900"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exceptions Table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- Header -->
            <div
                class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Schedule Exceptions
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Showing {{ scheduleExceptions.from || 0 }} to
                            {{ scheduleExceptions.to || 0 }} of
                            {{ scheduleExceptions.total || 0 }} exceptions
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500"
                            >Total: {{ scheduleExceptions.total || 0 }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="sm:hidden">
                <div
                    v-if="scheduleExceptions.data?.length === 0"
                    class="text-center py-12 px-4"
                >
                    <div
                        class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center"
                    >
                        <svg
                            class="w-8 h-8 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">
                        No exceptions found
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Get started by adding an exception."
                        }}
                    </p>
                    <div v-if="canManageExceptions" class="mt-6">
                        <PrimaryButton
                            @click="createSchedule"
                            class="inline-flex items-center gap-2"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                />
                            </svg>
                            Add Exception
                        </PrimaryButton>
                    </div>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="exception in scheduleExceptions.data"
                        :key="exception.id"
                        class="p-4 hover:bg-gray-50 transition-colors duration-150"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        Dr. {{ exception.user?.first_name }}
                                        {{ exception.user?.last_name }}
                                    </h4>
                                    <span
                                        v-if="isPast(exception.exception_date)"
                                        class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                                    >
                                        Past
                                    </span>
                                    <span
                                        v-else-if="
                                            isUpcoming(exception.exception_date)
                                        "
                                        class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                                    >
                                        Upcoming
                                    </span>
                                </div>

                                <div class="space-y-1.5 text-sm">
                                    <div
                                        class="flex items-center gap-2 text-gray-600"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span>{{
                                            formatDate(exception.exception_date)
                                        }}</span>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 text-gray-600"
                                    >
                                        <svg
                                            class="w-4 h-4"
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
                                        <span
                                            >{{
                                                formatTime(exception.start_time)
                                            }}
                                            -
                                            {{
                                                formatTime(exception.end_time)
                                            }}</span
                                        >
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                getStatusColor(
                                                    exception.is_available
                                                ),
                                            ]"
                                        >
                                            {{
                                                getStatusText(
                                                    exception.is_available
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="exception.reason"
                                        class="text-gray-600 line-clamp-2"
                                    >
                                        <span class="font-medium">Reason:</span>
                                        {{ exception.reason }}
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="canManageExceptions"
                                class="flex flex-col gap-1"
                            >
                                <button
                                    @click="editSchedule(exception)"
                                    class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Edit"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="handleDelete(exception)"
                                    class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete"
                                >
                                    <svg
                                        class="w-5 h-5"
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
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
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
                                Date
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Time
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Reason
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
                                Timeline
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
                            v-for="exception in scheduleExceptions.data"
                            :key="exception.id"
                            class="hover:bg-gray-50 transition-colors duration-150"
                        >
                            <!-- Doctor -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                    >
                                        <span
                                            class="text-blue-600 font-semibold"
                                        >
                                            {{
                                                exception.user
                                                    ?.first_name?.[0] || "D"
                                            }}{{
                                                exception.user
                                                    ?.last_name?.[0] || "R"
                                            }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            Dr. {{ exception.user?.last_name }},
                                            {{ exception.user?.first_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{
                                                exception.user
                                                    ?.specialization ||
                                                "General"
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="text-sm font-semibold text-gray-900"
                                >
                                    {{ formatDate(exception.exception_date) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{
                                        new Date(
                                            exception.exception_date
                                        ).toLocaleDateString("en-US", {
                                            weekday: "long",
                                        })
                                    }}
                                </div>
                            </td>

                            <!-- Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatTime(exception.start_time) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    to {{ formatTime(exception.end_time) }}
                                </div>
                            </td>

                            <!-- Reason -->
                            <td class="px-6 py-4">
                                <div
                                    class="text-sm text-gray-900 max-w-xs truncate"
                                >
                                    {{
                                        exception.reason || "No reason provided"
                                    }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                                        getStatusColor(exception.is_available),
                                    ]"
                                >
                                    {{ getStatusText(exception.is_available) }}
                                </span>
                            </td>

                            <!-- Timeline -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="isPast(exception.exception_date)"
                                    class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                                >
                                    Past
                                </span>
                                <span
                                    v-else-if="
                                        isUpcoming(exception.exception_date)
                                    "
                                    class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                                >
                                    Upcoming
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="editSchedule(exception)"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="handleDelete(exception)"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State for Desktop -->
            <div
                v-if="scheduleExceptions.data?.length === 0"
                class="hidden sm:block text-center py-16 px-6"
            >
                <div
                    class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center"
                >
                    <svg
                        class="w-10 h-10 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"
                        />
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-medium text-gray-900">
                    No schedule exceptions found
                </h3>
                <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters or search criteria."
                            : "Add exceptions for special dates, holidays, or doctor unavailability."
                    }}
                </p>
                <div v-if="canManageExceptions" class="mt-8">
                    <PrimaryButton
                        @click="createSchedule"
                        class="inline-flex items-center gap-2 px-6 py-3"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        Add Exception
                    </PrimaryButton>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="scheduleExceptions.links?.length > 3"
                class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div class="text-sm text-gray-700">
                        Showing {{ scheduleExceptions.from || 0 }} to
                        {{ scheduleExceptions.to || 0 }} of
                        {{ scheduleExceptions.total || 0 }} results
                    </div>
                    <div class="flex items-center gap-2">
                        <template
                            v-for="(link, index) in scheduleExceptions.links"
                            :key="index"
                        >
                            <a
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'inline-flex items-center justify-center min-w-[36px] px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex items-center justify-center min-w-[36px] px-3 py-2 text-sm text-gray-500"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full"
                >
                    <svg
                        class="w-8 h-8 text-red-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"
                        />
                    </svg>
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-xl font-bold text-gray-900">
                        Delete Schedule Exception
                    </h3>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete the exception for
                            <span class="font-semibold text-gray-900">
                                Dr. {{ selectedException?.user?.first_name }}
                                {{ selectedException?.user?.last_name }}
                            </span>
                            on
                            <span class="font-semibold text-gray-900">{{
                                formatDate(selectedException?.exception_date)
                            }}</span
                            >?
                        </p>
                        <div
                            class="mt-6 p-4 bg-red-50 rounded-lg border border-red-100"
                        >
                            <div class="flex items-center">
                                <svg
                                    class="w-5 h-5 text-red-600 mr-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"
                                    />
                                </svg>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-red-800">
                                        This action cannot be undone
                                    </p>
                                    <ul
                                        class="text-xs text-red-700 mt-1 list-disc list-inside"
                                    >
                                        <li>Appointments may be affected</li>
                                        <li>Doctor availability will change</li>
                                        <li>
                                            Exception record will be permanently
                                            removed
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-8 flex flex-col sm:flex-row sm:justify-end gap-3"
                >
                    <SecondaryButton
                        @click="showDeleteModal = false"
                        class="w-full sm:w-auto order-2 sm:order-1"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        :disabled="processingAction"
                        class="w-full sm:w-auto order-1 sm:order-2"
                    >
                        <span
                            v-if="processingAction"
                            class="flex items-center gap-2"
                        >
                            <svg
                                class="animate-spin h-4 w-4"
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
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            Deleting...
                        </span>
                        <span v-else class="flex items-center gap-2">
                            Delete Exception
                        </span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    :deep(input),
    :deep(select),
    :deep(textarea) {
        font-size: 16px !important;
        min-height: 44px;
    }

    :deep(button) {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Animations */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
