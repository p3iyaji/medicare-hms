<script setup>
import { router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, watch, reactive, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { ChevronDownIcon } from "@heroicons/vue/20/solid";
import { PlusIcon } from "@heroicons/vue/20/solid";
import { CalendarIcon } from "@heroicons/vue/20/solid";
import { ClockIcon } from "@heroicons/vue/20/solid";
import { DocumentTextIcon } from "@heroicons/vue/20/solid";
import { DocumentDuplicateIcon } from "@heroicons/vue/20/solid";
import { UserIcon } from "@heroicons/vue/20/solid";
import { CheckCircleIcon } from "@heroicons/vue/20/solid";
import { XCircleIcon } from "@heroicons/vue/20/solid";
import { EyeIcon } from "@heroicons/vue/20/solid";
import { PencilIcon } from "@heroicons/vue/20/solid";
import { TrashIcon } from "@heroicons/vue/20/solid";
import { ClipboardDocumentListIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    appointments: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            status: "",
            appointment_type: "",
            priority: "",
            date_range: "",
            doctor_id: "",
        }),
    },
    doctors: {
        type: Array,
        default: () => [],
    },
    noteTemplates: {
        type: Array,
        default: () => [],
    },
    currentDoctor: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });
const isDoctor = computed(() => auth.value.user?.user_type === "doctor");
// Flash message handling
const flash = ref(
    page.props.flash || { success: null, error: null, warning: null },
);

const welcomeMessage = computed(() => {
    return isDoctor.value
        ? "Manage Your Appointments & Notes"
        : "Manage Appointments";
});

// search and filters
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const typeFilter = ref(props.filters.appointment_type || "");
const priorityFilter = ref(props.filters.priority || "");
const dateRangeFilter = ref(props.filters.date_range || "");
const doctorFilter = ref(
    props.filters.doctor_id || (isDoctor.value ? auth.value.user?.id : ""),
);

// modals state
const showDeleteModal = ref(false);
const showNoteModal = ref(false);
const showTemplateModal = ref(false);
const showTemplateManagerModal = ref(false);
const selectedAppointment = ref(null);
const selectedNoteTemplate = ref(null);
const processingAction = ref(false);

// Appointment notes
const appointmentNotes = ref("");
const noteTitle = ref("");
const isPrivateNote = ref(false);

// Note template management
const templateName = ref("");
const templateContent = ref("");
const templateCategory = ref("consultation");
const editTemplateId = ref(null);

// Template categories
const templateCategories = [
    { value: "consultation", label: "Consultation" },
    { value: "followup", label: "Follow-up" },
    { value: "procedure", label: "Procedure" },
    { value: "emergency", label: "Emergency" },
    { value: "routine", label: "Routine Check" },
    { value: "referral", label: "Referral" },
    { value: "discharge", label: "Discharge" },
    { value: "other", label: "Other" },
];

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
            "/doctor/appointments",
            {
                search: search.value,
                status: statusFilter.value,
                appointment_type: typeFilter.value,
                priority: priorityFilter.value,
                date_range: dateRangeFilter.value,
                doctor_id: doctorFilter.value,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ["appointments", "filters"],
            },
        );
    }, 300);
};

// Status options
const statusOptions = [
    { value: "", label: "All Status" },
    { value: "scheduled", label: "Scheduled" },
    { value: "confirmed", label: "Confirmed" },
    { value: "in-progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "no-show", label: "No Show" },
    { value: "cancelled", label: "Cancelled" },
    { value: "rescheduled", label: "Rescheduled" },
];

const typeOptions = [
    { value: "", label: "All Types" },
    { value: "consultation", label: "Consultation" },
    { value: "followup", label: "Follow-up" },
    { value: "emergency", label: "Emergency" },
    { value: "routine", label: "Routine Check" },
    { value: "procedure", label: "Procedure" },
    { value: "vaccination", label: "Vaccination" },
    { value: "therapy", label: "Therapy" },
];

const priorityOptions = [
    { value: "", label: "All Priorities" },
    { value: "low", label: "Low" },
    { value: "medium", label: "Medium" },
    { value: "high", label: "High" },
    { value: "emergency", label: "Emergency" },
];

// Check for active filters
const hasActiveFilters = computed(() => {
    return (
        search.value ||
        statusFilter.value ||
        typeFilter.value ||
        priorityFilter.value ||
        dateRangeFilter.value ||
        (doctorFilter.value && !isDoctor.value)
    );
});

// Clear all filters
const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    typeFilter.value = "";
    priorityFilter.value = "";
    dateRangeFilter.value = "";
    if (!isDoctor.value) {
        doctorFilter.value = "";
    }
};

