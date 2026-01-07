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
    scheduleExp: {
        type: Object,
        default: null,
    },
    doctors: {
        type: Array,
        required: true,
    },
    existingExceptions: {
        type: Array,
        default: () => [],
    },
});

// Computed properties
const scheduleExp = computed(() => props.scheduleExp || {});
const isEditing = computed(() => !!props.scheduleExp?.id);
const pageTitle = computed(() =>
    isEditing.value
        ? `Edit Exception - ${formatExceptionName()}`
        : "Create Schedule Exception"
);
const pageSubtitle = computed(() =>
    isEditing.value
        ? "Update exception details and availability"
        : "Add an exception for special dates, holidays, or doctor unavailability"
);

// Form state
const form = useForm({
    doctor_id: props.scheduleExp?.doctor_id || "",
    exception_date: props.scheduleExp?.exception_date || "",
    start_time: props.scheduleExp?.start_time || "09:00",
    end_time: props.scheduleExp?.end_time || "17:00",
    is_available: props.scheduleExp?.is_available ?? false,
    reason: props.scheduleExp?.reason || "",
});

// UI State
const showDeleteModal = ref(false);
const showValidationErrors = ref(false);
const isSubmitting = ref(false);
const selectedDoctor = ref(null);

// Date helpers
const today = new Date().toISOString().split("T")[0];
const maxDate = new Date();
maxDate.setFullYear(maxDate.getFullYear() + 1);
const maxDateString = maxDate.toISOString().split("T")[0];

// Computed properties
const canManageExceptions = computed(() => {
    return ["admin", "doctor", "nurse"].includes(auth.value.user?.user_type);
});

const formatExceptionName = () => {
    if (scheduleExp.value.user) {
        return `Dr. ${scheduleExp.value.user.last_name}, ${
            scheduleExp.value.user.first_name
        } - ${formatDate(scheduleExp.value.exception_date)}`;
    }
    return `Exception #${scheduleExp.value.id}`;
};

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

// Get day name from date
const getDayName = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { weekday: "long" });
};

// Time validation
const validateTimes = () => {
    if (!form.start_time || !form.end_time) return true;

    const start = new Date(`1970-01-01T${form.start_time}`);
    const end = new Date(`1970-01-01T${form.end_time}`);

    return end > start;
};

const calculateDuration = () => {
    if (!form.start_time || !form.end_time) return 0;

    const start = new Date(`1970-01-01T${form.start_time}`);
    const end = new Date(`1970-01-01T${form.end_time}`);
    return (end - start) / (1000 * 60 * 60); // hours
};

// Check for date conflicts
const hasDateConflict = computed(() => {
    if (!form.doctor_id || !form.exception_date) {
        return false;
    }

    return props.existingExceptions.some((existing) => {
        // Skip the current exception when editing
        if (isEditing.value && existing.id === scheduleExp.value.id)
            return false;

        if (
            existing.doctor_id == form.doctor_id &&
            existing.exception_date === form.exception_date
        ) {
            return true;
        }
        return false;
    });
});

// Form validation
const validateForm = () => {
    showValidationErrors.value = true;

    if (!validateTimes()) {
        form.setError("end_time", "End time must be after start time");
        return false;
    }

    if (hasDateConflict.value) {
        form.setError(
            "exception_date",
            "Doctor already has an exception on this date"
        );
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
        form.put(`/schedule-exceptions/update/${props.scheduleExp.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setTimeout(() => {
                    router.visit("/schedule-exceptions");
                }, 1500);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else {
        form.post("/schedule-exceptions/store", {
            preserveScroll: true,
            onSuccess: () => {
                setTimeout(() => {
                    router.visit("/schedule-exceptions");
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
            router.visit("/schedule-exceptions");
        }
    } else {
        router.visit("/schedule-exceptions");
    }
};

// Delete functionality
const confirmDelete = () => {
    router.delete(`/schedule-exceptions/destroy/${props.scheduleExp.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            router.visit("/schedule-exceptions");
        },
    });
};

// Watch for doctor selection
watch(
    () => form.doctor_id,
    (newDoctorId) => {
        selectedDoctor.value = props.doctors.find((d) => d.id == newDoctorId);
    }
);

