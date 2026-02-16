<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SelectInput from "@/Components/SelectInput.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import Modal from "@/Components/Modal.vue";

import {
    CalendarIcon,
    HeartIcon,
    BeakerIcon,
    DocumentTextIcon,
    DocumentDuplicateIcon,
    UserIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowDownTrayIcon,
    PrinterIcon,
    ChartBarIcon,
    ClipboardDocumentListIcon,
} from "@heroicons/vue/20/solid";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    patient: Object,
    appointments: Array,
    vitals: Array,
    labOrders: Array,
    prescriptions: Array,
    notes: Array,
    statistics: Object,
    vitalsChartData: Object,
    filters: Object,
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });

// State
const activeTab = ref("overview");
const selectedAppointment = ref(null);
const showVitalsModal = ref(false);
const showLabModal = ref(false);
const showPrescriptionModal = ref(false);
const showNoteModal = ref(false);
const dateRangeFilter = ref(props.filters.date_range || "");
const typeFilter = ref(props.filters.type || "");

// Computed
const filteredAppointments = computed(() => {
    let filtered = props.appointments;

    if (dateRangeFilter.value) {
        const [start, end] = dateRangeFilter.value.split(" to ");
        filtered = filtered.filter(
            (a) =>
                new Date(a.scheduled_date) >= new Date(start) &&
                new Date(a.scheduled_date) <= new Date(end),
        );
    }

    return filtered;
});

const chartOptions = computed(() => ({
    chart: {
        type: "line",
        height: 350,
    },
    series: [
        {
            name: "Heart Rate",
            data: props.vitalsChartData.heart_rate,
        },
    ],
    xaxis: {
        categories: props.vitalsChartData.labels,
    },
}));

