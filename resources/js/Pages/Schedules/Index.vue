<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import { debounce } from "lodash";

const props = defineProps({
    schedules: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            status: "",
            doctor: "",
            day: "",
        }),
    },
    doctors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });
const selectedSchedule = ref(null);
const welcomeMessage = computed(() => {
    return "Manage doctor schedules and availability";
});

// Actions
const createSchedule = () => {
    router.visit("/schedules/create");
};

const viewScheduleExceptions = () => {
    router.visit("/schedule-exceptions");
};

// Search and filters
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const doctorFilter = ref(props.filters.doctor || "");
const dayFilter = ref(props.filters.day || "");

// Modal state
const showDeleteModal = ref(false);
const showScheduleDetails = ref(false);
const scheduleDetails = ref(null);

// Processing state
const processingAction = ref(false);

// Days of week options
const daysOfWeek = [
    { value: "", label: "All Days" },
    { value: "Monday", label: "Monday" },
    { value: "Tuesday", label: "Tuesday" },
    { value: "Wednesday", label: "Wednesday" },
    { value: "Thursday", label: "Thursday" },
    { value: "Friday", label: "Friday" },
    { value: "Saturday", label: "Saturday" },
    { value: "Sunday", label: "Sunday" },
];

// Status options
const statusOptions = [
    { value: "", label: "All Status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

// Computed properties
const hasActiveFilters = computed(() => {
    return (
        search.value ||
        statusFilter.value ||
        doctorFilter.value ||
        dayFilter.value
    );
});

const canManageSchedules = computed(() => {
    return ["admin", "doctor", "nurse"].includes(auth.value.user?.user_type);
});

// Debounced filter application
const applyFilters = debounce(() => {
    router.get(
        "/schedules",
        {
            search: search.value,
            status: statusFilter.value,
            doctor: doctorFilter.value,
            day: dayFilter.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ["schedules", "filters"],
        }
    );
}, 300);

// Watch for filter changes
watch([search, statusFilter, doctorFilter, dayFilter], applyFilters, {
    deep: true,
});

// Clear all filters
const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    doctorFilter.value = "";
    dayFilter.value = "";
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

// Schedule actions
const editSchedule = (schedule) => {
    router.visit(`/schedules/edit/${schedule.id}`);
};

const viewSchedule = (schedule) => {
    scheduleDetails.value = schedule;
    showScheduleDetails.value = true;
};

const handleDelete = (schedule) => {
    selectedSchedule.value = schedule;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!selectedSchedule.value) return;

    processingAction.value = true;
    try {
        await router.delete(`/schedules/destroy/${selectedSchedule.value.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedSchedule.value = null;
            },
            onFinish: () => {
                processingAction.value = false;
            },
        });
    } catch (error) {
        processingAction.value = false;
    }
};

// Calculate total weekly hours for a schedule
const calculateWeeklyHours = (schedule) => {
    if (!schedule.start_time || !schedule.end_time) return 0;

    const start = new Date(`1970-01-01T${schedule.start_time}`);
    const end = new Date(`1970-01-01T${schedule.end_time}`);
    const hours = (end - start) / (1000 * 60 * 60);

    return Math.round(hours * 10) / 10; // Round to 1 decimal
};

// Get day badge color
const getDayColor = (day) => {
    const colors = {
        Monday: "bg-blue-100 text-blue-800",
        Tuesday: "bg-purple-100 text-purple-800",
        Wednesday: "bg-green-100 text-green-800",
        Thursday: "bg-yellow-100 text-yellow-800",
        Friday: "bg-indigo-100 text-indigo-800",
        Saturday: "bg-red-100 text-red-800",
        Sunday: "bg-pink-100 text-pink-800",
    };
    return colors[day] || "bg-gray-100 text-gray-800";
};

// Check if schedule is current
const isCurrentSchedule = (schedule) => {
    if (!schedule.is_active) return false;

    const now = new Date();
    const currentDay = now.toLocaleDateString("en-US", { weekday: "long" });
    if (currentDay !== schedule.day_of_week) return false;

    const currentTime = now.toTimeString().slice(0, 8);
    return (
        schedule.start_time <= currentTime && schedule.end_time >= currentTime
    );
};
</script>

<template>
    <AppLayout title="Doctor Schedules" :subtitle="welcomeMessage">
        <template #headerActions>
            <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                <button
                    v-if="canManageSchedules"
                    @click="viewScheduleExceptions"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200"
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
                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Manage Exceptions
                </button>
                <PrimaryButton
                    v-if="canManageSchedules"
                    @click="createSchedule"
                    class="inline-flex items-center justify-center gap-2"
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
                    Add New Schedule
                </PrimaryButton>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Schedules
                        </p>
                        <p class="text-2xl font-semibold text-gray-900 mt-1">
                            {{ schedules.total || 0 }}
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
                            Active Schedules
                        </p>
                        <p class="text-2xl font-semibold text-green-600 mt-1">
                            {{
                                schedules.data?.filter((s) => s.is_active)
                                    .length || 0
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
                            Doctors with Schedules
                        </p>
                        <p class="text-2xl font-semibold text-purple-600 mt-1">
                            {{
                                [
                                    ...new Set(
                                        schedules.data?.map((s) => s.user?.id)
                                    ),
                                ].length || 0
                            }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <svg
                            class="w-6 h-6 text-purple-600"
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
                </div>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Today's Schedules
                        </p>
                        <p class="text-2xl font-semibold text-orange-600 mt-1">
                            {{
                                schedules.data?.filter((s) => {
                                    const today = new Date().toLocaleDateString(
                                        "en-US",
                                        { weekday: "long" }
                                    );
                                    return (
                                        s.day_of_week === today && s.is_active
                                    );
                                }).length || 0
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
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Filters
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Filter schedules by various criteria
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
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Search -->
                    <div>
                        <InputLabel
                            for="search"
                            value="Search Doctors"
                            class="mb-2"
                        />
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
                                placeholder="Search by doctor name..."
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

                    <!-- Day Filter -->
                    <div>
                        <InputLabel
                            for="day"
                            value="Day of Week"
                            class="mb-2"
                        />
                        <SelectInput
                            id="day"
                            v-model="dayFilter"
                            class="w-full"
                        >
                            <option
                                v-for="option in daysOfWeek"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
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

                            <!-- Doctor Filter Badge -->
                            <div
                                v-if="doctorFilter"
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
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                <span>{{
                                    doctors.find((d) => d.id == doctorFilter)
                                        ?.first_name || "Doctor"
                                }}</span>
                                <button
                                    @click="doctorFilter = ''"
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

                            <!-- Status Filter Badge -->
                            <div
                                v-if="statusFilter"
                                :class="[
                                    'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm border',
                                    statusFilter === 'active'
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
                                    statusFilter === "active"
                                        ? "Active"
                                        : "Inactive"
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedules Table -->
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
                            Schedules Overview
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Showing {{ schedules.from || 0 }} to
                            {{ schedules.to || 0 }} of
                            {{ schedules.total || 0 }} schedules
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500"
                            >Total: {{ schedules.total || 0 }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="sm:hidden">
                <div
                    v-if="schedules.data?.length === 0"
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
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">
                        No schedules found
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Get started by adding a new schedule."
                        }}
                    </p>
                    <div v-if="canManageSchedules" class="mt-6">
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
                            Add New Schedule
                        </PrimaryButton>
                    </div>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="schedule in schedules.data"
                        :key="schedule.id"
                        class="p-4 hover:bg-gray-50 transition-colors duration-150"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div
                                @click="viewSchedule(schedule)"
                                class="flex-1 min-w-0 cursor-pointer"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <h4
                                        class="text-sm font-semibold text-gray-900 truncate"
                                    >
                                        Dr. {{ schedule.user?.first_name }}
                                        {{ schedule.user?.last_name }}
                                    </h4>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-xs font-medium rounded-full',
                                            getDayColor(schedule.day_of_week),
                                        ]"
                                    >
                                        {{ schedule.day_of_week }}
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
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <span
                                            >{{
                                                formatTime(schedule.start_time)
                                            }}
                                            -
                                            {{
                                                formatTime(schedule.end_time)
                                            }}</span
                                        >
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
                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                            />
                                        </svg>
                                        <span
                                            >{{
                                                calculateWeeklyHours(schedule)
                                            }}
                                            hrs/week</span
                                        >
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                schedule.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800',
                                            ]"
                                        >
                                            {{
                                                schedule.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                        <span
                                            v-if="isCurrentSchedule(schedule)"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            In Progress
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="canManageSchedules"
                                class="flex flex-col gap-1"
                            >
                                <button
                                    @click="editSchedule(schedule)"
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
                                    @click="handleDelete(schedule)"
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
                                Day
                            </th>
                            <th
                                scope="col"
                                class="px6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Time
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Duration
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Slot Info
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
                            v-for="schedule in schedules.data"
                            :key="schedule.id"
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
                                                schedule.user
                                                    ?.first_name?.[0] || "D"
                                            }}{{
                                                schedule.user?.last_name?.[0] ||
                                                "R"
                                            }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-semibold text-gray-900"
                                        >
                                            Dr. {{ schedule.user?.last_name }},
                                            {{ schedule.user?.first_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{
                                                schedule.user?.specialization ||
                                                "General"
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Day -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'px-3 py-1 text-xs font-medium rounded-full',
                                        getDayColor(schedule.day_of_week),
                                    ]"
                                >
                                    {{ schedule.day_of_week }}
                                </span>
                            </td>

                            <!-- Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatTime(schedule.start_time) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    to {{ formatTime(schedule.end_time) }}
                                </div>
                            </td>

                            <!-- Duration -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ calculateWeeklyHours(schedule) }} hrs
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ schedule.slot_duration || 30 }} min slots
                                </div>
                            </td>

                            <!-- Slot Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ schedule.max_patients_per_slot || 1 }}
                                    patient(s)/slot
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ schedule.total_slots || 0 }} total slots
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            schedule.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{
                                            schedule.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                    <span
                                        v-if="isCurrentSchedule(schedule)"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        <svg
                                            class="mr-1 w-3 h-3 animate-pulse"
                                            fill="currentColor"
                                            viewBox="0 0 8 8"
                                        >
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        In Progress
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="viewSchedule(schedule)"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                    >
                                        View
                                    </button>
                                    <template v-if="canManageSchedules">
                                        <button
                                            @click="editSchedule(schedule)"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="handleDelete(schedule)"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
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

            <!-- Empty State for Desktop -->
            <div
                v-if="schedules.data?.length === 0"
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
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-medium text-gray-900">
                    No schedules found
                </h3>
                <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters or search criteria."
                            : "Get started by adding a new schedule to manage doctor availability."
                    }}
                </p>
                <div v-if="canManageSchedules" class="mt-8">
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
                        Add New Schedule
                    </PrimaryButton>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="schedules.links?.length > 3"
                class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div class="text-sm text-gray-700">
                        Showing {{ schedules.from || 0 }} to
                        {{ schedules.to || 0 }} of
                        {{ schedules.total || 0 }} results
                    </div>
                    <div class="flex items-center gap-2">
                        <template
                            v-for="(link, index) in schedules.links"
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

        <!-- Schedule Details Modal -->
        <Modal :show="showScheduleDetails" @close="showScheduleDetails = false">
            <div class="p-6">
                <div v-if="scheduleDetails">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                Schedule Details
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ scheduleDetails.day_of_week }} Schedule
                            </p>
                        </div>
                        <button
                            @click="showScheduleDetails = false"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg
                                class="w-6 h-6"
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

                    <div class="mt-6 space-y-6">
                        <!-- Doctor Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Doctor Information
                            </h4>
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <span
                                        class="text-blue-600 font-semibold text-lg"
                                    >
                                        {{
                                            scheduleDetails.user
                                                ?.first_name?.[0]
                                        }}{{
                                            scheduleDetails.user?.last_name?.[0]
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        Dr.
                                        {{ scheduleDetails.user?.last_name }},
                                        {{ scheduleDetails.user?.first_name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{
                                            scheduleDetails.user
                                                ?.specialization ||
                                            "General Practitioner"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Day of Week</label
                                >
                                <div
                                    :class="[
                                        'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium',
                                        getDayColor(
                                            scheduleDetails.day_of_week
                                        ),
                                    ]"
                                >
                                    {{ scheduleDetails.day_of_week }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Status</label
                                >
                                <div
                                    :class="[
                                        'inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium',
                                        scheduleDetails.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                    ]"
                                >
                                    {{
                                        scheduleDetails.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Start Time</label
                                >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatTime(scheduleDetails.start_time) }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >End Time</label
                                >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatTime(scheduleDetails.end_time) }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Slot Duration</label
                                >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ scheduleDetails.slot_duration }} minutes
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Max Patients per Slot</label
                                >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ scheduleDetails.max_patients_per_slot }}
                                    patient(s)
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Schedule Summary
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="text-center p-3 bg-blue-50 rounded-lg"
                                >
                                    <div
                                        class="text-2xl font-bold text-blue-700"
                                    >
                                        {{
                                            calculateWeeklyHours(
                                                scheduleDetails
                                            )
                                        }}
                                    </div>
                                    <div class="text-xs text-blue-600">
                                        Hours per Week
                                    </div>
                                </div>
                                <div
                                    class="text-center p-3 bg-green-50 rounded-lg"
                                >
                                    <div
                                        class="text-2xl font-bold text-green-700"
                                    >
                                        {{
                                            Math.floor(
                                                (calculateWeeklyHours(
                                                    scheduleDetails
                                                ) *
                                                    60) /
                                                    scheduleDetails.slot_duration
                                            )
                                        }}
                                    </div>
                                    <div class="text-xs text-green-600">
                                        Total Slots
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <SecondaryButton @click="showScheduleDetails = false">
                            Close
                        </SecondaryButton>
                        <PrimaryButton
                            v-if="canManageSchedules"
                            @click="editSchedule(scheduleDetails)"
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
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit Schedule
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

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
                    <h3 class="text-lg font-semibold text-gray-900">
                        Delete Schedule
                    </h3>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete the schedule for
                            <span class="font-semibold text-gray-900">
                                Dr. {{ selectedSchedule?.user?.first_name }}
                                {{ selectedSchedule?.user?.last_name }}
                            </span>
                            on
                            <span class="font-semibold text-gray-900">{{
                                selectedSchedule?.day_of_week
                            }}</span
                            >?
                        </p>
                        <div
                            class="mt-4 p-3 bg-red-50 rounded-lg border border-red-100"
                        >
                            <p class="text-sm text-red-700 font-medium">
                                ⚠️ This action cannot be undone. All associated
                                appointment slots will be removed.
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-3"
                >
                    <SecondaryButton
                        @click="showDeleteModal = false"
                        :disabled="processingAction"
                        class="w-full sm:w-auto"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        :disabled="processingAction"
                        class="w-full sm:w-auto"
                    >
                        <span
                            v-if="processingAction"
                            class="flex items-center gap-2"
                        >
                            <svg
                                class="animate-spin h-4 w-4 text-white"
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
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            Delete Schedule
                        </span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
