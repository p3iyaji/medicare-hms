<script setup>
import { usePage, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import SelectInput from "@/Components/SelectInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Checkbox from "@/Components/Checkbox.vue";

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });

const props = defineProps({
    schedule: {
        type: Object,
        default: null,
    },
    doctors: {
        type: Array,
        required: true,
    },
    weekdays: {
        type: Array,
        default: () => [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
        ],
    },
    existingSchedules: {
        type: Array,
        default: () => [],
    },
});

// Computed properties
const schedule = computed(() => props.schedule || {});
const isEditing = computed(() => !!props.schedule?.id);
const pageTitle = computed(() =>
    isEditing.value
        ? `Edit Schedule - ${formatScheduleName()}`
        : "Create New Schedule"
);
const pageSubtitle = computed(() =>
    isEditing.value
        ? "Update doctor's availability and schedule details"
        : "Add a new schedule for doctor appointments"
);

// Form state
const form = useForm({
    doctor_id: props.schedule?.doctor_id || "",
    day_of_week: props.schedule?.day_of_week || "",
    start_time: props.schedule?.start_time || "09:00",
    end_time: props.schedule?.end_time || "17:00",
    slot_duration: props.schedule?.slot_duration || 30,
    max_patients_per_slot: props.schedule?.max_patients_per_slot || 1,
    is_active: props.schedule?.is_active ?? true,
});

// UI State
const showDeleteModal = ref(false);
const showValidationErrors = ref(false);
const isSubmitting = ref(false);

// Schedule calculations
const calculatedSlots = computed(() => {
    if (!form.start_time || !form.end_time || !form.slot_duration) return 0;

    const start = new Date(`1970-01-01T${form.start_time}`);
    const end = new Date(`1970-01-01T${form.end_time}`);
    const duration = (end - start) / (1000 * 60); // minutes
    return Math.floor(duration / form.slot_duration);
});

const totalPatientsPerDay = computed(() => {
    return calculatedSlots.value * form.max_patients_per_slot;
});

const formatScheduleName = () => {
    if (schedule.value.user) {
        return `Dr. ${schedule.value.user.last_name}, ${schedule.value.user.first_name} - ${schedule.value.day_of_week}`;
    }
    return `Schedule #${schedule.value.id}`;
};

// Time validation
const validateTimes = () => {
    if (!form.start_time || !form.end_time) return true;

    const start = new Date(`1970-01-01T${form.start_time}`);
    const end = new Date(`1970-01-01T${form.end_time}`);
    const minDuration = form.slot_duration * 60 * 1000; // milliseconds

    return end > start && end - start >= minDuration;
};

const hasTimeConflict = computed(() => {
    if (
        !form.doctor_id ||
        !form.day_of_week ||
        !form.start_time ||
        !form.end_time
    ) {
        return false;
    }

    const currentStart = new Date(`1970-01-01T${form.start_time}`);
    const currentEnd = new Date(`1970-01-01T${form.end_time}`);

    return props.existingSchedules.some((existing) => {
        // Skip the current schedule when editing
        if (isEditing.value && existing.id === schedule.value.id) return false;

        if (
            existing.doctor_id == form.doctor_id &&
            existing.day_of_week === form.day_of_week
        ) {
            const existingStart = new Date(`1970-01-01T${existing.start_time}`);
            const existingEnd = new Date(`1970-01-01T${existing.end_time}`);

            // Check for overlap
            return (
                (currentStart >= existingStart && currentStart < existingEnd) ||
                (currentEnd > existingStart && currentEnd <= existingEnd) ||
                (currentStart <= existingStart && currentEnd >= existingEnd)
            );
        }
        return false;
    });
});

// Form validation
const validateForm = () => {
    showValidationErrors.value = true;

    if (!validateTimes()) {
        form.setError(
            "end_time",
            "End time must be after start time and allow at least one slot"
        );
        return false;
    }

    if (hasTimeConflict.value) {
        form.setError("start_time", "Time conflict with existing schedule");
        return false;
    }

    return true;
};

