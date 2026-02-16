<!-- resources/js/Pages/Doctor/Consultation/Show.vue -->
<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Modal from "@/Components/Modal.vue";
import {
    DocumentTextIcon,
    HeartIcon,
    BeakerIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    UserIcon,
    CalendarIcon,
    CheckCircleIcon,
    PlusIcon,
    DocumentDuplicateIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    appointment: Object,
    previousAppointments: Array,
    noteTemplates: Array,
});

const page = usePage();

// Form data
const form = ref({
    doctor_notes: props.appointment.doctor_notes || "",
    diagnosis: props.appointment.diagnosis || "",
    prescription: props.appointment.prescription || "",
    lab_orders: props.appointment.lab_orders || "",
    follow_up_instructions: props.appointment.follow_up_instructions || "",
    follow_up_date: props.appointment.follow_up_date || "",
    status: "",
});

const activeTab = ref("consultation");
const showTemplateModal = ref(false);
const selectedTemplate = ref(null);
const processing = ref(false);

// Computed properties
const consultationDuration = computed(() => {
    if (!props.appointment.consultation_started_at) return "Not started";

    const start = new Date(props.appointment.consultation_started_at);
    const end = props.appointment.consultation_ended_at
        ? new Date(props.appointment.consultation_ended_at)
        : new Date();

    const diffMs = end - start;
    const diffMins = Math.floor(diffMs / 60000);
    const hours = Math.floor(diffMins / 60);
    const mins = diffMins % 60;

    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
});

