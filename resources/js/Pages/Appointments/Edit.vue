<script setup>
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, onMounted, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    appointment: {
        type: Object,
        default: () => ({}),
    },
    patients: {
        type: Array,
        default: () => [],
    },
    doctors: {
        type: Array,
        default: () => [],
    },
    doctorsSchedules: {
        type: Object,
        default: () => {},
    },
    doctorsException: {
        type: Array,
        default: () => [],
    },
    existingAppointments: {
        type: Array,
        default: () => [],
    },
});

// Form - initialize with appointment data
const form = useForm({
    appointment_no: props.appointment?.appointment_no || "",
    patient_id: props.appointment?.patient_id || "",
    doctor_id: props.appointment?.doctor_id || "",
    appointment_type: props.appointment?.appointment_type || "",
    status: props.appointment?.status || "scheduled",
    scheduled_date: props.appointment?.scheduled_date || "",
    scheduled_time: props.appointment?.scheduled_time || "",
    estimated_duration: props.appointment?.estimated_duration || "30",
    reason: props.appointment?.reason || "",
    symptoms: props.appointment?.symptoms || "",
    priority: props.appointment?.priority || "low",
    actual_start_time: props.appointment?.actual_start_time || null,
    actual_end_time: props.appointment?.actual_end_time || null,
});

// Appointment types
const appointmentTypes = [
    "consultation",
    "follow up",
    "routine check",
    "emergency",
    "procedure",
    "lab test",
    "vaccination",
    "therapy",
    "other",
];

// Status options
const statusOptions = [
    { value: "scheduled", label: "scheduled" },
    { value: "confirmed", label: "confirmed" },
    { value: "completed", label: "completed" },
    { value: "cancelled", label: "cancelled" },
    { value: "no show", label: "No show" },
    { value: "rescheduled", label: "rescheduled" },
];

// Priority options
const priorityOptions = [
    { value: "low", label: "low" },
    { value: "medium", label: "medium" },
    { value: "high", label: "high" },
    { value: "emergency", label: "emergency" },
];

// Duration options
const durationOptions = [
    { value: "15", label: "15 minutes" },
    { value: "30", label: "30 minutes" },
    { value: "45", label: "45 minutes" },
    { value: "60", label: "1 hour" },
    { value: "90", label: "1.5 hours" },
    { value: "120", label: "2 hours" },
];

// Calendar & Scheduling
const showCalendarModal = ref(false);
const selectedDate = ref(
    props.appointment?.scheduled_date
        ? new Date(props.appointment.scheduled_date)
        : null
);
const selectedTime = ref(
    props.appointment?.scheduled_time
        ? {
              time: props.appointment.scheduled_time,
              display: formatTimeForDisplay(props.appointment.scheduled_time),
          }
        : null
);
const calendarView = ref("month");
const currentMonth = ref(
    selectedDate.value ? selectedDate.value.getMonth() : new Date().getMonth()
);
const currentYear = ref(
    selectedDate.value
        ? selectedDate.value.getFullYear()
        : new Date().getFullYear()
);
const availableSlots = ref([]);
const availableDates = ref([]);
const doctorSchedule = ref([]);
const scheduleExceptions = ref([]);
const bookedSlots = ref([]);

// Show delete confirmation modal
const showDeleteModal = ref(false);

// Check if editing
const isEditing = computed(() => !!props.appointment?.id);