// Form submission
const submitForm = () => {
    showValidationErrors.value = true;

    if (!validateForm()) {
        return;
    }

    isSubmitting.value = true;

    if (isEditing.value) {
        form.put(`/schedules/update/${props.schedule.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setTimeout(() => {
                    router.visit("/schedules");
                }, 1500);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else {
        form.post("/schedules/store", {
            preserveScroll: true,
            onSuccess: () => {
                setTimeout(() => {
                    router.visit("/schedules");
                }, 1500);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    }
};

// Navigation
const handleCancel = () => {
    if (form.isDirty) {
        if (
            confirm("You have unsaved changes. Are you sure you want to leave?")
        ) {
            router.visit("/schedules");
        }
    } else {
        router.visit("/schedules");
    }
};

// Delete functionality
const confirmDelete = () => {
    router.delete(`/schedules/destroy/${props.schedule.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            router.visit("/schedules");
        },
    });
};

// Time helpers
const formatTime = (time) => {
    if (!time) return "N/A";
    return new Date(`1970-01-01T${time}`).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

const calculateDuration = () => {
    if (!form.start_time || !form.end_time) return "N/A";

    const start = new Date(`1970-01-01T${form.start_time}`);
    const end = new Date(`1970-01-01T${form.end_time}`);
    const minutes = (end - start) / (1000 * 60);

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    return `${hours}h ${mins}m`;
};

// Watch for time changes
watch([() => form.start_time, () => form.end_time], () => {
    if (showValidationErrors.value) {
        validateTimes();
    }
});

// Initialize form
onMounted(() => {
    if (isEditing.value) {
        document.title = `Edit Schedule - ${formatScheduleName()}`;
    }
});
</script>

<template>
    <AppLayout :title="pageTitle" :subtitle="pageSubtitle">
        <template #headerActions>
            <div class="flex flex-wrap gap-3">
                <SecondaryButton
                    @click="handleCancel"
                    class="flex items-center gap-2 px-4 py-2.5"
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
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    <span class="hidden sm:inline">Back to Schedules</span>
                    <span class="sm:hidden">Back</span>
                </SecondaryButton>

                <PrimaryButton
                    @click="submitForm"
                    :disabled="form.processing || isSubmitting"
                    class="flex items-center gap-2 px-4 py-2.5 min-w-[120px]"
                >
                    <span
                        v-if="form.processing || isSubmitting"
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
                        <span class="hidden sm:inline">Saving...</span>
                    </span>
                    <span v-else class="flex items-center gap-2">
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
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                            />
                        </svg>
                        <span class="hidden sm:inline"
                            >{{
                                isEditing ? "Update" : "Create"
                            }}
                            Schedule</span
                        >
                        <span class="sm:hidden">{{
                            isEditing ? "Update" : "Save"
                        }}</span>
                    </span>
                </PrimaryButton>

                <DangerButton
                    v-if="
                        isEditing &&
                        (auth.user?.user_type === 'admin' ||
                            auth.user?.user_type === 'doctor')
                    "
                    @click="showDeleteModal = true"
                    class="flex items-center gap-2 px-4 py-2.5"
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
                    <span class="hidden sm:inline">Delete</span>
                </DangerButton>
            </div>
        </template>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-600">
                    <li>
                        <router-link
                            to="/schedules"
                            class="hover:text-gray-900 transition-colors"
                        >
                            Schedules
                        </router-link>
                    </li>
                    <li class="flex items-center">
                        <svg
                            class="w-4 h-4 mx-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </li>
                    <li class="font-medium text-gray-900">
                        {{ isEditing ? "Edit Schedule" : "New Schedule" }}
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Form -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <!-- Form Header -->
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">
                                        {{
                                            isEditing
                                                ? "Edit Schedule"
                                                : "Create New Schedule"
                                        }}
                                    </h2>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{
                                            isEditing
                                                ? "Update schedule details and availability"
                                                : "Configure doctor availability and appointment settings"
                                        }}
                                    </p>
                                </div>
                                <div class="hidden sm:block">
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-full bg-white border border-blue-200 text-blue-700"
                                    >
                                        {{ isEditing ? "Editing" : "Creating" }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="p-6 space-y-8">
                            <!-- Doctor Selection -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Doctor Information
                                    </h3>
                                    <span class="text-xs text-red-600"
                                        >Required</span
                                    >
                                </div>

                                <div>
                                    <InputLabel
                                        for="doctor_id"
                                        value="Select Doctor"
                                        class="mb-2"
                                    />
                                    <div class="relative">
                                        <SelectInput
                                            id="doctor_id"
                                            v-model="form.doctor_id"
                                            :class="[
                                                'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                form.errors.doctor_id
                                                    ? 'border-red-300 focus:border-red-500'
                                                    : 'border-gray-300 focus:border-blue-500',
                                            ]"
                                        >
                                            <option value="" disabled>
                                                Select a doctor
                                            </option>
                                            <option
                                                v-for="doctor in doctors"
                                                :key="doctor.id"
                                                :value="doctor.id"
                                            >
                                                Dr. {{ doctor.last_name }},
                                                {{ doctor.first_name }}
                                                <template
                                                    v-if="doctor.specialization"
                                                >
                                                    -
                                                    {{ doctor.specialization }}
                                                </template>
                                            </option>
                                        </SelectInput>
                                        <div
                                            class="absolute left-3 top-3 text-gray-400"
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
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <InputError
                                        :message="form.errors.doctor_id"
                                        class="mt-1"
                                    />
                                    <p class="mt-2 text-sm text-gray-500">
                                        Select the doctor for this schedule
                                    </p>
                                </div>
                            </div>

                            <!-- Day and Time -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Schedule Timing
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Day Selection -->
                                    <div>
                                        <InputLabel
                                            for="day_of_week"
                                            value="Day of Week"
                                            class="mb-2"
                                        />
                                        <div class="relative">
                                            <SelectInput
                                                id="day_of_week"
                                                v-model="form.day_of_week"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                    form.errors.day_of_week
                                                        ? 'border-red-300 focus:border-red-500'
                                                        : 'border-gray-300 focus:border-blue-500',
                                                ]"
                                            >
                                                <option value="" disabled>
                                                    Select day
                                                </option>
                                                <option
                                                    v-for="weekday in weekdays"
                                                    :key="weekday"
                                                    :value="weekday"
                                                >
                                                    {{ weekday }}
                                                </option>
                                            </SelectInput>
                                            <div
                                                class="absolute left-3 top-3 text-gray-400"
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
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="form.errors.day_of_week"
                                            class="mt-1"
                                        />
                                    </div>

                                    <!-- Time Range -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel
                                                for="start_time"
                                                value="Start Time"
                                                class="mb-2"
                                            />
                                            <div class="relative">
                                                <TextInput
                                                    id="start_time"
                                                    type="time"
                                                    v-model="form.start_time"
                                                    :class="[
                                                        'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                        form.errors
                                                            .start_time ||
                                                        hasTimeConflict
                                                            ? 'border-red-300 focus:border-red-500'
                                                            : 'border-gray-300 focus:border-blue-500',
                                                    ]"
                                                />
                                                <div
                                                    class="absolute left-3 top-3 text-gray-400"
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
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <InputError
                                                :message="
                                                    form.errors.start_time
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div>
                                            <InputLabel
                                                for="end_time"
                                                value="End Time"
                                                class="mb-2"
                                            />
                                            <div class="relative">
                                                <TextInput
                                                    id="end_time"
                                                    type="time"
                                                    v-model="form.end_time"
                                                    :class="[
                                                        'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                        form.errors.end_time
                                                            ? 'border-red-300 focus:border-red-500'
                                                            : 'border-gray-300 focus:border-blue-500',
                                                    ]"
                                                />
                                                <div
                                                    class="absolute left-3 top-3 text-gray-400"
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
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <InputError
                                                :message="form.errors.end_time"
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Conflict Warning -->
                                <div
                                    v-if="hasTimeConflict"
                                    class="p-4 bg-red-50 border border-red-200 rounded-lg"
                                >
                                    <div class="flex items-start">
                                        <svg
                                            class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0"
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
                                        <div>
                                            <p
                                                class="text-sm font-medium text-red-800"
                                            >
                                                Schedule Conflict Detected
                                            </p>
                                            <p
                                                class="text-sm text-red-700 mt-1"
                                            >
                                                This doctor already has a
                                                schedule on
                                                {{ form.day_of_week }} during
                                                this time period. Please adjust
                                                the timing to avoid conflicts.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment Settings -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Appointment Settings
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Slot Duration -->
                                    <div>
                                        <InputLabel
                                            for="slot_duration"
                                            value="Slot Duration (minutes)"
                                            class="mb-2"
                                        />
                                        <div class="relative">
                                            <TextInput
                                                id="slot_duration"
                                                v-model="form.slot_duration"
                                                type="number"
                                                min="15"
                                                max="120"
                                                step="5"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                    form.errors.slot_duration
                                                        ? 'border-red-300 focus:border-red-500'
                                                        : 'border-gray-300 focus:border-blue-500',
                                                ]"
                                            />
                                            <div
                                                class="absolute left-3 top-3 text-gray-400"
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
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="form.errors.slot_duration"
                                            class="mt-1"
                                        />
                                        <p class="mt-2 text-sm text-gray-500">
                                            Duration for each appointment slot
                                            (15-120 minutes)
                                        </p>
                                    </div>

                                    <!-- Max Patients per Slot -->
                                    <div>
                                        <InputLabel
                                            for="max_patients_per_slot"
                                            value="Max Patients per Slot"
                                            class="mb-2"
                                        />
                                        <div class="relative">
                                            <TextInput
                                                id="max_patients_per_slot"
                                                v-model="
                                                    form.max_patients_per_slot
                                                "
                                                type="number"
                                                min="1"
                                                max="10"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                    form.errors
                                                        .max_patients_per_slot
                                                        ? 'border-red-300 focus:border-red-500'
                                                        : 'border-gray-300 focus:border-blue-500',
                                                ]"
                                            />
                                            <div
                                                class="absolute left-3 top-3 text-gray-400"
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
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <InputError
                                            :message="
                                                form.errors
                                                    .max_patients_per_slot
                                            "
                                            class="mt-1"
                                        />
                                        <p class="mt-2 text-sm text-gray-500">
                                            Maximum number of patients that can
                                            book the same slot
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Schedule Status
                                </h3>

                                <div
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
                                >
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <Checkbox
                                                id="is_active"
                                                v-model:checked="form.is_active"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                            />
                                            <div>
                                                <label
                                                    for="is_active"
                                                    class="text-sm font-medium text-gray-900 cursor-pointer"
                                                >
                                                    Active Schedule
                                                </label>
                                                <p
                                                    class="text-sm text-gray-600 mt-1"
                                                >
                                                    When active, patients can
                                                    book appointments during
                                                    this schedule
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium',
                                            form.is_active
                                                ? 'bg-green-100 text-green-800 border border-green-200'
                                                : 'bg-red-100 text-red-800 border border-red-200',
                                        ]"
                                    >
                                        {{
                                            form.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </div>
                                </div>
                                <InputError
                                    :message="form.errors.is_active"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Form Status -->
                            <div
                                v-if="form.recentlySuccessful"
                                class="p-4 bg-green-50 border border-green-200 rounded-lg"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 text-green-600 mr-3"
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
                                    <div>
                                        <p
                                            class="text-sm font-medium text-green-800"
                                        >
                                            Schedule
                                            {{
                                                isEditing
                                                    ? "updated"
                                                    : "created"
                                            }}
                                            successfully!
                                        </p>
                                        <p class="text-sm text-green-700 mt-1">
                                            Redirecting to schedules page...
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="form.hasErrors && showValidationErrors"
                                class="p-4 bg-red-50 border border-red-200 rounded-lg"
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
                                    <div>
                                        <p
                                            class="text-sm font-medium text-red-800"
                                        >
                                            Please correct the errors below
                                        </p>
                                        <ul
                                            class="text-sm text-red-700 mt-1 list-disc list-inside"
                                        >
                                            <li
                                                v-for="error in Object.values(
                                                    form.errors
                                                )"
                                                :key="error"
                                            >
                                                {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Footer -->
                        <div
                            class="px-6 py-4 border-t border-gray-200 bg-gray-50"
                        >
                            <div
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                            >
                                <div class="text-sm text-gray-600">
                                    <span v-if="isEditing">
                                        Last updated:
                                        {{
                                            new Date(
                                                schedule.updated_at
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                    <span v-else>
                                        <span class="text-red-600">*</span>
                                        indicates required field
                                    </span>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <SecondaryButton
                                        @click="handleCancel"
                                        class="w-full sm:w-auto"
                                    >
                                        Cancel
                                    </SecondaryButton>
                                    <PrimaryButton
                                        @click="submitForm"
                                        :disabled="
                                            form.processing || isSubmitting
                                        "
                                        class="w-full sm:w-auto"
                                    >
                                        <span
                                            v-if="
                                                form.processing || isSubmitting
                                            "
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
                                            Saving...
                                        </span>
                                        <span v-else>
                                            {{
                                                isEditing
                                                    ? "Update Schedule"
                                                    : "Create Schedule"
                                            }}
                                        </span>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Preview & Info -->
                <div class="space-y-6">
                    <!-- Schedule Preview -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Schedule Preview
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Real-time schedule information
                            </p>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Current Status -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700"
                                    >Current Status:</span
                                >
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        form.is_active
                                            ? 'bg-green-100 text-green-800 border border-green-200'
                                            : 'bg-red-100 text-red-800 border border-red-200',
                                    ]"
                                >
                                    {{ form.is_active ? "Active" : "Inactive" }}
                                </span>
                            </div>

                            <!-- Schedule Summary -->
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div
                                            class="text-2xl font-bold text-blue-700"
                                        >
                                            {{ calculatedSlots }}
                                        </div>
                                        <div
                                            class="text-xs text-blue-600 font-medium"
                                        >
                                            Total Slots
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div
                                            class="text-2xl font-bold text-green-700"
                                        >
                                            {{ totalPatientsPerDay }}
                                        </div>
                                        <div
                                            class="text-xs text-green-600 font-medium"
                                        >
                                            Patients/Day
                                        </div>
                                    </div>
                                </div>

                                <!-- Duration Info -->
                                <div class="pt-4 border-t border-gray-200">
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="text-gray-600"
                                            >Duration:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900"
                                            >{{ calculateDuration() }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex items-center justify-between text-sm mt-2"
                                    >
                                        <span class="text-gray-600"
                                            >Time Range:</span
                                        >
                                        <span class="font-medium text-gray-900">
                                            {{ formatTime(form.start_time) }} -
                                            {{ formatTime(form.end_time) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Status -->
                            <div
                                v-if="
                                    showValidationErrors &&
                                    (!validateTimes() || hasTimeConflict)
                                "
                                class="p-4 bg-amber-50 border border-amber-200 rounded-lg"
                            >
                                <div class="flex items-start">
                                    <svg
                                        class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0"
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
                                    <div>
                                        <p
                                            class="text-sm font-medium text-amber-800"
                                        >
                                            Validation Required
                                        </p>
                                        <p class="text-sm text-amber-700 mt-1">
                                            Please fix the schedule timing
                                            issues before saving.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help & Tips -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Help & Tips
                            </h3>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Slot Duration
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Typical appointment durations are 15,
                                        30, or 60 minutes depending on the
                                        specialty.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-green-600"
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
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Time Conflicts
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        The system automatically checks for
                                        overlapping schedules to prevent
                                        conflicts.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-purple-600"
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
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Security
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Only authorized staff can create or
                                        modify schedules. All changes are
                                        logged.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            max-width="lg"
        >
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </div>

                <div class="mt-4 text-center">
                    <h3 class="text-xl font-bold text-gray-900">
                        Delete Schedule
                    </h3>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete the schedule for
                            <span class="font-semibold text-gray-900">
                                {{
                                    schedule.user
                                        ? `Dr. ${schedule.user.first_name} ${schedule.user.last_name}`
                                        : "this doctor"
                                }}
                            </span>
                            on
                            <span class="font-semibold text-gray-900">{{
                                schedule.day_of_week
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
                                        <li>
                                            All appointment slots will be
                                            removed
                                        </li>
                                        <li>
                                            Future appointments will be
                                            cancelled
                                        </li>
                                        <li>
                                            Patients will be notified
                                            automatically
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
                        :disabled="form.processing"
                        class="w-full sm:w-auto order-1 sm:order-2"
                    >
                        <span
                            v-if="form.processing"
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
                            Delete Schedule
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

/* Improved focus styles */
:deep(:focus-visible) {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 0.375rem;
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

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
