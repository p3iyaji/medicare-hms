<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import AppLogoIcon from "@/components/AppLogoIcon.vue";

const props = defineProps({
    appointmentId: {
        type: String,
        required: true,
    },
    patient: {
        type: Object,
        required: true,
    },
    vitalHistory: {
        type: Array,
        default: () => [],
    },
    devices: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });
const vitalHistory = props.vitalHistory || [];

// Form state
const form = useForm({
    appointment_id: props.appointmentId || null,
    patient_id: props.patient.id,
    recorded_by: auth.value.user?.id || null,
    blood_pressure_systolic: "",
    blood_pressure_diastolic: "",
    heart_rate: "",
    temperature: "",
    respiratory_rate: "",
    oxygen_saturation: "",
    weight: "",
    height: "",
    bmi: "",
    notes: "",
    recorded_at: new Date().toISOString().slice(0, 16),
    data_source: "manual",
    pain_scale: "",
    device_id: null,
});

// UI State
const showDeviceModal = ref(false);
const isRecordingAutomatically = ref(false);
const selectedDevice = ref(null);
const recordingInterval = ref(null);
const lastRecordedVitals = ref(null);
const showHistoryModal = ref(false);
const connectionStatus = ref("disconnected");
const errors = ref({});

const pageTitle = computed(
    () =>
        "Record Vital Signs - " +
        props.patient.first_name +
        " " +
        props.patient.last_name,
);

// Automatic recording devices
const availableDevices = ref([
    {
        id: 1,
        name: "Omron Blood Pressure Monitor",
        type: "blood_pressure",
        status: "available",
    },
    {
        id: 2,
        name: "Withings Smart Scale",
        type: "weight_height",
        status: "available",
    },
    {
        id: 3,
        name: "Fitbit Charge 5",
        type: "heart_rate_oxygen",
        status: "available",
    },
    {
        id: 4,
        name: "ThermoScan 7",
        type: "temperature",
        status: "available",
    },
    {
        id: 5,
        name: "Masimo Pulse Oximeter",
        type: "oxygen",
        status: "available",
    },
]);

// Vital sign ranges for validation
const vitalRanges = {
    blood_pressure_systolic: {
        min: 50,
        max: 250,
        normal: { min: 90, max: 120 },
    },
    blood_pressure_diastolic: {
        min: 30,
        max: 150,
        normal: { min: 60, max: 80 },
    },
    heart_rate: {
        min: 30,
        max: 200,
        normal: { min: 60, max: 100 },
    },
    temperature: {
        min: 32,
        max: 43,
        normal: { min: 36.1, max: 37.2 },
    },
    respiratory_rate: {
        min: 8,
        max: 40,
        normal: { min: 12, max: 20 },
    },
    oxygen_saturation: {
        min: 70,
        max: 100,
        normal: { min: 95, max: 100 },
    },
    weight: {
        min: 1,
        max: 300,
        normal: null,
    },
    height: {
        min: 30,
        max: 250,
        normal: null,
    },
};

// Computed properties
const bmiComputed = computed(() => {
    if (form.weight && form.height) {
        const heightInMeters = form.height / 100;
        const bmiValue = (
            form.weight /
            (heightInMeters * heightInMeters)
        ).toFixed(1);
        form.bmi = bmiValue;
        return bmiValue;
    }
    return null;
});

const bmiCategory = computed(() => {
    const bmi = parseFloat(bmiComputed.value);
    if (!bmi) return null;

    if (bmi < 18.5)
        return {
            category: "Underweight",
            color: "text-yellow-600",
            bgColor: "bg-yellow-100",
        };
    if (bmi < 25)
        return {
            category: "Normal",
            color: "text-green-600",
            bgColor: "bg-green-100",
        };
    if (bmi < 30)
        return {
            category: "Overweight",
            color: "text-orange-600",
            bgColor: "bg-orange-100",
        };
    return {
        category: "Obese",
        color: "text-red-600",
        bgColor: "bg-red-100",
    };
});

// Check if vital is in normal range
const isVitalNormal = (value, type) => {
    if (!value || !vitalRanges[type] || !vitalRanges[type].normal) return null;

    const numValue = parseFloat(value);
    const range = vitalRanges[type].normal;
    return numValue >= range.min && numValue <= range.max;
};

// Validate vital value
const validateVital = (field, value) => {
    if (!value || value === "") {
        delete errors.value[field];
        return true;
    }

    const numValue = parseFloat(value);
    const range = vitalRanges[field];

    if (!range) return true;

    if (numValue < range.min || numValue > range.max) {
        errors.value[field] =
            `Value must be between ${range.min} and ${range.max}`;
        return false;
    }

    delete errors.value[field];
    return true;
};