// Format time for display
function formatTimeForDisplay(time) {
    if (!time) return "";
    const [hours, minutes] = time.split(":").map(Number);
    const period = hours >= 12 ? "PM" : "AM";
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes.toString().padStart(2, "0")} ${period}`;
}

// Initialize calendar
function initCalendar() {
    if (selectedDate.value) {
        currentMonth.value = selectedDate.value.getMonth();
        currentYear.value = selectedDate.value.getFullYear();
    } else {
        const today = new Date();
        currentMonth.value = today.getMonth();
        currentYear.value = today.getFullYear();
    }
}

// Get days in month
function getDaysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
}

// Get first day of month
function getFirstDayOfMonth(month, year) {
    return new Date(year, month, 1).getDay();
}

// Generate calendar grid
const calendarDays = computed(() => {
    const daysInMonth = getDaysInMonth(currentMonth.value, currentYear.value);
    const firstDay = getFirstDayOfMonth(currentMonth.value, currentYear.value);
    const days = [];

    // Previous month's days
    const prevMonthDays = getDaysInMonth(
        currentMonth.value - 1 < 0 ? 11 : currentMonth.value - 1,
        currentMonth.value - 1 < 0 ? currentYear.value - 1 : currentYear.value
    );

    for (let i = 0; i < firstDay; i++) {
        const day = prevMonthDays - firstDay + i + 1;
        const date = new Date(
            currentMonth.value - 1 < 0
                ? currentYear.value - 1
                : currentYear.value,
            currentMonth.value - 1 < 0 ? 11 : currentMonth.value - 1,
            day
        );
        days.push({
            day,
            date,
            isCurrentMonth: false,
            isToday: false,
            isSelected: false,
            isAvailable: isDateAvailable(date),
        });
    }

    // Current month's days
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(currentYear.value, currentMonth.value, i);
        days.push({
            day: i,
            date,
            isCurrentMonth: true,
            isToday:
                date.getDate() === today.getDate() &&
                date.getMonth() === today.getMonth() &&
                date.getFullYear() === today.getFullYear(),
            isSelected:
                selectedDate.value &&
                date.toDateString() === selectedDate.value.toDateString(),
            isAvailable: isDateAvailable(date),
        });
    }

    // Next month's days
    const totalCells = 42; // 6 weeks
    const nextMonthStartDay = 1;
    for (let i = days.length; i < totalCells; i++) {
        const day = nextMonthStartDay + (i - days.length);
        const date = new Date(
            currentMonth.value + 1 > 11
                ? currentYear.value + 1
                : currentYear.value,
            currentMonth.value + 1 > 11 ? 0 : currentMonth.value + 1,
            day
        );
        days.push({
            day,
            date,
            isCurrentMonth: false,
            isToday: false,
            isSelected: false,
            isAvailable: isDateAvailable(date),
        });
    }

    return days;
});

// Get month name
const monthName = computed(() => {
    return new Date(
        currentYear.value,
        currentMonth.value,
        1
    ).toLocaleDateString("en-US", { month: "long" });
});

// Navigate calendar
function prevMonth() {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
}

function nextMonth() {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
}

// Get doctor's schedule
function getDoctorSchedule() {
    if (!form.doctor_id) {
        doctorSchedule.value = [];
        scheduleExceptions.value = [];
        bookedSlots.value = [];
        return;
    }

    doctorSchedule.value = props.doctorsSchedules[form.doctor_id] || [];
    scheduleExceptions.value = props.doctorsException.filter(
        (exception) => exception.doctor_id == form.doctor_id
    );

    // Get booked slots for this doctor (excluding current appointment)
    getBookedSlotsForDoctor();

    // Generate available dates for next 90 days
    generateAvailableDates();
}

function getBookedSlotsForDoctor() {
    if (!form.doctor_id) {
        bookedSlots.value = [];
        return;
    }

    // Filter existing appointments for this doctor, excluding the current appointment
    bookedSlots.value = props.existingAppointments
        .filter(
            (appointment) =>
                appointment.doctor_id == form.doctor_id &&
                appointment.id !== props.appointment?.id // Exclude current appointment
        )
        .map((appointment) => ({
            date: appointment.scheduled_date,
            time: appointment.scheduled_time,
            duration: parseInt(appointment.estimated_duration) || 30,
        }));
}

// Check if a time slot is booked
function isTimeSlotBooked(date, time) {
    const dateString = date.toISOString().split("T")[0];
    const timeString = time;

    // Check for exact time match
    const exactMatch = bookedSlots.value.some(
        (slot) => slot.date === dateString && slot.time === timeString
    );

    if (exactMatch) return true;

    // Check for overlapping appointments
    const slotDuration = parseInt(form.estimated_duration) || 30;
    const [slotHour, slotMinute] = timeString.split(":").map(Number);

    for (const bookedSlot of bookedSlots.value) {
        if (bookedSlot.date !== dateString) continue;

        const [bookedHour, bookedMinute] = bookedSlot.time
            .split(":")
            .map(Number);
        const bookedEndMinute = bookedMinute + bookedSlot.duration;
        const bookedEndHour = bookedHour + Math.floor(bookedEndMinute / 60);
        const bookedEndMinuteAdj = bookedEndMinute % 60;

        const slotEndMinute = slotMinute + slotDuration;
        const slotEndHour = slotHour + Math.floor(slotEndMinute / 60);
        const slotEndMinuteAdj = slotEndMinute % 60;

        // Check for overlap
        const slotStart = slotHour * 60 + slotMinute;
        const slotEnd = slotEndHour * 60 + slotEndMinuteAdj;
        const bookedStart = bookedHour * 60 + bookedMinute;
        const bookedEnd = bookedEndHour * 60 + bookedEndMinuteAdj;

        if (slotStart < bookedEnd && slotEnd > bookedStart) {
            return true;
        }
    }

    return false;
}

// Generate available dates (next 90 days)
function generateAvailableDates() {
    availableDates.value = [];
    const today = new Date();

    for (let i = 0; i <= 90; i++) {
        // Include today
        const date = new Date(today);
        date.setDate(today.getDate() + i);

        const dayOfWeek = date.getDay();
        const dayName = date
            .toLocaleDateString("en-US", { weekday: "long" })
            .toLowerCase();

        // Check if doctor works on this day
        const daySchedule = doctorSchedule.value.find(
            (s) => s.day_of_week.toLowerCase() === dayName && s.is_active
        );

        if (daySchedule) {
            // Check for exceptions
            const exception = scheduleExceptions.value.find(
                (e) =>
                    new Date(e.exception_date).toDateString() ===
                    date.toDateString()
            );

            // Only add if no exception or doctor is available on exception day
            if (!exception || exception.is_available) {
                availableDates.value.push({
                    date: date,
                    dayOfWeek: dayOfWeek,
                    dayName: dayName,
                    schedule: daySchedule,
                    exception: exception,
                });
            }
        }
    }
}

// Check if date is available
function isDateAvailable(date) {
    if (!form.doctor_id || availableDates.value.length === 0) return false;

    const dateString = date.toDateString();
    return availableDates.value.some(
        (avail) => avail.date.toDateString() === dateString
    );
}

// Generate time slots for selected date
function generateTimeSlots(date) {
    if (!date || !form.doctor_id || doctorSchedule.value.length === 0) {
        availableSlots.value = [];
        return;
    }

    const selectedDateObj = new Date(date);
    const dayName = selectedDateObj
        .toLocaleDateString("en-US", { weekday: "long" })
        .toLowerCase();

    // Find schedule for this day
    const daySchedule = doctorSchedule.value.find(
        (s) => s.day_of_week.toLowerCase() === dayName && s.is_active
    );

    if (!daySchedule) {
        availableSlots.value = [];
        return;
    }

    // Check for exceptions
    const exception = scheduleExceptions.value.find(
        (e) =>
            new Date(e.exception_date).toDateString() ===
            selectedDateObj.toDateString()
    );

    let startTime, endTime;

    if (exception) {
        if (!exception.is_available) {
            availableSlots.value = [];
            return;
        }
        startTime = exception.start_time;
        endTime = exception.end_time;
    } else {
        startTime = daySchedule.start_time;
        endTime = daySchedule.end_time;
    }

    // Generate slots
    const slots = [];
    const slotDuration = parseInt(form.estimated_duration) || 30;
    const [startHour, startMinute] = startTime.split(":").map(Number);
    const [endHour, endMinute] = endTime.split(":").map(Number);

    let currentHour = startHour;
    let currentMinute = startMinute;

    while (
        currentHour < endHour ||
        (currentHour === endHour && currentMinute < endMinute)
    ) {
        const timeString = `${currentHour
            .toString()
            .padStart(2, "0")}:${currentMinute.toString().padStart(2, "0")}`;

        // Check if this slot is booked
        const isBooked = isTimeSlotBooked(selectedDateObj, timeString);

        // Mark as selected if it's the current appointment time
        const isCurrentAppointment =
            selectedDateObj.toDateString() ===
                selectedDate.value?.toDateString() &&
            timeString === form.scheduled_time;

        slots.push({
            time: timeString,
            display: formatTimeForDisplay(timeString),
            isBooked: isBooked,
            isCurrentAppointment: isCurrentAppointment,
        });

        // Increment by slot duration
        currentMinute += slotDuration;
        if (currentMinute >= 60) {
            currentHour += Math.floor(currentMinute / 60);
            currentMinute = currentMinute % 60;
        }
    }

    availableSlots.value = slots;
}

// Open calendar modal
function openCalendarModal() {
    if (!form.doctor_id) {
        alert("Please select a doctor first");
        return;
    }
    showCalendarModal.value = true;
    initCalendar();
    getDoctorSchedule();
}

// Select date from calendar
function selectDate(day) {
    if (!day.isAvailable) return;

    selectedDate.value = day.date;
    selectedTime.value = null;
    generateTimeSlots(day.date);
}

// Select time slot
function selectTime(time) {
    if (time.isBooked && !time.isCurrentAppointment) {
        alert("This time slot is already booked. Please select another time.");
        return;
    }
    selectedTime.value = time;
}

// Confirm schedule selection
function confirmSchedule() {
    if (selectedDate.value && selectedTime.value) {
        form.scheduled_date = selectedDate.value.toISOString().split("T")[0];
        form.scheduled_time = selectedTime.value.time;
        showCalendarModal.value = false;
    }
}

// Form submission for update
const submitForm = () => {
    // Validate if time slot is booked (excluding current appointment)
    if (form.scheduled_date && form.scheduled_time) {
        const dateObj = new Date(form.scheduled_date);
        const timeString = form.scheduled_time;

        // Check if the slot is different from the original
        const isDifferentFromOriginal =
            form.scheduled_date !== props.appointment?.scheduled_date ||
            form.scheduled_time !== props.appointment?.scheduled_time;

        if (isDifferentFromOriginal) {
            const isBooked = isTimeSlotBooked(dateObj, timeString);

            if (isBooked) {
                alert(
                    "This time slot is already booked. Please choose another time."
                );
                return;
            }
        }
    }

    // Validate required fields
    if (
        !form.patient_id ||
        !form.doctor_id ||
        !form.appointment_type ||
        !form.scheduled_date ||
        !form.scheduled_time ||
        !form.reason
    ) {
        alert("Please fill in all required fields");
        return;
    }

    form.put(`/appointments/update/${props.appointment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
        onError: (errors) => {
            console.log("Form errors:", errors);
        },
    });
};