// Watch filters for changes
watch(
    [
        search,
        statusFilter,
        typeFilter,
        priorityFilter,
        dateRangeFilter,
        doctorFilter,
    ],
    () => {
        applyFilters();
    },
    { deep: true },
);

// Watch for flash messages
watch(
    () => page.props.flash,
    (newFlash) => {
        flash.value = newFlash;
    },
    { deep: true },
);

// Action handlers
const handleDelete = (appointment) => {
    selectedAppointment.value = appointment;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!selectedAppointment.value) return;

    processingAction.value = true;
    try {
        await router.delete(
            `/appointments/destroy/${selectedAppointment.value.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteModal.value = false;
                    selectedAppointment.value = null;
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

// Note management
const openNoteModal = (appointment) => {
    selectedAppointment.value = appointment;
    appointmentNotes.value = appointment.notes || "";
    noteTitle.value = `Notes for ${appointment.patient?.first_name} ${appointment.patient?.last_name}`;
    isPrivateNote.value = false;
    showNoteModal.value = true;
};

const saveAppointmentNotes = async () => {
    if (!selectedAppointment.value) return;

    processingAction.value = true;
    try {
        await router.post(
            `/appointments/${selectedAppointment.value.id}/notes`,
            {
                notes: appointmentNotes.value,
                is_private: isPrivateNote.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    showNoteModal.value = false;
                    // Refresh appointments to get updated notes
                    router.reload({ only: ["appointments"] });
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

// Template management
const openTemplateModal = () => {
    showTemplateModal.value = true;
    resetTemplateForm();
};

const resetTemplateForm = () => {
    templateName.value = "";
    templateContent.value = "";
    templateCategory.value = "consultation";
    editTemplateId.value = null;
};

const saveNoteTemplate = async () => {
    if (!templateName.value.trim() || !templateContent.value.trim()) {
        alert("Please fill in all required fields");
        return;
    }

    processingAction.value = true;
    const url = editTemplateId.value
        ? `/note-templates/${editTemplateId.value}`
        : "/note-templates/store";

    const method = editTemplateId.value ? "put" : "post";

    try {
        await router[method](
            url,
            {
                name: templateName.value,
                content: templateContent.value,
                category: templateCategory.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    showTemplateModal.value = false;
                    resetTemplateForm();
                    router.reload({ only: ["noteTemplates"] });
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

const editNoteTemplate = (template) => {
    selectedNoteTemplate.value = template;
    templateName.value = template.name;
    templateContent.value = template.content;
    templateCategory.value = template.category;
    editTemplateId.value = template.id;
    showTemplateModal.value = true;
};

const deleteNoteTemplate = async (templateId) => {
    if (!confirm("Are you sure you want to delete this template?")) return;

    processingAction.value = true;
    try {
        await router.delete(`/note-templates/${templateId}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ["noteTemplates"] });
            },
            onFinish: () => {
                processingAction.value = false;
            },
        });
    } catch (error) {
        processingAction.value = false;
    }
};

const useTemplate = (template) => {
    if (appointmentNotes.value) {
        appointmentNotes.value += "\n\n" + template.content;
    } else {
        appointmentNotes.value = template.content;
    }
};

const openTemplateManager = () => {
    showTemplateManagerModal.value = true;
};