const filteredVitals = computed(() => {
    if (dateRangeFilter.value) {
        const [start, end] = dateRangeFilter.value.split(" to ");
        return props.vitals.filter(
            (v) =>
                new Date(v.recorded_at) >= new Date(start) &&
                new Date(v.recorded_at) <= new Date(end),
        );
    }
    return props.vitals;
});

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getStatusColor = (status) => {
    const colors = {
        completed: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        cancelled: "bg-red-100 text-red-800",
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        scheduled: "bg-blue-100 text-blue-800",
        confirmed: "bg-green-100 text-green-800",
        "in-progress": "bg-yellow-100 text-yellow-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const isVitalNormal = (value, type) => {
    if (!value) return null;

    const ranges = {
        blood_pressure_systolic: { min: 90, max: 120 },
        blood_pressure_diastolic: { min: 60, max: 80 },
        heart_rate: { min: 60, max: 100 },
        temperature: { min: 36.1, max: 37.2 },
        oxygen_saturation: { min: 95, max: 100 },
    };

    if (!ranges[type]) return null;
    const numValue = parseFloat(value);
    return numValue >= ranges[type].min && numValue <= ranges[type].max;
};

const viewAppointmentDetails = (appointmentId) => {
    router.visit(`/appointments/${appointmentId}`);
};

const viewVitals = (appointment) => {
    selectedAppointment.value = appointment;
    showVitalsModal.value = true;
};

const printRecord = () => {
    window.print();
};

const exportRecord = () => {
    router.get(`/patients/${props.patient.id}/medical-record/export`);
};

const downloadTimeline = () => {
    router.get(
        `/patients/${props.patient.id}/medical-record/timeline/download`,
    );
};
</script>

<template>
    <AppLayout
        :title="`Medical Record - ${patient.first_name} ${patient.last_name}`"
    >
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div
                        class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center"
                    >
                        <UserIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ patient.first_name }} {{ patient.last_name }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            Patient ID: {{ patient.patient_no }} | DOB:
                            {{ formatDate(patient.date_of_birth) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <SecondaryButton @click="printRecord">
                        <PrinterIcon class="w-4 h-4 mr-2" />
                        Print
                    </SecondaryButton>
                    <PrimaryButton @click="exportRecord">
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Appointments</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ statistics.total_appointments }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <CalendarIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ statistics.completed_appointments }} completed
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Vitals Recorded</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ vitals.length }}
                        </p>
                    </div>
                    <div class="bg-red-100 p-2 rounded-lg">
                        <HeartIcon class="w-6 h-6 text-red-600" />
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Last:
                    {{ vitals[0] ? formatDate(vitals[0].recorded_at) : "N/A" }}
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Lab Orders</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ statistics.total_lab_orders }}
                        </p>
                    </div>
                    <div class="bg-green-100 p-2 rounded-lg">
                        <BeakerIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ statistics.pending_lab_orders }} pending
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Prescriptions</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ statistics.total_prescriptions }}
                        </p>
                    </div>
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ statistics.active_prescriptions }} active
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'overview'"
                        :class="[
                            activeTab === 'overview'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Overview
                    </button>
                    <button
                        @click="activeTab = 'appointments'"
                        :class="[
                            activeTab === 'appointments'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Appointments
                    </button>
                    <button
                        @click="activeTab = 'vitals'"
                        :class="[
                            activeTab === 'vitals'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Vital Signs
                    </button>
                    <button
                        @click="activeTab = 'labs'"
                        :class="[
                            activeTab === 'labs'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Lab Orders
                    </button>
                    <button
                        @click="activeTab = 'prescriptions'"
                        :class="[
                            activeTab === 'prescriptions'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Prescriptions
                    </button>
                    <button
                        @click="activeTab = 'notes'"
                        :class="[
                            activeTab === 'notes'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'px-6 py-3 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Clinical Notes
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <!-- Recent Vitals Chart -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Recent Vital Signs Trend
                        </h3>
                        <div class="h-64">
                            <!-- Add your chart component here (Chart.js, ApexCharts, etc.) -->
                            <div class="text-center text-gray-500 py-16">
                                <VueApexCharts
                                    type="line"
                                    height="350"
                                    :options="chartOptions"
                                    :series="chartOptions.series"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Recent Appointments
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="appointment in appointments.slice(0, 5)"
                                :key="appointment.id"
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{
                                            formatDate(
                                                appointment.scheduled_date,
                                            )
                                        }}
                                        at {{ appointment.scheduled_time }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Dr. {{ appointment.doctor?.first_name }}
                                        {{ appointment.doctor?.last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ appointment.reason }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            getStatusColor(appointment.status),
                                        ]"
                                    >
                                        {{ appointment.status }}
                                    </span>
                                    <button
                                        @click="
                                            viewAppointmentDetails(
                                                appointment.id,
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Prescriptions -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Current Prescriptions
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="prescription in prescriptions
                                    .filter((p) => p.status === 'active')
                                    .slice(0, 5)"
                                :key="prescription.id"
                                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ prescription.medication_name }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ prescription.dosage }} -
                                            {{ prescription.frequency }}
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"
                                    >
                                        Active
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-500">
                                    Prescribed by Dr.
                                    {{ prescription.doctor?.first_name }}
                                    {{ prescription.doctor?.last_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Tab -->
                <div v-if="activeTab === 'appointments'" class="space-y-4">
                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel value="Date Range" />
                            <DateRangePicker
                                v-model="dateRangeFilter"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <InputLabel value="Status" />
                            <SelectInput v-model="typeFilter" class="w-full">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="cancelled">Cancelled</option>
                            </SelectInput>
                        </div>
                    </div>

                    <!-- Appointments List -->
                    <div class="space-y-3">
                        <div
                            v-for="appointment in filteredAppointments"
                            :key="appointment.id"
                            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium text-gray-900">
                                            {{
                                                formatDate(
                                                    appointment.scheduled_date,
                                                )
                                            }}
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            {{ appointment.scheduled_time }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Dr. {{ appointment.doctor?.first_name }}
                                        {{ appointment.doctor?.last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-2">
                                        <p>
                                            <span class="font-medium"
                                                >Reason:</span
                                            >
                                            {{ appointment.reason }}
                                        </p>
                                        <p
                                            v-if="appointment.diagnosis"
                                            class="mt-1"
                                        >
                                            <span class="font-medium"
                                                >Diagnosis:</span
                                            >
                                            {{ appointment.diagnosis }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            getStatusColor(appointment.status),
                                        ]"
                                    >
                                        {{ appointment.status }}
                                    </span>
                                    <div class="mt-2">
                                        <button
                                            @click="
                                                viewAppointmentDetails(
                                                    appointment.id,
                                                )
                                            "
                                            class="text-sm text-blue-600 hover:text-blue-800"
                                        >
                                            View Details →
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vitals Tab -->
                <div v-if="activeTab === 'vitals'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <DateRangePicker
                            v-model="dateRangeFilter"
                            placeholder="Filter by date"
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        BP
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        HR
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Temp
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        SpO₂
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        RR
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Weight
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        BMI
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Recorded By
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="vital in filteredVitals"
                                    :key="vital.id"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ formatDateTime(vital.recorded_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="{
                                                'text-green-600':
                                                    isVitalNormal(
                                                        vital.blood_pressure_systolic,
                                                        'blood_pressure_systolic',
                                                    ) &&
                                                    isVitalNormal(
                                                        vital.blood_pressure_diastolic,
                                                        'blood_pressure_diastolic',
                                                    ),
                                                'text-red-600':
                                                    !isVitalNormal(
                                                        vital.blood_pressure_systolic,
                                                        'blood_pressure_systolic',
                                                    ) ||
                                                    !isVitalNormal(
                                                        vital.blood_pressure_diastolic,
                                                        'blood_pressure_diastolic',
                                                    ),
                                            }"
                                        >
                                            {{
                                                vital.blood_pressure_systolic
                                            }}/{{
                                                vital.blood_pressure_diastolic
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="{
                                                'text-green-600': isVitalNormal(
                                                    vital.heart_rate,
                                                    'heart_rate',
                                                ),
                                                'text-red-600': !isVitalNormal(
                                                    vital.heart_rate,
                                                    'heart_rate',
                                                ),
                                            }"
                                        >
                                            {{ vital.heart_rate }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="{
                                                'text-green-600': isVitalNormal(
                                                    vital.temperature,
                                                    'temperature',
                                                ),
                                                'text-red-600': !isVitalNormal(
                                                    vital.temperature,
                                                    'temperature',
                                                ),
                                            }"
                                        >
                                            {{ vital.temperature }}°C
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="{
                                                'text-green-600': isVitalNormal(
                                                    vital.oxygen_saturation,
                                                    'oxygen_saturation',
                                                ),
                                                'text-red-600': !isVitalNormal(
                                                    vital.oxygen_saturation,
                                                    'oxygen_saturation',
                                                ),
                                            }"
                                        >
                                            {{ vital.oxygen_saturation }}%
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ vital.respiratory_rate || "-" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ vital.weight || "-" }} kg
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ vital.bmi || "-" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ vital.recorded_by?.first_name }}
                                        {{ vital.recorded_by?.last_name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lab Orders Tab -->
                <div v-if="activeTab === 'labs'" class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Ordered Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Test Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Doctor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Priority
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Results
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Resulted Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="lab in labOrders" :key="lab.id">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ formatDateTime(lab.ordered_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ lab.test_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        Dr. {{ lab.doctor?.first_name }}
                                        {{ lab.doctor?.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                lab.priority === 'urgent'
                                                    ? 'bg-red-100 text-red-800'
                                                    : lab.priority === 'stat'
                                                      ? 'bg-orange-100 text-orange-800'
                                                      : 'bg-green-100 text-green-800',
                                            ]"
                                        >
                                            {{ lab.priority }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                getStatusColor(lab.status),
                                            ]"
                                        >
                                            {{ lab.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ lab.results || "-" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{
                                            lab.resulted_at
                                                ? formatDate(lab.resulted_at)
                                                : "-"
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Prescriptions Tab -->
                <div v-if="activeTab === 'prescriptions'" class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Medication
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Dosage
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Frequency
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Duration
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Doctor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                    >
                                        Refills
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="prescription in prescriptions"
                                    :key="prescription.id"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{
                                            formatDate(prescription.start_date)
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ prescription.medication_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ prescription.dosage }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ prescription.frequency }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ prescription.duration }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        Dr.
                                        {{ prescription.doctor?.first_name }}
                                        {{ prescription.doctor?.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                getStatusColor(
                                                    prescription.status,
                                                ),
                                            ]"
                                        >
                                            {{ prescription.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                    >
                                        {{ prescription.refills || 0 }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Clinical Notes Tab -->
                <div v-if="activeTab === 'notes'" class="space-y-4">
                    <div class="space-y-4">
                        <div
                            v-for="note in notes"
                            :key="note.id"
                            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <DocumentDuplicateIcon
                                        class="w-5 h-5 text-gray-400"
                                    />
                                    <span class="font-medium text-gray-900">
                                        {{ formatDateTime(note.created_at) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-600">
                                    Dr. {{ note.doctor?.first_name }}
                                    {{ note.doctor?.last_name }}
                                </span>
                            </div>
                            <div
                                class="text-sm text-gray-700 whitespace-pre-line"
                            >
                                {{ note.content }}
                            </div>
                            <div v-if="note.is_private" class="mt-2">
                                <span
                                    class="text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded"
                                >
                                    Private Note
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Appointment:
                                {{ note.appointment?.appointment_no }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
    .print-only {
        display: block !important;
    }
}
</style>