// Cancel handler
const handleCancel = () => {
    router.visit(route("appointments.index"));
};

// Delete appointment
const confirmDelete = () => {
    router.delete(`/appointments/destroy/${props.appointment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            router.visit("appointments.index");
        },
    });
};

// Reset form
function resetForm() {
    // Reset to original values
    form.patient_id = props.appointment?.patient_id || "";
    form.doctor_id = props.appointment?.doctor_id || "";
    form.appointment_type = props.appointment?.appointment_type || "";
    form.status = props.appointment?.status || "scheduled";
    form.scheduled_date = props.appointment?.scheduled_date || "";
    form.scheduled_time = props.appointment?.scheduled_time || "";
    form.estimated_duration = props.appointment?.estimated_duration || "30";
    form.reason = props.appointment?.reason || "";
    form.symptoms = props.appointment?.symptoms || "";
    form.priority = props.appointment?.priority || "low";

    selectedDate.value = props.appointment?.scheduled_date
        ? new Date(props.appointment.scheduled_date)
        : null;
    selectedTime.value = props.appointment?.scheduled_time
        ? {
              time: props.appointment.scheduled_time,
              display: formatTimeForDisplay(props.appointment.scheduled_time),
          }
        : null;

    getDoctorSchedule();
}

// Initialize when component mounts
onMounted(() => {
    initCalendar();
    if (form.doctor_id) {
        getDoctorSchedule();
    }
});

// Watchers
watch(
    () => form.doctor_id,
    () => {
        if (form.doctor_id === props.appointment?.doctor_id) {
            // Keep the current date/time if it's the same doctor
            if (props.appointment?.scheduled_date && !form.scheduled_date) {
                selectedDate.value = new Date(props.appointment.scheduled_date);
                form.scheduled_date = props.appointment.scheduled_date;
            }
            if (props.appointment?.scheduled_time && !form.scheduled_time) {
                selectedTime.value = {
                    time: props.appointment.scheduled_time,
                    display: formatTimeForDisplay(
                        props.appointment.scheduled_time
                    ),
                };
                form.scheduled_time = props.appointment.scheduled_time;
            }
        } else {
            // Clear if doctor changed
            form.scheduled_date = "";
            form.scheduled_time = "";
            selectedDate.value = null;
            selectedTime.value = null;
        }
        getDoctorSchedule();
    }
);

watch(
    () => form.estimated_duration,
    () => {
        if (selectedDate.value) {
            generateTimeSlots(selectedDate.value);
        }
    }
);

// Watch for selected date change to update form
watch(
    () => selectedDate.value,
    (newDate) => {
        if (newDate) {
            form.scheduled_date = newDate.toISOString().split("T")[0];
        }
    }
);

// Watch for selected time change to update form
watch(
    () => selectedTime.value,
    (newTime) => {
        if (newTime) {
            form.scheduled_time = newTime.time;
        }
    }
);
</script>

<template>
    <AppLayout
        :title="isEditing ? 'Edit Appointment' : 'Create Appointment'"
        :subtitle="
            isEditing
                ? 'Update appointment details'
                : 'Schedule a new appointment for a patient'
        "
    >
        <template #headerActions>
            <div class="flex gap-2">
                <SecondaryButton @click="handleCancel">
                    Cancel
                </SecondaryButton>
                <PrimaryButton @click="submitForm" :disabled="form.processing">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else
                        >{{ isEditing ? "Update" : "Create" }} Appointment</span
                    >
                </PrimaryButton>
                <DangerButton v-if="isEditing" @click="showDeleteModal = true">
                    Delete
                </DangerButton>
            </div>
        </template>

        <!-- Appointment Header -->
        <div class="mb-6">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            Appointment #{{ form.appointment_no }}
                        </h2>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="text-sm text-gray-600">
                                Created:
                                {{
                                    new Date(
                                        appointment.created_at
                                    ).toLocaleDateString()
                                }}
                            </span>
                            <span class="text-sm text-gray-600">
                                Last Updated:
                                {{
                                    new Date(
                                        appointment.updated_at
                                    ).toLocaleDateString()
                                }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-sm font-medium',
                                appointment.status === 'completed'
                                    ? 'bg-green-100 text-green-800'
                                    : appointment.status === 'cancelled'
                                    ? 'bg-red-100 text-red-800'
                                    : appointment.status === 'confirmed'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-yellow-100 text-yellow-800',
                            ]"
                        >
                            {{ appointment.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Main Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 space-y-6">
                    <!-- Step 1: Patient Selection -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            1. Patient Information
                        </h3>

                        <div class="space-y-2">
                            <InputLabel
                                for="patient_id"
                                value="Select Patient *"
                            />
                            <SelectInput
                                id="patient_id"
                                v-model="form.patient_id"
                                required
                                class="w-full"
                            >
                                <option value="">Select a patient</option>
                                <option
                                    v-for="patient in patients"
                                    :key="patient.id"
                                    :value="patient.id"
                                >
                                    {{ patient.patient_no }} -
                                    {{ patient.title }}
                                    {{ patient.first_name }}
                                    {{ patient.last_name }}
                                    <template v-if="patient.email">
                                        ({{ patient.email }})
                                    </template>
                                </option>
                            </SelectInput>
                            <span
                                v-if="form.errors.patient_id"
                                class="text-xs text-red-600"
                            >
                                {{ form.errors.patient_id }}
                            </span>
                        </div>
                    </div>

                    <!-- Step 2: Doctor & Appointment Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            2. Appointment Details
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Doctor Selection -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="doctor_id"
                                    value="Select Doctor *"
                                />
                                <SelectInput
                                    id="doctor_id"
                                    v-model="form.doctor_id"
                                    required
                                    class="w-full"
                                >
                                    <option value="">Select a doctor</option>
                                    <option
                                        v-for="doctor in doctors"
                                        :key="doctor.id"
                                        :value="doctor.id"
                                    >
                                        Dr. {{ doctor.first_name }}
                                        {{ doctor.last_name }}
                                        <template
                                            v-if="
                                                doctor.specializations &&
                                                doctor.specializations.length >
                                                    0
                                            "
                                        >
                                            ({{
                                                doctor.specializations
                                                    .map((s) => s.name)
                                                    .join(", ")
                                            }})
                                        </template>
                                    </option>
                                </SelectInput>
                                <span
                                    v-if="form.errors.doctor_id"
                                    class="text-xs text-red-600"
                                >
                                    {{ form.errors.doctor_id }}
                                </span>
                            </div>

                            <!-- Appointment Type -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="appointment_type"
                                    value="Appointment Type *"
                                />
                                <SelectInput
                                    id="appointment_type"
                                    v-model="form.appointment_type"
                                    required
                                    class="w-full"
                                >
                                    <option value="">Select type</option>
                                    <option
                                        v-for="type in appointmentTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{
                                            type.charAt(0).toUpperCase() +
                                            type.slice(1).replace("-", " ")
                                        }}
                                    </option>
                                </SelectInput>
                                <span
                                    v-if="form.errors.appointment_type"
                                    class="text-xs text-red-600"
                                >
                                    {{ form.errors.appointment_type }}
                                </span>
                            </div>

                            <!-- Priority -->
                            <div class="space-y-2">
                                <InputLabel for="priority" value="Priority" />
                                <SelectInput
                                    id="priority"
                                    v-model="form.priority"
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

                            <!-- Duration -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="estimated_duration"
                                    value="Estimated Duration"
                                />
                                <SelectInput
                                    id="estimated_duration"
                                    v-model="form.estimated_duration"
                                    class="w-full"
                                >
                                    <option
                                        v-for="option in durationOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </SelectInput>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Schedule Appointment -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            3. Schedule Appointment
                        </h3>

                        <!-- Doctor Schedule Info -->
                        <div
                            v-if="form.doctor_id && doctorSchedule.length > 0"
                            class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4"
                        >
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-green-600 mt-0.5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                                <div>
                                    <p
                                        class="text-sm font-medium text-green-800"
                                    >
                                        Doctor's Schedule Loaded
                                    </p>
                                    <p class="text-xs text-green-600 mt-1">
                                        Available for next 90 days. Click
                                        "Choose Date & Time" to select.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="
                                form.doctor_id && doctorSchedule.length === 0
                            "
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4"
                        >
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-yellow-600 mt-0.5 mr-2"
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
                                <div>
                                    <p
                                        class="text-sm font-medium text-yellow-800"
                                    >
                                        No schedule available for this doctor
                                    </p>
                                    <p class="text-xs text-yellow-600 mt-1">
                                        Please select another doctor or check
                                        with administration
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Selection Button -->
                        <div class="space-y-2">
                            <InputLabel value="Date & Time *" />
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <div
                                        v-if="
                                            form.scheduled_date &&
                                            form.scheduled_time
                                        "
                                        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-blue-900"
                                                >
                                                    {{
                                                        new Date(
                                                            form.scheduled_date
                                                        ).toLocaleDateString(
                                                            "en-US",
                                                            {
                                                                weekday: "long",
                                                                year: "numeric",
                                                                month: "long",
                                                                day: "numeric",
                                                            }
                                                        )
                                                    }}
                                                </p>
                                                <p
                                                    class="text-sm text-blue-700"
                                                >
                                                    {{
                                                        formatTimeForDisplay(
                                                            form.scheduled_time
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="openCalendarModal"
                                                class="text-sm text-blue-600 hover:text-blue-800"
                                            >
                                                Change
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center"
                                    >
                                        <svg
                                            class="w-12 h-12 mx-auto text-gray-400"
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
                                        <p class="mt-2 text-sm text-gray-600">
                                            No date and time selected
                                        </p>
                                    </div>
                                </div>
                                <PrimaryButton
                                    type="button"
                                    @click="openCalendarModal"
                                    :disabled="!form.doctor_id"
                                >
                                    Choose Date & Time
                                </PrimaryButton>
                            </div>
                            <div
                                v-if="
                                    form.errors.scheduled_date ||
                                    form.errors.scheduled_time
                                "
                                class="text-xs text-red-600"
                            >
                                {{
                                    form.errors.scheduled_date ||
                                    form.errors.scheduled_time
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Reason & Symptoms -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            4. Appointment Details
                        </h3>

                        <div class="space-y-4">
                            <!-- Reason -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="reason"
                                    value="Reason for Visit *"
                                />
                                <textarea
                                    id="reason"
                                    v-model="form.reason"
                                    required
                                    rows="3"
                                    class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    placeholder="Brief description of why the patient needs this appointment..."
                                ></textarea>
                                <span
                                    v-if="form.errors.reason"
                                    class="text-xs text-red-600"
                                >
                                    {{ form.errors.reason }}
                                </span>
                            </div>

                            <!-- Symptoms -->
                            <div class="space-y-2">
                                <InputLabel
                                    for="symptoms"
                                    value="Symptoms (Optional)"
                                />
                                <textarea
                                    id="symptoms"
                                    v-model="form.symptoms"
                                    rows="3"
                                    class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    placeholder="List any symptoms the patient is experiencing..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <InputLabel for="status" value="Appointment Status" />
                        <SelectInput
                            id="status"
                            v-model="form.status"
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

                    <!-- Actual Time Tracking (only for completed appointments) -->
                    <div v-if="form.status === 'completed'" class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            5. Actual Time Tracking
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <InputLabel
                                    for="actual_start_time"
                                    value="Actual Start Time"
                                />
                                <TextInput
                                    id="actual_start_time"
                                    v-model="form.actual_start_time"
                                    type="datetime-local"
                                    class="w-full"
                                />
                            </div>
                            <div class="space-y-2">
                                <InputLabel
                                    for="actual_end_time"
                                    value="Actual End Time"
                                />
                                <TextInput
                                    id="actual_end_time"
                                    v-model="form.actual_end_time"
                                    type="datetime-local"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span class="text-red-600">*</span> Required fields
                        </div>
                        <div class="flex gap-3">
                            <SecondaryButton type="button" @click="resetForm">
                                Reset Changes
                            </SecondaryButton>
                            <SecondaryButton @click="handleCancel">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                @click="submitForm"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Update Appointment</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Appointment Summary
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Left Column -->
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="w-32 text-sm text-gray-500">
                                    Appointment #:
                                </div>
                                <div class="font-medium">
                                    {{ form.appointment_no }}
                                </div>
                            </div>

                            <div
                                class="flex items-center"
                                v-if="form.patient_id"
                            >
                                <div class="w-32 text-sm text-gray-500">
                                    Patient:
                                </div>
                                <div class="font-medium">
                                    {{
                                        patients.find(
                                            (p) => p.id == form.patient_id
                                        )?.first_name
                                    }}
                                    {{
                                        patients.find(
                                            (p) => p.id == form.patient_id
                                        )?.last_name
                                    }}
                                </div>
                            </div>

                            <div
                                class="flex items-center"
                                v-if="form.doctor_id"
                            >
                                <div class="w-32 text-sm text-gray-500">
                                    Doctor:
                                </div>
                                <div class="font-medium">
                                    Dr.
                                    {{
                                        doctors.find(
                                            (d) => d.id == form.doctor_id
                                        )?.first_name
                                    }}
                                    {{
                                        doctors.find(
                                            (d) => d.id == form.doctor_id
                                        )?.last_name
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-3">
                            <div
                                class="flex items-center"
                                v-if="form.scheduled_date"
                            >
                                <div class="w-32 text-sm text-gray-500">
                                    Date:
                                </div>
                                <div class="font-medium">
                                    {{
                                        new Date(
                                            form.scheduled_date
                                        ).toLocaleDateString("en-US", {
                                            weekday: "long",
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                        })
                                    }}
                                </div>
                            </div>

                            <div
                                class="flex items-center"
                                v-if="form.scheduled_time"
                            >
                                <div class="w-32 text-sm text-gray-500">
                                    Time:
                                </div>
                                <div class="font-medium">
                                    {{
                                        formatTimeForDisplay(
                                            form.scheduled_time
                                        )
                                    }}
                                </div>
                            </div>

                            <div
                                class="flex items-center"
                                v-if="form.appointment_type"
                            >
                                <div class="w-32 text-sm text-gray-500">
                                    Type:
                                </div>
                                <div class="font-medium">
                                    {{
                                        form.appointment_type
                                            .charAt(0)
                                            .toUpperCase() +
                                        form.appointment_type
                                            .slice(1)
                                            .replace("-", " ")
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div
                        class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <div v-if="form.priority">
                            <div class="text-sm text-gray-500 mb-1">
                                Priority:
                            </div>
                            <div
                                :class="{
                                    'text-green-600 font-medium':
                                        form.priority === 'low',
                                    'text-blue-600 font-medium':
                                        form.priority === 'normal',
                                    'text-orange-600 font-medium':
                                        form.priority === 'high',
                                    'text-red-600 font-medium':
                                        form.priority === 'urgent',
                                }"
                            >
                                {{
                                    priorityOptions.find(
                                        (p) => p.value === form.priority
                                    )?.label
                                }}
                            </div>
                        </div>

                        <div v-if="form.estimated_duration">
                            <div class="text-sm text-gray-500 mb-1">
                                Duration:
                            </div>
                            <div class="font-medium">
                                {{
                                    durationOptions.find(
                                        (d) =>
                                            d.value === form.estimated_duration
                                    )?.label
                                }}
                            </div>
                        </div>

                        <div v-if="form.status">
                            <div class="text-sm text-gray-500 mb-1">
                                Status:
                            </div>
                            <div class="font-medium">
                                {{
                                    statusOptions.find(
                                        (s) => s.value === form.status
                                    )?.label
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Reason Preview -->
                    <div
                        class="mt-4 pt-4 border-t border-gray-200"
                        v-if="form.reason"
                    >
                        <div class="text-sm text-gray-500 mb-1">Reason:</div>
                        <div class="text-gray-700">{{ form.reason }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Modal -->
        <Modal
            :show="showCalendarModal"
            @close="showCalendarModal = false"
            max-width="5xl"
        >
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">
                        Select Appointment Date & Time
                    </h3>
                    <button
                        @click="showCalendarModal = false"
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Calendar Section -->
                    <div>
                        <div
                            class="bg-white rounded-lg border border-gray-200 p-4"
                        >
                            <!-- Calendar Header -->
                            <div class="flex items-center justify-between mb-4">
                                <button
                                    @click="prevMonth"
                                    class="p-2 rounded-lg hover:bg-gray-100"
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
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                </button>
                                <h4 class="text-lg font-semibold">
                                    {{ monthName }} {{ currentYear }}
                                </h4>
                                <button
                                    @click="nextMonth"
                                    class="p-2 rounded-lg hover:bg-gray-100"
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
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Week Days Header -->
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Sun
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Mon
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Tue
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Wed
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Thu
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Fri
                                </div>
                                <div
                                    class="text-center text-xs font-medium text-gray-500 py-1"
                                >
                                    Sat
                                </div>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-1">
                                <button
                                    v-for="(day, index) in calendarDays"
                                    :key="index"
                                    @click="selectDate(day)"
                                    :disabled="!day.isAvailable"
                                    :class="[
                                        'py-2 rounded-lg text-center text-sm transition-colors',
                                        day.isCurrentMonth
                                            ? 'text-gray-900'
                                            : 'text-gray-400',
                                        day.isToday ? 'font-bold' : '',
                                        day.isSelected
                                            ? 'bg-blue-100 border-2 border-blue-500'
                                            : day.isAvailable
                                            ? 'hover:bg-gray-100'
                                            : '',
                                        !day.isAvailable
                                            ? 'opacity-50 cursor-not-allowed'
                                            : 'cursor-pointer',
                                    ]"
                                >
                                    {{ day.day }}
                                    <div
                                        v-if="day.isAvailable"
                                        class="w-1 h-1 mx-auto mt-1 rounded-full bg-green-500"
                                    ></div>
                                </button>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div
                            class="mt-4 flex items-center justify-center space-x-4 text-xs"
                        >
                            <div class="flex items-center">
                                <div
                                    class="w-3 h-3 rounded-full bg-green-500 mr-1"
                                ></div>
                                <span class="text-gray-600">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div
                                    class="w-3 h-3 rounded-full bg-gray-300 mr-1"
                                ></div>
                                <span class="text-gray-600">Unavailable</span>
                            </div>
                        </div>
                    </div>

                    <!-- Time Slots Section -->
                    <div>
                        <div
                            class="bg-white rounded-lg border border-gray-200 p-4 h-full"
                        >
                            <!-- Selected Date -->
                            <div v-if="selectedDate" class="mb-6">
                                <h4
                                    class="text-lg font-semibold text-gray-900 mb-2"
                                >
                                    {{
                                        selectedDate.toLocaleDateString(
                                            "en-US",
                                            {
                                                weekday: "long",
                                                year: "numeric",
                                                month: "long",
                                                day: "numeric",
                                            }
                                        )
                                    }}
                                </h4>
                                <div class="text-sm text-gray-600">
                                    Select a time slot ({{
                                        form.estimated_duration
                                    }}
                                    minute intervals)
                                </div>
                            </div>

                            <!-- Available Time Slots -->
                            <div
                                v-if="selectedDate && availableSlots.length > 0"
                            >
                                <div
                                    class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[400px] overflow-y-auto"
                                >
                                    <button
                                        v-for="slot in availableSlots"
                                        :key="slot.time"
                                        @click="selectTime(slot)"
                                        :disabled="
                                            slot.isBooked &&
                                            !slot.isCurrentAppointment
                                        "
                                        :class="[
                                            'p-3 rounded-lg border text-center transition-colors relative',
                                            selectedTime?.time === slot.time
                                                ? 'bg-blue-50 border-blue-500 text-blue-700'
                                                : slot.isCurrentAppointment
                                                ? 'bg-purple-50 border-purple-300 text-purple-700'
                                                : slot.isBooked
                                                ? 'bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed opacity-60'
                                                : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100',
                                        ]"
                                        :title="
                                            slot.isBooked
                                                ? slot.isCurrentAppointment
                                                    ? 'Current appointment time'
                                                    : 'Already booked'
                                                : 'Available'
                                        "
                                    >
                                        <div class="font-medium">
                                            {{ slot.display }}
                                        </div>
                                        <div
                                            v-if="slot.isCurrentAppointment"
                                            class="absolute top-1 right-1 w-2 h-2 bg-purple-500 rounded-full"
                                        ></div>
                                        <div
                                            v-else-if="slot.isBooked"
                                            class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                                        ></div>
                                        <div
                                            v-if="slot.isCurrentAppointment"
                                            class="text-xs text-purple-500 mt-1"
                                        >
                                            Current
                                        </div>
                                        <div
                                            v-else-if="slot.isBooked"
                                            class="text-xs text-red-500 mt-1"
                                        >
                                            Booked
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- No Slots Available -->
                            <div
                                v-else-if="
                                    selectedDate && availableSlots.length === 0
                                "
                                class="text-center py-8"
                            >
                                <svg
                                    class="w-12 h-12 mx-auto text-gray-400"
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
                                <p class="mt-2 text-gray-600">
                                    No available time slots for this date
                                </p>
                                <p class="text-sm text-gray-500">
                                    Try selecting a different date
                                </p>
                            </div>

                            <!-- No Date Selected -->
                            <div v-else class="text-center py-8">
                                <svg
                                    class="w-12 h-12 mx-auto text-gray-400"
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
                                <p class="mt-2 text-gray-600">
                                    Select a date from the calendar
                                </p>
                            </div>

                            <!-- Selected Time Display -->
                            <div
                                v-if="selectedTime"
                                class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-green-900">
                                            Selected Time:
                                        </p>
                                        <p
                                            class="text-lg font-semibold text-green-700"
                                        >
                                            {{ selectedTime.display }}
                                        </p>
                                    </div>
                                    <button
                                        @click="selectedTime = null"
                                        class="text-sm text-green-600 hover:text-green-800"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex justify-end space-x-3">
                                <SecondaryButton
                                    @click="showCalendarModal = false"
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton
                                    @click="confirmSchedule"
                                    :disabled="!selectedDate || !selectedTime"
                                >
                                    Confirm Selection
                                </PrimaryButton>
                            </div>
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
                            <span class="font-semibold"
                                >#{{ appointment.appointment_no }}</span
                            >?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All appointment data
                            will be permanently removed.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="confirmDelete">
                        Delete Appointment
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for time slots */
.max-h-\[400px\]::-webkit-scrollbar {
    width: 6px;
}

.max-h-\[400px\]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