// Appointment status management
const updateAppointmentStatus = async (appointmentId, status) => {
    processingAction.value = true;
    try {
        await router.put(
            `/appointments/${appointmentId}/status`,
            {
                status: status,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ["appointments"] });
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

// Format date and time
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatTime = (time) => {
    if (!time) return "";
    return new Date(`2000-01-01T${time}`).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

// Navigate to appointment details
const viewAppointmentDetails = (appointmentId) => {
    router.visit(`/appointments/${appointmentId}`);
};

// Start consultation
const startConsultation = (appointmentId) => {
    router.visit(`/consultation/${appointmentId}`);
};

// Get status color
const getStatusColor = (status) => {
    const colors = {
        scheduled: "bg-blue-100 text-blue-800",
        confirmed: "bg-green-100 text-green-800",
        "in-progress": "bg-yellow-100 text-yellow-800",
        completed: "bg-gray-100 text-gray-800",
        cancelled: "bg-red-100 text-red-800",
        "no-show": "bg-red-100 text-red-800",
        rescheduled: "bg-purple-100 text-purple-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

// Get priority color
const getPriorityColor = (priority) => {
    const colors = {
        low: "bg-green-100 text-green-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        emergency: "bg-red-100 text-red-800",
    };
    return colors[priority] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <AppLayout title="Doctor's Appointments" :subtitle="welcomeMessage">
        <template #headerActions>
            <div class="flex items-center space-x-3">
                <PrimaryButton v-if="isDoctor" @click="openTemplateModal">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    New Note Template
                </PrimaryButton>
                <PrimaryButton @click="openTemplateManager" v-if="isDoctor">
                    <DocumentTextIcon class="w-5 h-5 mr-2" />
                    Manage Templates
                </PrimaryButton>
            </div>
        </template>

        <!-- Filters section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="flex justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    Appointment Filters
                </h3>
                <button
                    @click="clearFilters"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Clear all filters
                </button>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <InputLabel for="search" value="Search Patients" />
                        <TextInput
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Patient name, appointment #, symptoms..."
                            class="block w-full"
                        />
                    </div>

                    <!-- Doctor Filter (only for non-doctors) -->
                    <div v-if="!isDoctor">
                        <InputLabel for="doctor" value="Doctor" />
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
                                Dr. {{ doctor.first_name }}
                                {{ doctor.last_name }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <InputLabel for="status" value="Status" />
                        <SelectInput
                            id="status"
                            v-model="statusFilter"
                            class="w-full"
                        >
                            <option
                                v-for="status in statusOptions"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <InputLabel for="type" value="Type" />
                        <SelectInput
                            id="type"
                            v-model="typeFilter"
                            class="w-full"
                        >
                            <option
                                v-for="type in typeOptions"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <InputLabel for="priority" value="Priority" />
                        <SelectInput
                            id="priority"
                            v-model="priorityFilter"
                            class="w-full"
                        >
                            <option
                                v-for="priority in priorityOptions"
                                :key="priority.value"
                                :value="priority.value"
                            >
                                {{ priority.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <InputLabel for="date_range" value="Date Range" />
                        <DateRangePicker
                            id="date_range"
                            v-model="dateRangeFilter"
                            placeholder="Select dates"
                            class="w-full"
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
                            <template v-if="search">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800"
                                >
                                    Search: "{{ search }}"
                                    <button
                                        @click="search = ''"
                                        class="ml-1 text-blue-600 hover:text-blue-800"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>
                            <template v-if="doctorFilter && !isDoctor">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-purple-100 text-purple-800"
                                >
                                    Doctor:
                                    {{
                                        doctors.find(
                                            (d) => d.id == doctorFilter,
                                        )?.first_name
                                    }}
                                    <button
                                        @click="doctorFilter = ''"
                                        class="ml-1 text-purple-600 hover:text-purple-800"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>
                            <template v-if="statusFilter">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800"
                                >
                                    {{
                                        statusOptions.find(
                                            (s) => s.value === statusFilter,
                                        )?.label
                                    }}
                                    <button
                                        @click="statusFilter = ''"
                                        class="ml-1 text-green-600 hover:text-green-800"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>
                            <template v-if="priorityFilter">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-orange-100 text-orange-800"
                                >
                                    {{
                                        priorityOptions.find(
                                            (p) => p.value === priorityFilter,
                                        )?.label
                                    }}
                                    <button
                                        @click="priorityFilter = ''"
                                        class="ml-1 text-orange-600 hover:text-orange-800"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>
                            <template v-if="dateRangeFilter">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800"
                                >
                                    Date:
                                    {{
                                        formatDateRangeForDisplay(
                                            dateRangeFilter,
                                        )
                                    }}
                                    <button
                                        @click="dateRangeFilter = ''"
                                        class="ml-1 text-indigo-600 hover:text-indigo-800"
                                    >
                                        ×
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Today's Appointments Card -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Today's Schedule
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <CalendarIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="appointment in appointments?.data?.filter(
                            (a) =>
                                new Date(a.scheduled_date).toDateString() ===
                                new Date().toDateString(),
                        )"
                        :key="appointment.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ appointment.patient?.first_name }}
                                {{ appointment.patient?.last_name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ formatTime(appointment.scheduled_time) }}
                            </div>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-full',
                                getStatusColor(appointment.status),
                            ]"
                        >
                            {{ appointment.status }}
                        </span>
                    </div>
                    <div
                        v-if="
                            !appointments?.data?.filter(
                                (a) =>
                                    new Date(
                                        a.scheduled_date,
                                    ).toDateString() ===
                                    new Date().toDateString(),
                            ).length
                        "
                        class="text-center py-4 text-gray-500"
                    >
                        No appointments scheduled for today
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments Card -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Upcoming
                        </h3>
                        <p class="text-sm text-gray-600">Next 7 days</p>
                    </div>
                    <div class="bg-green-100 p-2 rounded-lg">
                        <ClockIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="appointment in appointments?.data
                            ?.filter(
                                (a) =>
                                    new Date(a.scheduled_date) > new Date() &&
                                    new Date(a.scheduled_date) <=
                                        new Date(
                                            Date.now() +
                                                7 * 24 * 60 * 60 * 1000,
                                        ),
                            )
                            .slice(0, 5)"
                        :key="appointment.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ appointment.patient?.first_name }}
                                {{ appointment.patient?.last_name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ formatDate(appointment.scheduled_date) }}
                                <span class="mx-1">•</span>
                                {{ formatTime(appointment.scheduled_time) }}
                            </div>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-full',
                                getPriorityColor(appointment.priority),
                            ]"
                        >
                            {{ appointment.priority }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Note Templates Quick Access -->
            <div
                v-if="isDoctor"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Note Templates
                        </h3>
                        <p class="text-sm text-gray-600">Quick access</p>
                    </div>
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
                <div class="space-y-2">
                    <div
                        v-for="template in noteTemplates.slice(0, 5)"
                        :key="template.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 group"
                    >
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ template.name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ template.category }}
                            </div>
                        </div>
                        <button
                            @click="useTemplate(template)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-600 hover:text-blue-800"
                        >
                            <DocumentDuplicateIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <button
                        @click="openTemplateManager"
                        class="w-full mt-4 text-center text-blue-600 hover:text-blue-800 font-medium"
                    >
                        View all templates →
                    </button>
                </div>
            </div>
        </div>

        <!-- Appointments Table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        All Appointments ({{ appointments?.total || 0 }})
                    </h3>
                    <div class="text-sm text-gray-700">
                        <template v-if="appointments?.data?.length">
                            Showing {{ appointments.from }} to
                            {{ appointments.to }} of {{ appointments.total }}
                        </template>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Patient
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Doctor
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Date & Time
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Type & Priority
                            </th>
                            <th
                                class="px6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="appointment in appointments?.data || []"
                            :key="appointment.id"
                            class="hover:bg-gray-50"
                        >
                            <!-- Patient Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"
                                        >
                                            <UserIcon
                                                class="w-6 h-6 text-blue-600"
                                            />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{
                                                appointment.patient?.first_name
                                            }}
                                            {{ appointment.patient?.last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ appointment.patient_no }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Doctor Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ appointment.doctor?.first_name }}
                                    {{ appointment.doctor?.last_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{
                                        appointment.doctor?.specialization ||
                                        "General Practitioner"
                                    }}
                                </div>
                            </td>

                            <!-- Date & Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatDate(appointment.scheduled_date) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatTime(appointment.scheduled_time) }}
                                </div>
                            </td>

                            <!-- Type & Priority -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ appointment.appointment_type }}
                                    </span>
                                    <div>
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getPriorityColor(
                                                    appointment.priority,
                                                ),
                                            ]"
                                        >
                                            {{ appointment.priority }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            getStatusColor(appointment.status),
                                        ]"
                                    >
                                        {{ appointment.status }}
                                    </span>
                                    <div
                                        v-if="
                                            isDoctor &&
                                            appointment.status === 'scheduled'
                                        "
                                    >
                                        <button
                                            @click="
                                                updateAppointmentStatus(
                                                    appointment.id,
                                                    'confirmed',
                                                )
                                            "
                                            class="text-green-600 hover:text-green-800"
                                            title="Confirm Appointment"
                                        >
                                            <CheckCircleIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium relative"
                            >
                                <div class="flex items-center space-x-2">
                                    <!-- View Details -->
                                    <button
                                        @click="
                                            viewAppointmentDetails(
                                                appointment.id,
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-900"
                                        title="View Details"
                                    >
                                        <EyeIcon class="w-5 h-5" />
                                    </button>

                                    <!-- Add Notes -->
                                    <button
                                        v-if="isDoctor"
                                        @click="openNoteModal(appointment)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Add Notes"
                                    >
                                        <DocumentTextIcon class="w-5 h-5" />
                                    </button>

                                    <!-- Start Consultation -->
                                    <button
                                        v-if="
                                            isDoctor &&
                                            ['scheduled', 'confirmed'].includes(
                                                appointment.status,
                                            )
                                        "
                                        @click="
                                            startConsultation(appointment.id)
                                        "
                                        class="text-purple-600 hover:text-purple-900"
                                        title="Start Consultation"
                                    >
                                        <ClipboardDocumentListIcon
                                            class="w-5 h-5"
                                        />
                                    </button>

                                    <!-- More Actions Dropdown -->
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button
                                                class="inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            >
                                                <ChevronDownIcon
                                                    class="h-4 w-4"
                                                />
                                            </button>
                                        </template>

                                        <template #content>
                                            <!-- Start consultation -->
                                            <button
                                                v-if="
                                                    isDoctor &&
                                                    [
                                                        'scheduled',
                                                        'confirmed',
                                                    ].includes(
                                                        appointment.status,
                                                    )
                                                "
                                                @click="
                                                    startConsultation(
                                                        appointment.id,
                                                    )
                                                "
                                                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                title="Start Consultation"
                                            >
                                                <ClipboardDocumentListIcon
                                                    class="mr-3 h-5 w-5 text-gray-400"
                                                />
                                                Start Consultation
                                            </button>
                                            <!-- Add/View Notes -->
                                            <button
                                                @click="
                                                    openNoteModal(appointment)
                                                "
                                                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                <DocumentTextIcon
                                                    class="mr-3 h-5 w-5 text-gray-400"
                                                />
                                                Add/View Notes
                                            </button>

                                            <!-- Mark Complete -->
                                            <button
                                                @click="
                                                    updateAppointmentStatus(
                                                        appointment.id,
                                                        'completed',
                                                    )
                                                "
                                                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                <CheckCircleIcon
                                                    class="mr-3 h-5 w-5 text-gray-400"
                                                />
                                                Mark Complete
                                            </button>

                                            <!-- Cancel -->
                                            <button
                                                @click="
                                                    updateAppointmentStatus(
                                                        appointment.id,
                                                        'cancelled',
                                                    )
                                                "
                                                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                <XCircleIcon
                                                    class="mr-3 h-5 w-5 text-gray-400"
                                                />
                                                Cancel
                                            </button>

                                            <div
                                                class="border-t border-gray-100"
                                            ></div>

                                            <!-- Delete -->
                                            <button
                                                @click="
                                                    handleDelete(appointment)
                                                "
                                                class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                            >
                                                <TrashIcon
                                                    class="mr-3 h-5 w-5 text-red-400"
                                                />
                                                Delete
                                            </button>
                                        </template>
                                    </Dropdown>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="appointments?.links?.length > 3"
                class="px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Page {{ appointments?.current_page }} of
                        {{ appointments?.last_page }}
                    </div>
                    <div class="flex space-x-2">
                        <template
                            v-for="(link, index) in appointments?.links"
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
                            ></a>
                            <span
                                v-else
                                class="px-3 py-1 text-gray-500"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!appointments?.data?.length" class="text-center py-12">
                <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                    <svg
                        class="w-full h-full"
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No appointments found
                </h3>
                <p class="text-gray-600 mb-4">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters"
                            : "No appointments are scheduled yet."
                    }}
                </p>
            </div>
        </div>

        <!-- Appointment Notes Modal -->
        <Modal
            :show="showNoteModal"
            @close="showNoteModal = false"
            max-width="3xl"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ noteTitle }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Appointment:
                            {{ selectedAppointment?.appointment_no }}
                        </p>
                    </div>
                    <button
                        @click="showNoteModal = false"
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

                <!-- Template Suggestions -->
                <div v-if="noteTemplates.length > 0" class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-700">
                            Quick Templates
                        </h4>
                        <button
                            @click="openTemplateManager"
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            View all templates
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="template in noteTemplates.slice(0, 6)"
                            :key="template.id"
                            @click="useTemplate(template)"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100"
                        >
                            {{ template.name }}
                        </button>
                    </div>
                </div>

                <!-- Notes Editor -->
                <div class="space-y-4">
                    <div>
                        <InputLabel for="notes" value="Appointment Notes" />
                        <TextArea
                            id="notes"
                            v-model="appointmentNotes"
                            rows="10"
                            class="mt-1 block w-full"
                            placeholder="Enter your notes here. You can use templates for common note patterns."
                        />
                    </div>

                    <!-- Private Note Toggle -->
                    <div class="flex items-center">
                        <input
                            id="private-note"
                            v-model="isPrivateNote"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label
                            for="private-note"
                            class="ml-2 block text-sm text-gray-900"
                        >
                            Private note (only visible to you)
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200"
                >
                    <SecondaryButton @click="showNoteModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="saveAppointmentNotes"
                        :disabled="processingAction"
                    >
                        <span v-if="processingAction">Saving...</span>
                        <span v-else>Save Notes</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Note Template Modal -->
        <Modal
            :show="showTemplateModal"
            @close="showTemplateModal = false"
            max-width="2xl"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ editTemplateId ? "Edit" : "Create" }} Note
                            Template
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Create reusable templates for common note patterns
                        </p>
                    </div>
                    <button
                        @click="showTemplateModal = false"
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

                <div class="space-y-6">
                    <!-- Template Name -->
                    <div>
                        <InputLabel
                            for="templateName"
                            value="Template Name *"
                        />
                        <TextInput
                            id="templateName"
                            v-model="templateName"
                            type="text"
                            placeholder="e.g., Follow-up Consultation, Routine Check, Emergency Assessment"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <!-- Template Category -->
                    <div>
                        <InputLabel for="templateCategory" value="Category *" />
                        <SelectInput
                            id="templateCategory"
                            v-model="templateCategory"
                            class="mt-1 block w-full"
                        >
                            <option value="">Select a category</option>
                            <option
                                v-for="category in templateCategories"
                                :key="category.value"
                                :value="category.value"
                            >
                                {{ category.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- Template Content -->
                    <div>
                        <InputLabel
                            for="templateContent"
                            value="Template Content *"
                        />
                        <TextArea
                            id="templateContent"
                            v-model="templateContent"
                            rows="12"
                            class="mt-1 block w-full font-mono text-sm"
                            placeholder="Enter your template content here. You can use placeholders like [Patient Name], [Date], [Diagnosis], etc."
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            Tip: Use placeholders like [Patient Name], [Date],
                            [Diagnosis], [Treatment], [Follow-up Date]
                        </p>
                    </div>

                    <!-- Example Templates -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            Example Templates
                        </h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <button
                                @click="
                                    templateContent =
                                        'Patient presented with [symptoms]. Assessment reveals [findings]. Recommended treatment: [treatment]. Follow-up in [timeframe].'
                                "
                                class="text-blue-600 hover:text-blue-800 hover:underline"
                            >
                                Basic Consultation Template
                            </button>
                            <div class="text-xs text-gray-500">
                                Click to load example
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200"
                >
                    <SecondaryButton @click="showTemplateModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        v-if="editTemplateId"
                        @click="deleteNoteTemplate(editTemplateId)"
                        class="mr-auto"
                    >
                        Delete Template
                    </DangerButton>
                    <PrimaryButton
                        @click="saveNoteTemplate"
                        :disabled="processingAction"
                    >
                        <span v-if="processingAction">Saving...</span>
                        <span v-else
                            >{{
                                editTemplateId ? "Update" : "Create"
                            }}
                            Template</span
                        >
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Template Manager Modal -->
        <Modal
            :show="showTemplateManagerModal"
            @close="showTemplateManagerModal = false"
            max-width="4xl"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            My Note Templates
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Manage your reusable note templates
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <PrimaryButton @click="openTemplateModal">
                            <PlusIcon class="w-5 h-5 mr-2" />
                            New Template
                        </PrimaryButton>
                        <button
                            @click="showTemplateManagerModal = false"
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
                </div>

                <!-- Template Categories Filter -->
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="templateCategory = ''"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium',
                                templateCategory === ''
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            ]"
                        >
                            All Templates
                        </button>
                        <button
                            v-for="category in templateCategories"
                            :key="category.value"
                            @click="templateCategory = category.value"
                            :class="[
                                'px-3 py-1.5 rounded-full text-sm font-medium',
                                templateCategory === category.value
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            ]"
                        >
                            {{ category.label }}
                        </button>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div
                    v-if="noteTemplates.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <div
                        v-for="template in noteTemplates.filter(
                            (t) =>
                                !templateCategory ||
                                t.category === templateCategory,
                        )"
                        :key="template.id"
                        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition-all"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    {{ template.name }}
                                </h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1"
                                >
                                    {{
                                        templateCategories.find(
                                            (c) =>
                                                c.value === template.category,
                                        )?.label || template.category
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="editNoteTemplate(template)"
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Edit Template"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="useTemplate(template)"
                                    class="text-green-600 hover:text-green-800"
                                    title="Use Template"
                                >
                                    <DocumentDuplicateIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="deleteNoteTemplate(template.id)"
                                    class="text-red-600 hover:text-red-800"
                                    title="Delete Template"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 line-clamp-3 mb-3">
                            {{ template.content }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Created:
                            {{
                                new Date(
                                    template.created_at,
                                ).toLocaleDateString()
                            }}
                        </div>
                    </div>
                </div>

                <!-- Empty State for Templates -->
                <div v-else class="text-center py-12">
                    <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                        <DocumentTextIcon class="w-full h-full" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No templates created yet
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Create your first note template to save time on common
                        note patterns.
                    </p>
                    <PrimaryButton @click="openTemplateModal">
                        Create Your First Template
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
                    <TrashIcon class="w-6 h-6 text-red-600" />
                </div>
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Delete Appointment
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete appointment
                            <span class="font-semibold">{{
                                selectedAppointment?.appointment_no
                            }}</span
                            >?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone.
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
                        <span v-if="processingAction">Deleting...</span>
                        <span v-else>Delete Appointment</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