// Calculate BMI automatically when weight or height changes
watch(
    () => [form.weight, form.height],
    () => {
        if (form.weight && form.height) {
            const heightInMeters = form.height / 100;
            form.bmi = (
                form.weight /
                (heightInMeters * heightInMeters)
            ).toFixed(1);
        }
    },
);

// Form submission
const submitForm = () => {
    // Validate all fields
    let isValid = true;
    Object.keys(vitalRanges).forEach((field) => {
        if (form[field]) {
            isValid = validateVital(field, form[field]) && isValid;
        }
    });

    if (!isValid) {
        alert("Please correct the errors before submitting.");
        return;
    }

    // Add device type if recording automatically
    if (isRecordingAutomatically.value && selectedDevice.value) {
        form.device_type = selectedDevice.value.type;
    }

    form.post("/vitals/store", {
        preserveScroll: true,
        onSuccess: () => {
            const recordedData = { ...form.data() };
            form.reset();
            lastRecordedVitals.value = recordedData;
            alert("Vital signs recorded successfully!");
        },
        onError: (errors) => {
            console.error("Error recording vitals:", errors);
            // Show specific error messages
            if (errors.data_source) {
                alert("Data source error: " + errors.data_source);
            }
            if (errors.device_type) {
                alert("Device type error: " + errors.device_type);
            }
        },
    });
};

// Fixed device connection functions
const connectToDevice = (device) => {
    console.log("Connecting to device:", device); // Debug log

    selectedDevice.value = device;
    connectionStatus.value = "connecting";

    // Simulate device connection
    setTimeout(() => {
        connectionStatus.value = "connected";
        showDeviceModal.value = false;

        // Start automatic recording based on device type
        startAutomaticRecording(device.type);

        // Show success notification
        alert(`Successfully connected to ${device.name}`);
    }, 1500);
};

const startAutomaticRecording = (deviceType) => {
    console.log("Starting automatic recording for device type:", deviceType);

    isRecordingAutomatically.value = true;
    form.data_source = "device";
    form.device_id = selectedDevice.value?.id || null;
    form.device_type = deviceType; // ADD THIS LINE

    // Clear any existing interval
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
    }

    // Simulate automatic data collection
    recordingInterval.value = setInterval(() => {
        simulateDeviceReading(deviceType);
    }, 3000);

    // Show initial reading immediately
    setTimeout(() => {
        simulateDeviceReading(deviceType);
    }, 500);
};

const stopAutomaticRecording = () => {
    console.log("Stopping automatic recording"); // Debug log

    isRecordingAutomatically.value = false;
    connectionStatus.value = "disconnected";
    form.data_source = "manual";
    form.device_id = null;

    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
        recordingInterval.value = null;
    }

    alert("Device disconnected. Manual entry mode activated.");
};

const simulateDeviceReading = (deviceType) => {
    // Generate realistic vital signs based on device type
    const simulatedData = {
        blood_pressure: () => ({
            blood_pressure_systolic: Math.floor(Math.random() * 40) + 100,
            blood_pressure_diastolic: Math.floor(Math.random() * 30) + 60,
        }),
        heart_rate_oxygen: () => ({
            heart_rate: Math.floor(Math.random() * 40) + 60,
            oxygen_saturation: Math.floor(Math.random() * 5) + 95,
        }),
        weight_height: () => ({
            weight: (Math.random() * 20 + 60).toFixed(1),
            height: props.patient.height || 170,
        }),
        temperature: () => ({
            temperature: (Math.random() * 1 + 36.5).toFixed(1),
        }),
        oxygen: () => ({
            oxygen_saturation: Math.floor(Math.random() * 5) + 95,
        }),
    };

    const data = simulatedData[deviceType] ? simulatedData[deviceType]() : {};

    // Update form with simulated data
    Object.keys(data).forEach((key) => {
        form[key] = data[key];
    });

    // Update last recorded vitals
    lastRecordedVitals.value = { ...form.data(), ...data };
};

// Quick entry functions
const quickEntry = (type, value) => {
    form[type] = value;
    validateVital(type, value);
};

// Reset form
const resetForm = () => {
    form.reset();
    errors.value = {};
    if (isRecordingAutomatically.value) {
        stopAutomaticRecording();
    }
};

// Load last recorded vitals
const loadLastVitals = () => {
    if (props.vitalHistory.length > 0) {
        const lastRecord = props.vitalHistory[0];
        Object.keys(lastRecord).forEach((key) => {
            if (key in form) {
                form[key] = lastRecord[key];
            }
        });
    }
};

const handleCancel = () => {
    router.visit(route("appointments.index"));
};

const calculateBMI = (weight, height) => {
    if (!weight || !height) return null;
    const heightInMeters = height / 100;
    return (weight / (heightInMeters * heightInMeters)).toFixed(1);
};

