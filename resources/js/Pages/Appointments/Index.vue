<script setup>
import { router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, watch, onMounted, onBeforeUnmount } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import DangerButton from "@/Components/DangerButton.vue";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import { ChevronDownIcon } from "@heroicons/vue/20/solid";
import ArchiveIcon from "@heroicons/vue/24/outline/ArchiveBoxIcon";
import DuplicateIcon from "@heroicons/vue/24/outline/Square2StackIcon";
import MoveIcon from "@heroicons/vue/24/outline/ArrowRightCircleIcon";
import EditIcon from "@heroicons/vue/24/outline/PencilIcon";
import DeleteIcon from "@heroicons/vue/24/outline/TrashIcon";
import VitalSignIcon from "@heroicons/vue/24/outline/HeartIcon";
import ChartBarIcon from "@heroicons/vue/24/outline/ChartBarIcon";

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
        }),
    },
});

const page = usePage();

const flash = page.props.flash || {
    success: null,
    error: null,
    warning: null,
    info: null,
};

// Watch for flash messages
watch(
    () => page.props.flash,
    (newFlash) => {
        flash.value = newFlash;
    },
    { deep: true },
);

const auth = computed(() => page.props.auth || { user: null });

const welcomeMessage = computed(() => {
    return "Manage appointments";
});

//auto refreshing appointments from redis
const lastVersion = ref(null);
let timer = null;
let interval = 4000;

const pollVersion = async () => {
    try {
        const res = await fetch("/appointments/version");

        if (!res.ok) {
            console.error("Version endpoint failed", res.status);
            return;
        }

        const data = await res.json();

        if (lastVersion.value === null) {
            lastVersion.value = data.version;
            return;
        }

        if (data.version > lastVersion.value) {
            lastVersion.value = data.version;
            interval = 4000; // fast refresh
            router.reload({ only: ["appointments"], preserveScroll: true });
        } else {
            interval = Math.min(interval + 2000, 15000); // slow down
        }

        if (timer) clearInterval(timer);
        timer = window.setInterval(pollVersion, interval);
    } catch (e) {
        console.error("Polling failed", e);
    }
};