// Initialize form
onMounted(() => {
    if (isEditing.value) {
        document.title = `Edit Exception - ${formatExceptionName()}`;
    }

    // Set default date to today if not editing
    if (!isEditing.value && !form.exception_date) {
        form.exception_date = today;
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
                    <span class="hidden sm:inline">Back to Exceptions</span>
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
                            Exception</span
                        >
                        <span class="sm:hidden">{{
                            isEditing ? "Update" : "Save"
                        }}</span>
                    </span>
                </PrimaryButton>

                <DangerButton
                    v-if="isEditing && canManageExceptions"
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
                            to="/schedule-exceptions"
                            class="hover:text-gray-900 transition-colors"
                        >
                            Schedule Exceptions
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
                        {{ isEditing ? "Edit Exception" : "New Exception" }}
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
                                                ? "Edit Schedule Exception"
                                                : "Create Schedule Exception"
                                        }}
                                    </h2>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{
                                            isEditing
                                                ? "Update exception details and availability"
                                                : "Configure special availability or unavailability for a doctor"
                                        }}
                                    </p>
                                </div>
                                <div class="hidden sm:block">
                                    <span
                                        :class="[
                                            'px-3 py-1 text-xs font-medium rounded-full border',
                                            form.is_available
                                                ? 'bg-green-100 text-green-800 border-green-200'
                                                : 'bg-red-100 text-red-800 border-red-200',
                                        ]"
                                    >
                                        {{
                                            form.is_available
                                                ? "Available"
                                                : "Unavailable"
                                        }}
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
                                </div>
                            </div>

                            <!-- Date and Time -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Exception Details
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Date Selection -->
                                    <div>
                                        <InputLabel
                                            for="exception_date"
                                            value="Exception Date"
                                            class="mb-2"
                                        />
                                        <div class="relative">
                                            <TextInput
                                                id="exception_date"
                                                type="date"
                                                v-model="form.exception_date"
                                                :min="today"
                                                :max="maxDateString"
                                                :class="[
                                                    'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                    form.errors
                                                        .exception_date ||
                                                    hasDateConflict
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
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div
                                            class="flex justify-between items-center mt-1"
                                        >
                                            <InputError
                                                :message="
                                                    form.errors.exception_date
                                                "
                                            />
                                            <span
                                                class="text-xs text-gray-500"
                                                v-if="form.exception_date"
                                            >
                                                {{
                                                    getDayName(
                                                        form.exception_date
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Time Range -->
                                    <div>
                                        <InputLabel
                                            value="Time Range"
                                            class="mb-2"
                                        />
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="relative">
                                                    <TextInput
                                                        id="start_time"
                                                        type="time"
                                                        v-model="
                                                            form.start_time
                                                        "
                                                        :class="[
                                                            'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                            form.errors
                                                                .start_time ||
                                                            !validateTimes()
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
                                                <div class="relative">
                                                    <TextInput
                                                        id="end_time"
                                                        type="time"
                                                        v-model="form.end_time"
                                                        :class="[
                                                            'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                            form.errors
                                                                .end_time ||
                                                            !validateTimes()
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
                                                        form.errors.end_time
                                                    "
                                                    class="mt-1"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Set the time range for this
                                            exception
                                        </p>
                                    </div>
                                </div>

                                <!-- Date Conflict Warning -->
                                <div
                                    v-if="hasDateConflict"
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
                                                Date Conflict Detected
                                            </p>
                                            <p
                                                class="text-sm text-red-700 mt-1"
                                            >
                                                This doctor already has an
                                                exception on
                                                {{
                                                    formatDate(
                                                        form.exception_date
                                                    )
                                                }}. Please select a different
                                                date or edit the existing
                                                exception.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Reason & Notes
                                </h3>

                                <div>
                                    <InputLabel
                                        for="reason"
                                        value="Reason for Exception"
                                        class="mb-2"
                                    />
                                    <div class="relative">
                                        <textarea
                                            id="reason"
                                            v-model="form.reason"
                                            rows="4"
                                            placeholder="e.g., Doctor on vacation, Holiday schedule, Special clinic hours, etc."
                                            :class="[
                                                'w-full pl-10 pr-4 py-3 border rounded-lg transition-colors',
                                                form.errors.reason
                                                    ? 'border-red-300 focus:border-red-500'
                                                    : 'border-gray-300 focus:border-blue-500',
                                            ]"
                                        >
                                        </textarea>
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
                                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <InputError
                                        :message="form.errors.reason"
                                        class="mt-1"
                                    />
                                    <p class="mt-2 text-sm text-gray-500">
                                        Provide a clear reason for this
                                        exception. This helps with record
                                        keeping and communication.
                                    </p>
                                </div>
                            </div>

                            <!-- Availability Status -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Availability Status
                                </h3>

                                <div
                                    class="flex items-center justify-between p-4 rounded-lg border"
                                    :class="[
                                        form.is_available
                                            ? 'bg-green-50 border-green-200'
                                            : 'bg-red-50 border-red-200',
                                    ]"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center h-5">
                                            <Checkbox
                                                id="is_available"
                                                v-model:checked="
                                                    form.is_available
                                                "
                                                :class="[
                                                    'w-5 h-5 rounded focus:ring-2 focus:ring-offset-2',
                                                    form.is_available
                                                        ? 'text-green-600 focus:ring-green-500'
                                                        : 'text-red-600 focus:ring-red-500',
                                                ]"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                for="is_available"
                                                class="text-sm font-medium cursor-pointer"
                                                :class="[
                                                    form.is_available
                                                        ? 'text-green-900'
                                                        : 'text-red-900',
                                                ]"
                                            >
                                                {{
                                                    form.is_available
                                                        ? "Doctor is available"
                                                        : "Doctor is unavailable"
                                                }}
                                            </label>
                                            <p
                                                class="text-sm mt-1"
                                                :class="[
                                                    form.is_available
                                                        ? 'text-green-700'
                                                        : 'text-red-700',
                                                ]"
                                            >
                                                {{
                                                    form.is_available
                                                        ? "Doctor will be available for appointments during this exception."
                                                        : "Doctor will not be available for appointments during this exception."
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium',
                                            form.is_available
                                                ? 'bg-green-100 text-green-800 border border-green-200'
                                                : 'bg-red-100 text-red-800 border border-red-200',
                                        ]"
                                    >
                                        {{
                                            form.is_available
                                                ? "Available"
                                                : "Unavailable"
                                        }}
                                    </div>
                                </div>
                                <InputError
                                    :message="form.errors.is_available"
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
                                            Exception
                                            {{
                                                isEditing
                                                    ? "updated"
                                                    : "created"
                                            }}
                                            successfully!
                                        </p>
                                        <p class="text-sm text-green-700 mt-1">
                                            Redirecting to exceptions page...
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
                                                scheduleExp.updated_at
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
                                                    ? "Update Exception"
                                                    : "Create Exception"
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
                    <!-- Exception Preview -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Exception Preview
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Real-time exception information
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
                                        form.is_available
                                            ? 'bg-green-100 text-green-800 border border-green-200'
                                            : 'bg-red-100 text-red-800 border border-red-200',
                                    ]"
                                >
                                    {{
                                        form.is_available
                                            ? "Available"
                                            : "Unavailable"
                                    }}
                                </span>
                            </div>

                            <!-- Exception Summary -->
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div
                                            class="text-2xl font-bold text-blue-700"
                                        >
                                            {{
                                                formatDate(form.exception_date)
                                                    ? "Set"
                                                    : "Not Set"
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-blue-600 font-medium"
                                        >
                                            Exception Date
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div
                                            class="text-2xl font-bold text-green-700"
                                        >
                                            {{ calculateDuration().toFixed(1) }}
                                        </div>
                                        <div
                                            class="text-xs text-green-600 font-medium"
                                        >
                                            Hours
                                        </div>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div
                                    class="pt-4 border-t border-gray-200 space-y-3"
                                >
                                    <div
                                        v-if="form.exception_date"
                                        class="text-sm"
                                    >
                                        <span class="text-gray-600">Date:</span>
                                        <span
                                            class="font-medium text-gray-900 ml-2"
                                            >{{
                                                formatDate(form.exception_date)
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-if="form.start_time && form.end_time"
                                        class="text-sm"
                                    >
                                        <span class="text-gray-600">Time:</span>
                                        <span
                                            class="font-medium text-gray-900 ml-2"
                                        >
                                            {{ formatTime(form.start_time) }} -
                                            {{ formatTime(form.end_time) }}
                                        </span>
                                    </div>
                                    <div v-if="selectedDoctor" class="text-sm">
                                        <span class="text-gray-600"
                                            >Doctor:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 ml-2"
                                        >
                                            Dr. {{ selectedDoctor.last_name }},
                                            {{ selectedDoctor.first_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Status -->
                            <div
                                v-if="
                                    showValidationErrors &&
                                    (!validateTimes() || hasDateConflict)
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
                                            Please fix the validation errors
                                            before saving.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help & Examples -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Common Use Cases
                            </h3>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-red-600"
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
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Doctor Unavailable
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Use for vacations, sick days,
                                        conferences, or personal time off.
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
                                        Extra Availability
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Add extra working hours on holidays or
                                        weekends for special clinics.
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
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Holiday Schedule
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Set different hours for public holidays
                                        or special occasions.
                                    </p>
                                </div>
                            </div>

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
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Time Changes
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Modify regular schedule hours for
                                        specific dates (early closure, late
                                        start).
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
                        Delete Schedule Exception
                    </h3>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete the exception for
                            <span class="font-semibold text-gray-900">
                                {{
                                    scheduleExp.user
                                        ? `Dr. ${scheduleExp.user.first_name} ${scheduleExp.user.last_name}`
                                        : "this doctor"
                                }}
                            </span>
                            on
                            <span class="font-semibold text-gray-900">{{
                                formatDate(scheduleExp.exception_date)
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
                                            Any affected appointments will be
                                            notified
                                        </li>
                                        <li>
                                            Doctor availability will be reverted
                                            to regular schedule
                                        </li>
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