onMounted(() => {
    // Set current time
    form.recorded_at = new Date().toISOString().slice(0, 16);
});
</script>

<template>
    <!-- <AppLayout
        :title="`Vital Signs - ${patient.first_name} ${patient.last_name}`"
    > -->
    <AppLayout :title="pageTitle">
        <template #headerActions>
            <div class="flex flex-wrap gap-2">
                <SecondaryButton @click="handleCancel" class="mr-2">
                    <span class="hidden sm:inline">Cancel</span>
                    <span class="sm:hidden">←</span>
                </SecondaryButton>

                <div class="flex flex-wrap gap-2 md:gap-3">
                    <SecondaryButton @click="showHistoryModal = true">
                        View History
                    </SecondaryButton>

                    <PrimaryButton
                        @click="showDeviceModal = true"
                        v-if="!isRecordingAutomatically"
                    >
                        Connect Device
                    </PrimaryButton>

                    <DangerButton
                        v-else
                        @click="stopAutomaticRecording"
                        class="flex items-center gap-2"
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
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
                            />
                        </svg>
                        Stop Recording
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Connection Status -->
            <div v-if="isRecordingAutomatically" class="mb-6">
                <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div
                            class="w-3 h-3 rounded-full bg-green-500 animate-pulse"
                        ></div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">
                            Connected to {{ selectedDevice?.name }}
                            <span
                                class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded"
                            >
                                Recording automatically
                            </span>
                        </p>
                        <p class="text-sm text-blue-600">
                            Data is being recorded every 3 seconds
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Vital Signs Form -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Data Source -->
                            <div class="border-b border-gray-200 pb-4">
                                <InputLabel value="Data Source" class="mb-2" />
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            v-model="form.data_source"
                                            value="manual"
                                            class="form-radio h-4 w-4 text-blue-600"
                                            :disabled="isRecordingAutomatically"
                                        />
                                        <span class="ml-2">Manual Entry</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input
                                            type="radio"
                                            v-model="form.data_source"
                                            value="device"
                                            class="form-radio h-4 w-4 text-blue-600"
                                            :disabled="
                                                !isRecordingAutomatically
                                            "
                                        />
                                        <span class="ml-2">Device</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Recorded At -->
                            <div>
                                <InputLabel
                                    for="recorded_at"
                                    value="Date & Time"
                                />
                                <TextInput
                                    id="recorded_at"
                                    v-model="form.recorded_at"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-300': errors.recorded_at,
                                    }"
                                />
                                <p
                                    v-if="errors.recorded_at"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.recorded_at }}
                                </p>
                            </div>

                            <!-- Vital Signs Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Blood Pressure - Takes full height -->
                                <div class="space-y-2 md:row-span-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel value="Blood Pressure" />
                                        <span class="text-xs text-gray-500"
                                            >mmHg</span
                                        >
                                    </div>
                                    <div class="rounded-md bg-blue-200 p-2">
                                        <div class="space-y-1">
                                            <div
                                                class="text-xs text-gray-800 mb-1"
                                            >
                                                Systolic
                                            </div>
                                            <TextInput
                                                type="number"
                                                v-model="
                                                    form.blood_pressure_systolic
                                                "
                                                placeholder="e.g., 120"
                                                @blur="
                                                    validateVital(
                                                        'blood_pressure_systolic',
                                                        form.blood_pressure_systolic,
                                                    )
                                                "
                                                :class="{
                                                    'border-red-300':
                                                        errors.blood_pressure_systolic,
                                                }"
                                                class="w-full"
                                            />
                                            <p
                                                v-if="
                                                    errors.blood_pressure_systolic
                                                "
                                                class="mt-1 text-sm text-red-600"
                                            >
                                                {{
                                                    errors.blood_pressure_systolic
                                                }}
                                            </p>
                                        </div>
                                        <div class="space-y-1">
                                            <div
                                                class="text-xs text-gray-800 mb-1"
                                            >
                                                Diastolic
                                            </div>
                                            <TextInput
                                                type="number"
                                                v-model="
                                                    form.blood_pressure_diastolic
                                                "
                                                placeholder="e.g., 80"
                                                @blur="
                                                    validateVital(
                                                        'blood_pressure_diastolic',
                                                        form.blood_pressure_diastolic,
                                                    )
                                                "
                                                :class="{
                                                    'border-red-300':
                                                        errors.blood_pressure_diastolic,
                                                }"
                                                class="w-full"
                                            />
                                            <p
                                                v-if="
                                                    errors.blood_pressure_diastolic
                                                "
                                                class="mt-1 text-sm text-red-600"
                                            >
                                                {{
                                                    errors.blood_pressure_diastolic
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        v-if="
                                            form.blood_pressure_systolic &&
                                            form.blood_pressure_diastolic
                                        "
                                        class="pt-2"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm font-medium"
                                                >Reading:</span
                                            >
                                            <span
                                                :class="{
                                                    'text-green-600 font-semibold':
                                                        isVitalNormal(
                                                            form.blood_pressure_systolic,
                                                            'blood_pressure_systolic',
                                                        ) &&
                                                        isVitalNormal(
                                                            form.blood_pressure_diastolic,
                                                            'blood_pressure_diastolic',
                                                        ),
                                                    'text-yellow-600 font-semibold':
                                                        !isVitalNormal(
                                                            form.blood_pressure_systolic,
                                                            'blood_pressure_systolic',
                                                        ) ||
                                                        !isVitalNormal(
                                                            form.blood_pressure_diastolic,
                                                            'blood_pressure_diastolic',
                                                        ),
                                                }"
                                            >
                                                {{
                                                    form.blood_pressure_systolic
                                                }}/{{
                                                    form.blood_pressure_diastolic
                                                }}
                                                mmHg
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Heart Rate - Aligned to top -->
                                <div class="space-y-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel
                                            for="heart_rate"
                                            value="Heart Rate"
                                        />
                                        <span class="text-xs text-gray-500"
                                            >bpm</span
                                        >
                                    </div>
                                    <TextInput
                                        id="heart_rate"
                                        v-model="form.heart_rate"
                                        type="number"
                                        min="30"
                                        max="200"
                                        class="w-full"
                                        @blur="
                                            validateVital(
                                                'heart_rate',
                                                form.heart_rate,
                                            )
                                        "
                                        :class="{
                                            'border-red-300': errors.heart_rate,
                                        }"
                                    />
                                    <p
                                        v-if="errors.heart_rate"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.heart_rate }}
                                    </p>
                                    <div v-if="form.heart_rate" class="pt-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm font-medium"
                                                >Reading:</span
                                            >
                                            <span
                                                :class="{
                                                    'text-green-600 font-semibold':
                                                        isVitalNormal(
                                                            form.heart_rate,
                                                            'heart_rate',
                                                        ),
                                                    'text-yellow-600 font-semibold':
                                                        !isVitalNormal(
                                                            form.heart_rate,
                                                            'heart_rate',
                                                        ),
                                                }"
                                            >
                                                {{ form.heart_rate }} bpm
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Temperature - Aligned below Heart Rate -->
                                <div class="space-y-2 md:col-start-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel
                                            for="temperature"
                                            value="Temperature"
                                        />
                                        <span class="text-xs text-gray-500"
                                            >°C</span
                                        >
                                    </div>
                                    <TextInput
                                        id="temperature"
                                        v-model="form.temperature"
                                        type="number"
                                        step="0.1"
                                        min="32"
                                        max="43"
                                        class="w-full"
                                        @blur="
                                            validateVital(
                                                'temperature',
                                                form.temperature,
                                            )
                                        "
                                        :class="{
                                            'border-red-300':
                                                errors.temperature,
                                        }"
                                    />
                                    <p
                                        v-if="errors.temperature"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.temperature }}
                                    </p>
                                    <div v-if="form.temperature" class="pt-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm font-medium"
                                                >Reading:</span
                                            >
                                            <span
                                                :class="{
                                                    'text-green-600 font-semibold':
                                                        isVitalNormal(
                                                            form.temperature,
                                                            'temperature',
                                                        ),
                                                    'text-yellow-600 font-semibold':
                                                        !isVitalNormal(
                                                            form.temperature,
                                                            'temperature',
                                                        ),
                                                }"
                                            >
                                                {{ form.temperature }}°C
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Respiratory Rate -->
                                <div class="space-y-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel
                                            for="respiratory_rate"
                                            value="Respiratory Rate"
                                        />
                                        <span class="text-xs text-gray-500"
                                            >breaths/min</span
                                        >
                                    </div>
                                    <TextInput
                                        id="respiratory_rate"
                                        v-model="form.respiratory_rate"
                                        type="number"
                                        min="8"
                                        max="40"
                                        class="w-full"
                                        @blur="
                                            validateVital(
                                                'respiratory_rate',
                                                form.respiratory_rate,
                                            )
                                        "
                                        :class="{
                                            'border-red-300':
                                                errors.respiratory_rate,
                                        }"
                                    />
                                    <p
                                        v-if="errors.respiratory_rate"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.respiratory_rate }}
                                    </p>
                                    <div
                                        v-if="form.respiratory_rate"
                                        class="pt-2"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm font-medium"
                                                >Reading:</span
                                            >
                                            <span
                                                :class="{
                                                    'text-green-600 font-semibold':
                                                        isVitalNormal(
                                                            form.respiratory_rate,
                                                            'respiratory_rate',
                                                        ),
                                                    'text-yellow-600 font-semibold':
                                                        !isVitalNormal(
                                                            form.respiratory_rate,
                                                            'respiratory_rate',
                                                        ),
                                                }"
                                            >
                                                {{ form.respiratory_rate }}
                                                breaths/min
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Oxygen Saturation -->
                                <div class="space-y-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel
                                            for="oxygen_saturation"
                                            value="Oxygen Saturation"
                                        />
                                        <span class="text-xs text-gray-500"
                                            >%</span
                                        >
                                    </div>
                                    <TextInput
                                        id="oxygen_saturation"
                                        v-model="form.oxygen_saturation"
                                        type="number"
                                        min="70"
                                        max="100"
                                        class="w-full"
                                        @blur="
                                            validateVital(
                                                'oxygen_saturation',
                                                form.oxygen_saturation,
                                            )
                                        "
                                        :class="{
                                            'border-red-300':
                                                errors.oxygen_saturation,
                                        }"
                                    />
                                    <p
                                        v-if="errors.oxygen_saturation"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.oxygen_saturation }}
                                    </p>
                                    <div
                                        v-if="form.oxygen_saturation"
                                        class="pt-2"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm font-medium"
                                                >Reading:</span
                                            >
                                            <span
                                                :class="{
                                                    'text-green-600 font-semibold':
                                                        isVitalNormal(
                                                            form.oxygen_saturation,
                                                            'oxygen_saturation',
                                                        ),
                                                    'text-yellow-600 font-semibold':
                                                        !isVitalNormal(
                                                            form.oxygen_saturation,
                                                            'oxygen_saturation',
                                                        ),
                                                }"
                                            >
                                                {{ form.oxygen_saturation }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Pain Scale -->
                                <div class="space-y-2">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <InputLabel
                                            for="pain_scale"
                                            value="Pain Scale"
                                        />
                                        <span class="text-xs text-gray-500"
                                            >0-10</span
                                        >
                                    </div>
                                    <div class="relative">
                                        <TextInput
                                            id="pain_scale"
                                            v-model="form.pain_scale"
                                            type="number"
                                            min="0"
                                            max="10"
                                            class="w-full"
                                            placeholder="Optional"
                                        />
                                        <div
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-400"
                                        >
                                            0-10 scale
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Weight & Height (full width) -->
                                <div class="md:col-span-2">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                    >
                                        <div class="space-y-2">
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <InputLabel
                                                    for="weight"
                                                    value="Weight"
                                                />
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >kg</span
                                                >
                                            </div>
                                            <TextInput
                                                id="weight"
                                                v-model="form.weight"
                                                type="number"
                                                step="0.1"
                                                min="1"
                                                max="300"
                                                class="w-full"
                                                @blur="
                                                    validateVital(
                                                        'weight',
                                                        form.weight,
                                                    )
                                                "
                                                :class="{
                                                    'border-red-300':
                                                        errors.weight,
                                                }"
                                            />
                                            <p
                                                v-if="errors.weight"
                                                class="mt-1 text-sm text-red-600"
                                            >
                                                {{ errors.weight }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <InputLabel
                                                    for="height"
                                                    value="Height"
                                                />
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >cm</span
                                                >
                                            </div>
                                            <TextInput
                                                id="height"
                                                v-model="form.height"
                                                type="number"
                                                step="0.1"
                                                min="30"
                                                max="250"
                                                class="w-full"
                                                @blur="
                                                    validateVital(
                                                        'height',
                                                        form.height,
                                                    )
                                                "
                                                :class="{
                                                    'border-red-300':
                                                        errors.height,
                                                }"
                                            />
                                            <p
                                                v-if="errors.height"
                                                class="mt-1 text-sm text-red-600"
                                            >
                                                {{ errors.height }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: BMI Display (full width) -->
                                <div v-if="bmiComputed" class="md:col-span-2">
                                    <div
                                        class="p-4 rounded-lg"
                                        :class="bmiCategory?.bgColor"
                                    >
                                        <div
                                            class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-gray-700"
                                                >
                                                    Body Mass Index (BMI)
                                                </p>
                                                <p
                                                    class="text-2xl font-bold mt-1"
                                                    :class="bmiCategory?.color"
                                                >
                                                    {{ bmiComputed }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="px-3 py-1 text-sm font-medium rounded-full"
                                                    :class="[
                                                        bmiCategory?.bgColor.replace(
                                                            '100',
                                                            '200',
                                                        ),
                                                        bmiCategory?.color,
                                                    ]"
                                                >
                                                    {{ bmiCategory?.category }}
                                                </span>
                                                <TextInput
                                                    v-model="form.bmi"
                                                    type="hidden"
                                                />
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div
                                                class="flex text-xs text-gray-600 mb-1"
                                            >
                                                <span class="w-1/4 text-center"
                                                    >Underweight</span
                                                >
                                                <span class="w-1/4 text-center"
                                                    >Normal</span
                                                >
                                                <span class="w-1/4 text-center"
                                                    >Overweight</span
                                                >
                                                <span class="w-1/4 text-center"
                                                    >Obese</span
                                                >
                                            </div>
                                            <div
                                                class="h-2 bg-gray-200 rounded-full overflow-hidden"
                                            >
                                                <div class="h-full flex">
                                                    <div
                                                        class="w-1/4 bg-yellow-400"
                                                    ></div>
                                                    <div
                                                        class="w-1/4 bg-green-500"
                                                    ></div>
                                                    <div
                                                        class="w-1/4 bg-orange-500"
                                                    ></div>
                                                    <div
                                                        class="w-1/4 bg-red-600"
                                                    ></div>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-1 flex justify-between text-xs text-gray-600"
                                            >
                                                <span>&lt;18.5</span>
                                                <span>18.5-24.9</span>
                                                <span>25-29.9</span>
                                                <span>≥30</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <InputLabel
                                    for="notes"
                                    value="Clinical Notes"
                                />
                                <TextArea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="4"
                                    class="mt-1 block w-full"
                                    placeholder="Additional observations, patient complaints, etc."
                                />
                            </div>

                            <!-- Form Actions -->
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-6 text-center border-t border-gray-200"
                            >
                                <SecondaryButton
                                    type="button"
                                    @click="resetForm"
                                >
                                    Reset
                                </SecondaryButton>
                                <SecondaryButton
                                    type="button"
                                    @click="loadLastVitals"
                                >
                                    Load Last Vitals
                                </SecondaryButton>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing"
                                        >Recording...</span
                                    >
                                    <span v-else>Record Vital Signs</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Quick Actions & Last Recording - FIXED -->
                <div class="space-y-6">
                    <!-- Quick Entry Actions -->
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Quick Actions
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-700 mb-3"
                                >
                                    Common Values
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <SecondaryButton
                                        @click="
                                            quickEntry(
                                                'blood_pressure_systolic',
                                                '120',
                                            );
                                            quickEntry(
                                                'blood_pressure_diastolic',
                                                '80',
                                            );
                                        "
                                        size="sm"
                                        class="w-full justify-center"
                                    >
                                        <span class="text-sm">BP: 120/80</span>
                                    </SecondaryButton>
                                    <SecondaryButton
                                        @click="quickEntry('heart_rate', '72')"
                                        size="sm"
                                        class="w-full justify-center"
                                    >
                                        <span class="text-sm">HR: 72 bpm</span>
                                    </SecondaryButton>
                                    <SecondaryButton
                                        @click="
                                            quickEntry('temperature', '36.8')
                                        "
                                        size="sm"
                                        class="w-full justify-center"
                                    >
                                        <span class="text-sm"
                                            >Temp: 36.8°C</span
                                        >
                                    </SecondaryButton>
                                    <SecondaryButton
                                        @click="
                                            quickEntry(
                                                'oxygen_saturation',
                                                '98',
                                            )
                                        "
                                        size="sm"
                                        class="w-full justify-center"
                                    >
                                        <span class="text-sm">SpO₂: 98%</span>
                                    </SecondaryButton>
                                </div>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-700 mb-3"
                                >
                                    Calculate BMI
                                </p>
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <InputLabel
                                                for="quick_weight"
                                                value="Weight (kg)"
                                                class="text-xs mb-1"
                                            />
                                            <TextInput
                                                id="quick_weight"
                                                v-model="form.weight"
                                                type="number"
                                                step="0.1"
                                                placeholder="e.g., 70"
                                                class="w-full text-sm"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                for="quick_height"
                                                value="Height (cm)"
                                                class="text-xs mb-1"
                                            />
                                            <TextInput
                                                id="quick_height"
                                                v-model="form.height"
                                                type="number"
                                                step="0.1"
                                                placeholder="e.g., 170"
                                                class="w-full text-sm"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        v-if="bmiComputed"
                                        class="p-3 bg-gray-50 rounded-lg text-center"
                                    >
                                        <p class="text-sm text-gray-600">
                                            Calculated BMI
                                        </p>
                                        <p
                                            class="text-xl font-bold mt-1"
                                            :class="bmiCategory?.color"
                                        >
                                            {{ bmiComputed }}
                                        </p>
                                        <p
                                            class="text-xs mt-1"
                                            :class="bmiCategory?.color"
                                        >
                                            {{ bmiCategory?.category }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Recorded Vitals -->
                    <div
                        v-if="lastRecordedVitals"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Last Recording
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-if="
                                    lastRecordedVitals.blood_pressure_systolic
                                "
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600"
                                    >Blood Pressure:</span
                                >
                                <span class="text-sm font-medium">
                                    {{
                                        lastRecordedVitals.blood_pressure_systolic
                                    }}/{{
                                        lastRecordedVitals.blood_pressure_diastolic
                                    }}
                                    mmHg
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.heart_rate"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600"
                                    >Heart Rate:</span
                                >
                                <span class="text-sm font-medium">
                                    {{ lastRecordedVitals.heart_rate }} bpm
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.temperature"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600"
                                    >Temperature:</span
                                >
                                <span class="text-sm font-medium">
                                    {{ lastRecordedVitals.temperature }}°C
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.oxygen_saturation"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600">SpO₂:</span>
                                <span class="text-sm font-medium">
                                    {{ lastRecordedVitals.oxygen_saturation }}%
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.weight"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600"
                                    >Weight:</span
                                >
                                <span class="text-sm font-medium">
                                    {{ lastRecordedVitals.weight }} kg
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.height"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-gray-600"
                                    >Height:</span
                                >
                                <span class="text-sm font-medium">
                                    {{ lastRecordedVitals.height }} cm
                                </span>
                            </div>
                            <div
                                v-if="lastRecordedVitals.recorded_at"
                                class="pt-3 border-t border-gray-100"
                            >
                                <p class="text-xs text-gray-500">
                                    Recorded at:
                                    {{
                                        new Date(
                                            lastRecordedVitals.recorded_at,
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Normal Ranges Reference -->
                    <div
                        class="bg-blue-50 rounded-lg border border-blue-200 p-6"
                    >
                        <h3 class="text-lg font-medium text-blue-900 mb-3">
                            Normal Ranges
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-blue-700"
                                    >Blood Pressure:</span
                                >
                                <span class="font-medium text-blue-800"
                                    >90-120/60-80 mmHg</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Heart Rate:</span>
                                <span class="font-medium text-blue-800"
                                    >60-100 bpm</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Temperature:</span>
                                <span class="font-medium text-blue-800"
                                    >36.1-37.2°C</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700"
                                    >Respiratory Rate:</span
                                >
                                <span class="font-medium text-blue-800"
                                    >12-20 breaths/min</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700"
                                    >Oxygen Saturation:</span
                                >
                                <span class="font-medium text-blue-800"
                                    >95-100%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Connection Modal - FIXED -->

        <Modal
            :show="showDeviceModal"
            @close="showDeviceModal = false"
            max-width="2xl"
        >
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4 sm:mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                        Connect Medical Device
                    </h3>
                    <button
                        @click="showDeviceModal = false"
                        class="text-gray-400 hover:text-gray-500 p-1"
                    >
                        <svg
                            class="w-5 h-5 sm:w-6 sm:h-6"
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

                <div class="space-y-4 sm:space-y-6">
                    <!-- Connection Status -->
                    <div
                        v-if="connectionStatus === 'connecting'"
                        class="p-3 sm:p-4 bg-blue-50 rounded-lg"
                    >
                        <div class="flex items-start sm:items-center">
                            <div
                                class="animate-spin rounded-full h-5 w-5 sm:h-6 sm:w-6 border-b-2 border-blue-600 flex-shrink-0 mt-0.5 sm:mt-0"
                            ></div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">
                                    Connecting to device...
                                </p>
                                <p
                                    class="text-xs sm:text-sm text-blue-600 mt-1"
                                >
                                    Please ensure the device is turned on and in
                                    range
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Available Devices -->
                    <div>
                        <h4
                            class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4"
                        >
                            Available Devices
                        </h4>
                        <div class="grid grid-cols-1 gap-3 sm:gap-4">
                            <div
                                v-for="device in availableDevices"
                                :key="device.id"
                                class="p-3 sm:p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors duration-200"
                            >
                                <div
                                    class="flex flex-col sm:flex-row sm:items-start justify-between gap-3"
                                >
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                            >
                                                <svg
                                                    class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="font-medium text-gray-900 text-sm sm:text-base truncate"
                                            >
                                                {{ device.name }}
                                            </p>
                                            <p
                                                class="text-xs sm:text-sm text-gray-500 mt-0.5"
                                            >
                                                {{
                                                    device.type.replace(
                                                        "_",
                                                        " ",
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-col items-start sm:items-end space-y-2"
                                    >
                                        <div class="flex items-center">
                                            <div
                                                class="w-2 h-2 rounded-full mr-2"
                                                :class="{
                                                    'bg-green-500':
                                                        device.status ===
                                                        'available',
                                                    'bg-red-500':
                                                        device.status ===
                                                        'unavailable',
                                                    'bg-yellow-500':
                                                        device.status ===
                                                        'busy',
                                                }"
                                            ></div>
                                            <span
                                                class="text-xs px-2 py-1 rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800':
                                                        device.status ===
                                                        'available',
                                                    'bg-red-100 text-red-800':
                                                        device.status ===
                                                        'unavailable',
                                                    'bg-yellow-100 text-yellow-800':
                                                        device.status ===
                                                        'busy',
                                                }"
                                            >
                                                {{ device.status }}
                                            </span>
                                        </div>
                                        <button
                                            @click="connectToDevice(device)"
                                            :disabled="
                                                device.status !== 'available'
                                            "
                                            class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed w-full sm:w-auto"
                                        >
                                            {{
                                                device.status === "available"
                                                    ? "Connect"
                                                    : "Unavailable"
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Device Entry -->
                    <div class="border-t border-gray-200 pt-4 sm:pt-6">
                        <h4
                            class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4"
                        >
                            Manual Device Entry
                        </h4>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <InputLabel
                                    for="manual_device_name"
                                    value="Device Name"
                                    class="text-sm sm:text-base"
                                />
                                <TextInput
                                    id="manual_device_name"
                                    class="mt-1 block w-full text-sm sm:text-base"
                                    placeholder="Enter device name"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    for="manual_device_type"
                                    value="Device Type"
                                    class="text-sm sm:text-base"
                                />
                                <SelectInput
                                    id="manual_device_type"
                                    class="mt-1 block w-full text-sm sm:text-base"
                                >
                                    <option value="">Select type</option>
                                    <option value="blood_pressure">
                                        Blood Pressure Monitor
                                    </option>
                                    <option value="thermometer">
                                        Thermometer
                                    </option>
                                    <option value="pulse_oximeter">
                                        Pulse Oximeter
                                    </option>
                                    <option value="scale">Scale</option>
                                    <option value="other">Other</option>
                                </SelectInput>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3"
                            >
                                <SecondaryButton
                                    class="flex-1 w-full sm:w-auto"
                                >
                                    Add Device
                                </SecondaryButton>
                                <PrimaryButton class="flex-1 w-full sm:w-auto">
                                    Save & Connect
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Vital History Modal -->
        <Modal
            :show="showHistoryModal"
            @close="showHistoryModal = false"
            max-width="2xl"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">
                        Vital Signs History
                    </h3>
                    <button
                        @click="showHistoryModal = false"
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

                <div v-if="vitalHistory.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date/Time
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    BP
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    HR
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Temp
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    SpO₂
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Weight
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Height
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    BMI
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Source
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Recorded By
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Recorded At
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="record in vitalHistory" :key="record.id">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{
                                        record.recorded_at
                                            ? new Date(
                                                  record.recorded_at,
                                              ).toLocaleString()
                                            : "N/A"
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                        {{ record.blood_pressure_systolic }}/{{
                                            record.blood_pressure_diastolic
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        :class="{
                                            'text-green-600': isVitalNormal(
                                                record.heart_rate,
                                                'heart_rate',
                                            ),
                                            'text-red-600': !isVitalNormal(
                                                record.heart_rate,
                                                'heart_rate',
                                            ),
                                        }"
                                    >
                                        {{ record.heart_rate }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        :class="{
                                            'text-green-600': isVitalNormal(
                                                record.temperature,
                                                'temperature',
                                            ),
                                            'text-red-600': !isVitalNormal(
                                                record.temperature,
                                                'temperature',
                                            ),
                                        }"
                                    >
                                        {{ record.temperature }}°C
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        :class="{
                                            'text-green-600': isVitalNormal(
                                                record.oxygen_saturation,
                                                'oxygen_saturation',
                                            ),
                                            'text-red-600': !isVitalNormal(
                                                record.oxygen_saturation,
                                                'oxygen_saturation',
                                            ),
                                        }"
                                    >
                                        {{ record.oxygen_saturation }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ record.weight }}kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ record.height }}cm
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{
                                        calculateBMI(
                                            record.weight,
                                            record.height,
                                        )
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full"
                                        :class="{
                                            'bg-blue-100 text-blue-800':
                                                record.data_source === 'device',
                                            'bg-gray-100 text-gray-800':
                                                record.data_source === 'manual',
                                        }"
                                    >
                                        {{ record.data_source }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ record.recorded_by.first_name }}
                                    {{ record.recorded_by.last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{
                                        record.recorded_at
                                            ? new Date(
                                                  record.recorded_at,
                                              ).toLocaleString("en-GB", {
                                                  day: "2-digit",
                                                  month: "short",
                                                  year: "numeric",
                                                  hour: "2-digit",
                                                  minute: "2-digit",
                                              })
                                            : "N/A"
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="vitalHistory.length === 0" class="text-center py-12">
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
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No vital signs recorded
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Start by recording the patient's vital signs.
                    </p>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