onMounted(() => {
    timer = window.setInterval(pollVersion, 4000);
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

// auto refresh ends here

const createAppointment = () => {
    router.visit("/appointments/create");
};

// search and filters
const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const typeFilter = ref(props.filters.appointment_type || "");
const priorityFilter = ref(props.filters.priority || "");
const dateRangeFilter = ref(props.filters.date_range || "");

// modal state
const showDeleteModal = ref(false);
const showVitalsModal = ref(false);
const showRetakeVitalsModal = ref(false);
const selectedAppointment = ref(null);
const selectedPatientVitals = ref([]);
const loadingVitals = ref(false);

// Processing state
const processingAction = ref(false);

// Add computed property for active filters
const hasActiveFilters = computed(() => {
    return (
        search.value ||
        statusFilter.value ||
        typeFilter.value ||
        priorityFilter.value ||
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
            "/appointments",
            {
                search: search.value,
                status: statusFilter.value,
                appointment_type: typeFilter.value,
                priority: priorityFilter.value,
                date_range: dateRangeFilter.value,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ["appointments", "filters"],
            },
        );
    }, 300); // 300ms debounce
};

// Filter options
const statusOptions = [
    { value: "", label: "All Status" },
    { value: "scheduled", label: "scheduled" },
    { value: "confirmed", label: "confirmed" },
    { value: "in-progress", label: "in progress" },
    { value: "completed", label: "completed" },
    { value: "no show", label: "no show" },
    { value: "rescheduled", label: "rescheduled" },
];

const typeOptions = [
    { value: "", label: "All Types" },
    { value: "consultation", label: "consultation" },
    { value: "follow up", label: "follow up" },
    { value: "emergency", label: "emergency" },
    { value: "routine check", label: "routine check" },
    { value: "lab test", label: "lab test" },
    { value: "vacination", label: "vacination" },
    { value: "therapy", label: "therapy" },
    { value: "procedure", label: "procedure" },
    { value: "other", label: "other" },
];

const priorityOptions = [
    { value: "", label: "All Priorities" },
    { value: "low", label: "low" },
    { value: "medium", label: "medium" },
    { value: "high", label: "high" },
    { value: "emergency", label: "emergency" },
];

watch(
    [search, statusFilter, typeFilter, priorityFilter, dateRangeFilter],
    () => {
        applyFilters();
    },
    { deep: true },
);

const clearFilters = () => {
    search.value = "";
    statusFilter.value = "";
    typeFilter.value = "";
    priorityFilter.value = "";
    dateRangeFilter.value = "";
};

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

// View vitals modal
const viewVitals = (appointment) => {
    selectedAppointment.value = appointment;
    selectedPatientVitals.value = appointment.vital_signs || [];
    showVitalsModal.value = true;
};

// Navigate to take vitals page
const takeVitals = (appointmentId) => {
    router.visit(`/vitals/create/${appointmentId}`);
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Format time for display
const formatTime = (time) => {
    if (!time) return "";
    return new Date(`2000-01-01T${time}`).toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

const editAppointment = (appointmentId) => {
    router.visit(`/appointments/edit/${appointmentId}`);
};

// View appointment details
const viewAppointmentDetails = (appointmentId) => {
    router.visit(`/appointments/${appointmentId}`);
};

// // Archive appointment
// const archiveAppointment = (appointmentId) => {
//     // Implement archive functionality
//     console.log("Archive appointment:", appointmentId);
// };

// // Duplicate appointment
// const duplicateAppointment = (appointmentId) => {
//     // Implement duplicate functionality
//     console.log("Duplicate appointment:", appointmentId);
// };

// Move appointment
// const moveAppointment = (appointmentId) => {
//     // Implement move functionality
//     console.log("Move appointment:", appointmentId);
// };

// Check if vital is in normal range (for display)
const isVitalNormal = (value, type) => {
    if (!value) return null;

    const vitalRanges = {
        blood_pressure_systolic: { normal: { min: 90, max: 120 } },
        blood_pressure_diastolic: { normal: { min: 60, max: 80 } },
        heart_rate: { normal: { min: 60, max: 100 } },
        temperature: { normal: { min: 36.1, max: 37.2 } },
        oxygen_saturation: { normal: { min: 95, max: 100 } },
    };

    if (!vitalRanges[type] || !vitalRanges[type].normal) return null;

    const numValue = parseFloat(value);
    const range = vitalRanges[type].normal;
    return numValue >= range.min && numValue <= range.max;
};

// Get BMI category (for display)
const getBMICategory = (bmi) => {
    if (!bmi) return null;
    const bmiValue = parseFloat(bmi);

    if (bmiValue < 18.5)
        return {
            category: "Underweight",
            color: "text-yellow-600",
            bgColor: "bg-yellow-100",
        };
    if (bmiValue < 25)
        return {
            category: "Normal",
            color: "text-green-600",
            bgColor: "bg-green-100",
        };
    if (bmiValue < 30)
        return {
            category: "Overweight",
            color: "text-orange-600",
            bgColor: "bg-orange-100",
        };
    return { category: "Obese", color: "text-red-600", bgColor: "bg-red-100" };
};
</script>

<template>
    <AppLayout title="Appointments Management" :subtitle="welcomeMessage">
        <template #headerActions>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <PrimaryButton
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor' ||
                        auth.user?.user_type === 'nurse'
                    "
                    @click="createAppointment"
                    class="w-full sm:w-auto text-center"
                >
                    Add New Appointment
                </PrimaryButton>
            </div>
        </template>

        <!-- Filters section -->
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
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- search -->
                    <div>
                        <InputLabel for="search" value="Search" />
                        <TextInput
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Patient name, appointment no..."
                            class="block w-full"
                        />
                    </div>
                    <!-- priority filter -->
                    <div>
                        <InputLabel for="priority" value="Priority" />
                        <SelectInput
                            id="priority"
                            v-model="priorityFilter"
                            class="w-full"
                        >
                            <option
                                v-for="option in priorityOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </SelectInput>
                    </div>

                    <!-- status filter -->
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
                    <!-- appointment type -->
                    <div>
                        <InputLabel
                            for="appointment_type"
                            value="Appointment Type"
                        />
                        <SelectInput
                            id="appointment_type"
                            v-model="typeFilter"
                            class="w-full"
                        >
                            <option
                                v-for="appointment in typeOptions"
                                :key="appointment.value"
                                :value="appointment.value"
                            >
                                {{ appointment.label }}
                            </option>
                        </SelectInput>
                    </div>
                    <!-- Date Range Filter -->
                    <div>
                        <InputLabel for="date_range" value="Appointment Date" />
                        <DateRangePicker
                            id="date_range"
                            v-model="dateRangeFilter"
                            placeholder="Select date range"
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
                            <!-- Priority Filter Badge -->
                            <div
                                v-if="priorityFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800"
                            >
                                {{
                                    priorityOptions.find(
                                        (o) => o.value === priorityFilter,
                                    )?.label
                                }}
                                <button
                                    @click="priorityFilter = ''"
                                    class="ml-1 text-green-600 hover:text-green-800"
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
                            <!-- Type Filter Badge -->
                            <div
                                v-if="typeFilter"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800"
                            >
                                {{
                                    typeOptions.find(
                                        (o) => o.value === typeFilter,
                                    )?.label
                                }}
                                <button
                                    @click="typeFilter = ''"
                                    class="ml-1 text-green-600 hover:text-green-800"
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

        <!-- Appointments table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- Table header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-500">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white">
                        Appointments: ({{ appointments?.total || 0 }})
                    </h3>
                    <div class="text-sm text-white">
                        <template v-if="appointments?.data?.length">
                            Showing {{ appointments.from }} to
                            {{ appointments.to }} of
                            {{ appointments.total }} results
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
                                Appointment No.
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Patient Info
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Doctor Info
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Time
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
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
                            <!-- Appointment Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ appointment.appointment_no }}
                                </div>
                            </td>

                            <!-- Patient info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ appointment.patient?.first_name }}
                                    {{ appointment.patient?.last_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    ID: {{ appointment.patient_no }}
                                </div>
                            </td>

                            <!-- Doctor info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ appointment.doctor?.first_name }}
                                    {{ appointment.doctor?.last_name }}
                                </div>
                            </td>

                            <!-- Appointment type -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ appointment.appointment_type }}
                                </div>
                            </td>

                            <!-- Appointment date -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ formatDate(appointment.scheduled_date) }}
                                </div>
                            </td>

                            <!-- Appointment time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ formatTime(appointment.scheduled_time) }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800':
                                            appointment.status === 'scheduled',
                                        'bg-blue-100 text-blue-800':
                                            appointment.status === 'confirmed',
                                        'bg-green-100 text-green-800':
                                            appointment.status === 'completed',
                                        'bg-red-100 text-red-800':
                                            appointment.status === 'cancelled',
                                        'bg-gray-100 text-gray-800':
                                            appointment.status === 'no show',
                                        'bg-purple-100 text-purple-800':
                                            appointment.status ===
                                            'rescheduled',
                                    }"
                                >
                                    {{ appointment.status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                            >
                                <div class="relative">
                                    <Menu
                                        as="div"
                                        class="relative inline-block text-left"
                                    >
                                        <div>
                                            <MenuButton
                                                class="inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            >
                                                Options
                                                <ChevronDownIcon
                                                    class="-mr-1 ml-2 h-4 w-4 text-gray-500"
                                                    aria-hidden="true"
                                                />
                                            </MenuButton>
                                        </div>

                                        <transition
                                            enter-active-class="transition duration-100 ease-out"
                                            enter-from-class="transform scale-95 opacity-0"
                                            enter-to-class="transform scale-100 opacity-100"
                                            leave-active-class="transition duration-75 ease-in"
                                            leave-from-class="transform scale-100 opacity-100"
                                            leave-to-class="transform scale-95 opacity-0"
                                        >
                                            <MenuItems
                                                class="absolute right-0 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                            >
                                                <div class="py-1">
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                editAppointment(
                                                                    appointment.id,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-gray-100 text-gray-900'
                                                                    : 'text-gray-700',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <EditIcon
                                                                class="mr-3 h-5 w-5 text-gray-400"
                                                                aria-hidden="true"
                                                            />
                                                            Edit/View
                                                            Appointment
                                                        </button>
                                                    </MenuItem>
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                viewVitals(
                                                                    appointment,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-gray-100 text-gray-900'
                                                                    : 'text-gray-700',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <VitalSignIcon
                                                                class="mr-3 h-5 w-5 text-gray-400"
                                                                aria-hidden="true"
                                                            />
                                                            View Vitals
                                                        </button>
                                                    </MenuItem>
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                takeVitals(
                                                                    appointment.id,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-gray-100 text-gray-900'
                                                                    : 'text-gray-700',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <ChartBarIcon
                                                                class="mr-3 h-5 w-5 text-gray-400"
                                                                aria-hidden="true"
                                                            />
                                                            Take Vitals
                                                        </button>
                                                    </MenuItem>
                                                </div>
                                                <!-- <div class="py-1">
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                archiveAppointment(
                                                                    appointment.id,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-gray-100 text-gray-900'
                                                                    : 'text-gray-700',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <ArchiveIcon
                                                                class="mr-3 h-5 w-5 text-gray-400"
                                                                aria-hidden="true"
                                                            />
                                                            Archive
                                                        </button>
                                                    </MenuItem>
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                moveAppointment(
                                                                    appointment.id,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-gray-100 text-gray-900'
                                                                    : 'text-gray-700',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <MoveIcon
                                                                class="mr-3 h-5 w-5 text-gray-400"
                                                                aria-hidden="true"
                                                            />
                                                            Move
                                                        </button>
                                                    </MenuItem>
                                                </div> -->
                                                <div class="py-1">
                                                    <MenuItem
                                                        v-slot="{ active }"
                                                    >
                                                        <button
                                                            @click="
                                                                handleDelete(
                                                                    appointment,
                                                                )
                                                            "
                                                            :class="[
                                                                active
                                                                    ? 'bg-red-50 text-red-700'
                                                                    : 'text-red-600',
                                                                'flex w-full items-center px-4 py-2 text-sm',
                                                            ]"
                                                        >
                                                            <DeleteIcon
                                                                class="mr-3 h-5 w-5 text-red-400"
                                                                aria-hidden="true"
                                                            />
                                                            Delete
                                                        </button>
                                                    </MenuItem>
                                                </div>
                                            </MenuItems>
                                        </transition>
                                    </Menu>
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
                    No appointments found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters"
                            : "Get started by adding a new appointment."
                    }}
                </p>
                <div
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor' ||
                        auth.user?.user_type === 'nurse'
                    "
                    class="mt-6"
                >
                    <PrimaryButton @click="createAppointment">
                        Add New Appointment
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- View Vitals Modal -->
        <Modal
            :show="showVitalsModal"
            @close="showVitalsModal = false"
            max-width="4xl"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Vital Signs History
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Patient:
                            {{ selectedAppointment?.patient?.first_name }}
                            {{ selectedAppointment?.patient?.last_name }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Appointment:
                            {{ selectedAppointment?.appointment_no }}
                        </p>
                    </div>
                    <button
                        @click="showVitalsModal = false"
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

                <!-- Loading State -->
                <div v-if="loadingVitals" class="text-center py-12">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"
                    ></div>
                    <p class="mt-4 text-gray-600">Loading vital signs...</p>
                </div>

                <!-- Vital Signs Content -->
                <div v-else>
                    <!-- No Vitals State -->
                    <div
                        v-if="!selectedPatientVitals.length"
                        class="text-center py-12"
                    >
                        <div class="mb-4">
                            <svg
                                class="mx-auto h-16 w-16 text-gray-400"
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
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            No Vital Signs Recorded
                        </h3>
                        <p class="text-gray-600 mb-6">
                            No vital signs have been recorded for this patient
                            yet.
                        </p>
                        <div class="flex justify-center space-x-4">
                            <SecondaryButton @click="showVitalsModal = false">
                                Close
                            </SecondaryButton>
                            <PrimaryButton
                                @click="takeVitals(selectedAppointment.id)"
                            >
                                Record Vitals
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Has Vitals -->
                    <div v-else>
                        <!-- Latest Vital Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div
                                class="bg-blue-50 p-4 rounded-lg border border-blue-200"
                            >
                                <h4
                                    class="text-sm font-medium text-blue-900 mb-2"
                                >
                                    Latest Reading
                                </h4>
                                <div class="space-y-2">
                                    <div
                                        v-if="
                                            selectedPatientVitals[0]
                                                .blood_pressure_systolic
                                        "
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-blue-700"
                                            >Blood Pressure:</span
                                        >
                                        <span class="text-sm font-medium">
                                            {{
                                                selectedPatientVitals[0]
                                                    .blood_pressure_systolic
                                            }}/{{
                                                selectedPatientVitals[0]
                                                    .blood_pressure_diastolic
                                            }}
                                            mmHg
                                        </span>
                                    </div>
                                    <div
                                        v-if="
                                            selectedPatientVitals[0].heart_rate
                                        "
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-blue-700"
                                            >Heart Rate:</span
                                        >
                                        <span class="text-sm font-medium">
                                            {{
                                                selectedPatientVitals[0]
                                                    .heart_rate
                                            }}
                                            bpm
                                        </span>
                                    </div>
                                    <div
                                        v-if="
                                            selectedPatientVitals[0].temperature
                                        "
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-blue-700"
                                            >Temperature:</span
                                        >
                                        <span class="text-sm font-medium">
                                            {{
                                                selectedPatientVitals[0]
                                                    .temperature
                                            }}°C
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-blue-700"
                                            >Recorded at:</span
                                        >
                                        <span class="font-sm font-medium">{{
                                            formatDate(
                                                selectedPatientVitals[0]
                                                    .recorded_at,
                                            )
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- BMI Card -->
                            <div
                                v-if="selectedPatientVitals[0].bmi"
                                class="bg-green-50 p-4 rounded-lg border border-green-200"
                            >
                                <h4
                                    class="text-sm font-medium text-green-900 mb-2"
                                >
                                    BMI Status
                                </h4>
                                <div class="text-center">
                                    <div class="text-2xl font-bold mb-1">
                                        {{ selectedPatientVitals[0].bmi }}
                                    </div>
                                    <div
                                        :class="
                                            getBMICategory(
                                                selectedPatientVitals[0].bmi,
                                            )?.color + ' text-sm font-medium'
                                        "
                                    >
                                        {{
                                            getBMICategory(
                                                selectedPatientVitals[0].bmi,
                                            )?.category
                                        }}
                                    </div>
                                </div>
                            </div>

                            <!-- Normal Ranges -->
                            <div
                                class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                            >
                                <h4
                                    class="text-sm font-medium text-gray-900 mb-2"
                                >
                                    Normal Ranges
                                </h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">BP:</span>
                                        <span class="font-medium"
                                            >90-120/60-80 mmHg</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">HR:</span>
                                        <span class="font-medium"
                                            >60-100 bpm</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Temp:</span>
                                        <span class="font-medium"
                                            >36.1-37.2°C</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">SpO₂:</span>
                                        <span class="font-medium">95-100%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vital Signs History Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Date/Time
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            BP
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            HR
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Temp
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            SpO₂
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            RR
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Recorded By
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="record in selectedPatientVitals"
                                        :key="record.id"
                                    >
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            {{ formatDate(record.recorded_at) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm"
                                        >
                                            <span
                                                :class="{
                                                    'text-green-600':
                                                        isVitalNormal(
                                                            record.blood_pressure_systolic,
                                                            'blood_pressure_systolic',
                                                        ) &&
                                                        isVitalNormal(
                                                            record.blood_pressure_diastolic,
                                                            'blood_pressure_diastolic',
                                                        ),
                                                    'text-red-600':
                                                        !isVitalNormal(
                                                            record.blood_pressure_systolic,
                                                            'blood_pressure_systolic',
                                                        ) ||
                                                        !isVitalNormal(
                                                            record.blood_pressure_diastolic,
                                                            'blood_pressure_diastolic',
                                                        ),
                                                }"
                                            >
                                                {{
                                                    record.blood_pressure_systolic
                                                }}/{{
                                                    record.blood_pressure_diastolic
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm"
                                        >
                                            <span
                                                :class="{
                                                    'text-green-600':
                                                        isVitalNormal(
                                                            record.heart_rate,
                                                            'heart_rate',
                                                        ),
                                                    'text-red-600':
                                                        !isVitalNormal(
                                                            record.heart_rate,
                                                            'heart_rate',
                                                        ),
                                                }"
                                            >
                                                {{ record.heart_rate }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm"
                                        >
                                            <span
                                                :class="{
                                                    'text-green-600':
                                                        isVitalNormal(
                                                            record.temperature,
                                                            'temperature',
                                                        ),
                                                    'text-red-600':
                                                        !isVitalNormal(
                                                            record.temperature,
                                                            'temperature',
                                                        ),
                                                }"
                                            >
                                                {{ record.temperature }}°C
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm"
                                        >
                                            <span
                                                :class="{
                                                    'text-green-600':
                                                        isVitalNormal(
                                                            record.oxygen_saturation,
                                                            'oxygen_saturation',
                                                        ),
                                                    'text-red-600':
                                                        !isVitalNormal(
                                                            record.oxygen_saturation,
                                                            'oxygen_saturation',
                                                        ),
                                                }"
                                            >
                                                {{ record.oxygen_saturation }}%
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm"
                                        >
                                            {{ record.respiratory_rate || "-" }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"
                                        >
                                            {{
                                                record.recorded_by
                                                    ? `${record.recorded_by.first_name} ${record.recorded_by.last_name}`
                                                    : "System"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200"
                        >
                            <SecondaryButton @click="showVitalsModal = false">
                                Close
                            </SecondaryButton>
                            <PrimaryButton
                                @click="takeVitals(selectedAppointment.id)"
                            >
                                Record New Vitals
                            </PrimaryButton>
                        </div>
                    </div>
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
                        <span v-if="processingAction">Processing...</span>
                        <span v-else>Delete Appointment</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