// Methods
const updateConsultation = () => {
    processing.value = true;

    router.put(`/consultation/${props.appointment.id}`, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const completeConsultation = () => {
    if (confirm("Are you sure you want to complete this consultation?")) {
        router.post(
            `/${props.appointment.id}/complete`,
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const useTemplate = (template) => {
    if (activeTab.value === "consultation") {
        form.value.doctor_notes = form.value.doctor_notes
            ? form.value.doctor_notes + "\n\n" + template.content
            : template.content;
    } else if (activeTab.value === "diagnosis") {
        form.value.diagnosis = form.value.diagnosis
            ? form.value.diagnosis + "\n\n" + template.content
            : template.content;
    } else if (activeTab.value === "prescription") {
        form.value.prescription = form.value.prescription
            ? form.value.prescription + "\n\n" + template.content
            : template.content;
    }

    // Increment usage count
    router.post(`/note-templates/${template.id}/increment-usage`);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
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
</script>

<template>
    <AppLayout :title="`Consultation - ${appointment.appointment_no}`">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Consultation
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Appointment #{{ appointment.appointment_no }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span
                        :class="[
                            'px-3 py-1 rounded-full text-sm font-medium',
                            getStatusColor(appointment.status),
                        ]"
                    >
                        {{ appointment.status }}
                    </span>
                    <span
                        v-if="appointment.priority"
                        :class="[
                            'px-3 py-1 rounded-full text-sm font-medium',
                            appointment.priority === 'emergency'
                                ? 'bg-red-100 text-red-800'
                                : appointment.priority === 'high'
                                  ? 'bg-orange-100 text-orange-800'
                                  : appointment.priority === 'medium'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-green-100 text-green-800',
                        ]"
                    >
                        {{ appointment.priority }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Patient Info Card -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6"
                >
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center"
                                >
                                    <UserIcon class="h-8 w-8 text-blue-600" />
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ appointment.patient?.first_name }}
                                        {{ appointment.patient?.last_name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Patient ID: {{ appointment.patient_no }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Age:
                                        {{
                                            appointment.patient?.date_of_birth
                                                ? new Date().getFullYear() -
                                                  new Date(
                                                      appointment.patient
                                                          .date_of_birth,
                                                  ).getFullYear()
                                                : "N/A"
                                        }}
                                        years
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="flex items-center text-sm text-gray-600 mb-1"
                                >
                                    <CalendarIcon class="h-4 w-4 mr-1" />
                                    {{ formatDate(appointment.scheduled_date) }}
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
                                >
                                    <ClockIcon class="h-4 w-4 mr-1" />
                                    {{ formatTime(appointment.scheduled_time) }}
                                </div>
                                <div
                                    class="mt-2 text-sm font-medium text-blue-600"
                                >
                                    Duration: {{ consultationDuration }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Left Column - Templates & History -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Quick Templates -->
                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200"
                        >
                            <div
                                class="p-4 border-b border-gray-200 bg-gray-50"
                            >
                                <h3
                                    class="font-medium text-gray-900 flex items-center"
                                >
                                    <DocumentDuplicateIcon
                                        class="h-5 w-5 mr-2 text-gray-500"
                                    />
                                    Quick Templates
                                </h3>
                            </div>
                            <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                                <button
                                    v-for="template in noteTemplates"
                                    :key="template.id"
                                    @click="useTemplate(template)"
                                    class="w-full text-left p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
                                >
                                    <div
                                        class="font-medium text-sm text-gray-900"
                                    >
                                        {{ template.name }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 mt-1 line-clamp-2"
                                    >
                                        {{
                                            template.content.substring(0, 100)
                                        }}...
                                    </div>
                                </button>
                                <div
                                    v-if="!noteTemplates.length"
                                    class="text-center py-4 text-gray-500 text-sm"
                                >
                                    No templates available
                                </div>
                            </div>
                        </div>

                        <!-- Previous Appointments -->
                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200"
                        >
                            <div
                                class="p-4 border-b border-gray-200 bg-gray-50"
                            >
                                <h3
                                    class="font-medium text-gray-900 flex items-center"
                                >
                                    <ClipboardDocumentListIcon
                                        class="h-5 w-5 mr-2 text-gray-500"
                                    />
                                    Previous Visits
                                </h3>
                            </div>
                            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                                <div
                                    v-for="visit in previousAppointments"
                                    :key="visit.id"
                                    class="border border-gray-200 rounded-lg p-3 hover:border-blue-300"
                                >
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span
                                            class="text-xs font-medium text-gray-600"
                                        >
                                            {{
                                                formatDate(visit.scheduled_date)
                                            }}
                                        </span>
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded-full text-xs',
                                                getStatusColor(visit.status),
                                            ]"
                                        >
                                            {{ visit.status }}
                                        </span>
                                    </div>
                                    <div
                                        class="text-sm text-gray-700 line-clamp-2"
                                    >
                                        {{ visit.doctor_notes || "No notes" }}
                                    </div>
                                </div>
                                <div
                                    v-if="!previousAppointments.length"
                                    class="text-center py-4 text-gray-500 text-sm"
                                >
                                    No previous visits
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Consultation Forms -->
                    <div class="lg:col-span-3">
                        <div
                            class="bg-white rounded-lg shadow-sm border border-gray-200"
                        >
                            <!-- Tabs -->
                            <div class="border-b border-gray-200">
                                <nav class="flex -mb-px">
                                    <button
                                        @click="activeTab = 'consultation'"
                                        :class="[
                                            activeTab === 'consultation'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'px-6 py-3 border-b-2 font-medium text-sm',
                                        ]"
                                    >
                                        Consultation Notes xxx
                                    </button>
                                    <button
                                        @click="activeTab = 'diagnosis'"
                                        :class="[
                                            activeTab === 'diagnosis'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'px-6 py-3 border-b-2 font-medium text-sm',
                                        ]"
                                    >
                                        Diagnosis
                                    </button>
                                    <button
                                        @click="activeTab = 'prescription'"
                                        :class="[
                                            activeTab === 'prescription'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'px-6 py-3 border-b-2 font-medium text-sm',
                                        ]"
                                    >
                                        Prescription
                                    </button>
                                    <button
                                        @click="activeTab = 'lab'"
                                        :class="[
                                            activeTab === 'lab'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'px-6 py-3 border-b-2 font-medium text-sm',
                                        ]"
                                    >
                                        Lab Orders
                                    </button>
                                    <button
                                        @click="activeTab = 'followup'"
                                        :class="[
                                            activeTab === 'followup'
                                                ? 'border-blue-500 text-blue-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'px-6 py-3 border-b-2 font-medium text-sm',
                                        ]"
                                    >
                                        Follow-up
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-6">
                                <!-- Consultation Notes -->
                                <div
                                    v-if="activeTab === 'consultation'"
                                    class="space-y-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="doctor_notes"
                                            value="Consultation Notes"
                                        />
                                        <TextArea
                                            id="doctor_notes"
                                            v-model="form.doctor_notes"
                                            rows="12"
                                            class="mt-1 block w-full"
                                            placeholder="Enter your consultation notes here..."
                                        />
                                    </div>
                                </div>

                                <!-- Diagnosis -->
                                <div
                                    v-if="activeTab === 'diagnosis'"
                                    class="space-y-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="diagnosis"
                                            value="Diagnosis"
                                        />
                                        <TextArea
                                            id="diagnosis"
                                            v-model="form.diagnosis"
                                            rows="8"
                                            class="mt-1 block w-full"
                                            placeholder="Enter diagnosis and assessment..."
                                        />
                                    </div>
                                </div>

                                <!-- Prescription -->
                                <div
                                    v-if="activeTab === 'prescription'"
                                    class="space-y-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="prescription"
                                            value="Prescription"
                                        />
                                        <TextArea
                                            id="prescription"
                                            v-model="form.prescription"
                                            rows="8"
                                            class="mt-1 block w-full font-mono"
                                            placeholder="Medication, dosage, frequency, duration..."
                                        />
                                    </div>
                                </div>

                                <!-- Lab Orders -->
                                <div
                                    v-if="activeTab === 'lab'"
                                    class="space-y-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="lab_orders"
                                            value="Laboratory Orders"
                                        />
                                        <TextArea
                                            id="lab_orders"
                                            v-model="form.lab_orders"
                                            rows="6"
                                            class="mt-1 block w-full"
                                            placeholder="Blood tests, imaging, cultures, etc..."
                                        />
                                    </div>
                                </div>

                                <!-- Follow-up -->
                                <div
                                    v-if="activeTab === 'followup'"
                                    class="space-y-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="follow_up_instructions"
                                            value="Follow-up Instructions"
                                        />
                                        <TextArea
                                            id="follow_up_instructions"
                                            v-model="
                                                form.follow_up_instructions
                                            "
                                            rows="4"
                                            class="mt-1 block w-full"
                                            placeholder="Instructions for follow-up care..."
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="follow_up_date"
                                            value="Follow-up Date"
                                        />
                                        <TextInput
                                            id="follow_up_date"
                                            v-model="form.follow_up_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="status"
                                            value="Appointment Status"
                                        />
                                        <SelectInput
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full"
                                        >
                                            <option value="">
                                                Keep current status
                                            </option>
                                            <option value="completed">
                                                Complete & Close
                                            </option>
                                            <option value="follow-up">
                                                Schedule Follow-up
                                            </option>
                                            <option value="rescheduled">
                                                Reschedule
                                            </option>
                                        </SelectInput>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200"
                                >
                                    <div class="flex items-center space-x-3">
                                        <PrimaryButton
                                            @click="updateConsultation"
                                            :disabled="processing"
                                        >
                                            {{
                                                processing
                                                    ? "Saving..."
                                                    : "Save Changes"
                                            }}
                                        </PrimaryButton>
                                        <!-- <SecondaryButton
                                            @click="useTemplate"
                                            v-if="noteTemplates.length"
                                        >
                                            Insert Template
                                        </SecondaryButton> -->
                                    </div>
                                    <DangerButton
                                        v-if="
                                            appointment.status !== 'completed'
                                        "
                                        @click="completeConsultation"
                                    >
                                        Complete Consultation
                                    </DangerButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
