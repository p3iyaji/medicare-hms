<!-- resources/js/Components/DateRangePicker.vue -->
<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: "Select date range",
    },
});

const emit = defineEmits(["update:modelValue"]);

const showCalendar = ref(false);
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDates = ref({ start: null, end: null });
const tempSelection = ref({ start: null, end: null });

// Close calendar when clicking outside
const handleClickOutside = (event) => {
    const calendar = event.target.closest(".relative");
    if (!calendar) {
        showCalendar.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    // Initialize from modelValue if exists
    if (props.modelValue) {
        const dates = props.modelValue.split(" to ");
        if (dates.length === 2) {
            selectedDates.value.start = new Date(dates[0].trim());
            selectedDates.value.end = new Date(dates[1].trim());
        }
    }
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

const displayValue = computed(() => {
    if (!selectedDates.value.start && !selectedDates.value.end) {
        return "";
    }

    const format = (date) => {
        return date.toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    };

    if (selectedDates.value.start && selectedDates.value.end) {
        return `${format(selectedDates.value.start)} to ${format(
            selectedDates.value.end
        )}`;
    }

    return selectedDates.value.start ? format(selectedDates.value.start) : "";
});

const currentMonthName = computed(() => {
    return new Date(currentYear.value, currentMonth.value).toLocaleDateString(
        "en-US",
        {
            month: "long",
        }
    );
});

const dates = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const daysInMonth = lastDay.getDate();

    // Get day of week for first day (0 = Sunday, 1 = Monday, etc.)
    const firstDayIndex = firstDay.getDay();

    const datesArray = [];

    // Previous month's days
    const prevMonthLastDay = new Date(
        currentYear.value,
        currentMonth.value,
        0
    ).getDate();
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        const date = new Date(
            currentYear.value,
            currentMonth.value - 1,
            prevMonthLastDay - i
        );
        datesArray.push({
            date: date.toISOString(),
            day: date.getDate(),
            isCurrentMonth: false,
            isToday: isToday(date),
            isInRange: isInRange(date),
        });
    }

    // Current month's days
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(currentYear.value, currentMonth.value, i);
        datesArray.push({
            date: date.toISOString(),
            day: date.getDate(),
            isCurrentMonth: true,
            isToday: isToday(date),
            isInRange: isInRange(date),
        });
    }

    // Next month's days
    const totalCells = 42; // 6 weeks * 7 days
    const nextMonthDays = totalCells - datesArray.length;
    for (let i = 1; i <= nextMonthDays; i++) {
        const date = new Date(currentYear.value, currentMonth.value + 1, i);
        datesArray.push({
            date: date.toISOString(),
            day: date.getDate(),
            isCurrentMonth: false,
            isToday: isToday(date),
            isInRange: isInRange(date),
        });
    }

    return datesArray;
});

function isToday(date) {
    const today = new Date();
    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
}

function isSelectedDate(dateObj) {
    const date = new Date(dateObj.date);
    return (
        (tempSelection.value.start &&
            isSameDay(date, tempSelection.value.start)) ||
        (tempSelection.value.end && isSameDay(date, tempSelection.value.end))
    );
}

function isInRange(date) {
    if (!tempSelection.value.start || !tempSelection.value.end) return false;

    const start = new Date(tempSelection.value.start);
    const end = new Date(tempSelection.value.end);

    // Ensure start is before end
    const rangeStart = start < end ? start : end;
    const rangeEnd = start < end ? end : start;

    return date >= rangeStart && date <= rangeEnd;
}

function isSameDay(date1, date2) {
    return (
        date1.getDate() === date2.getDate() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getFullYear() === date2.getFullYear()
    );
}

function selectDate(dateObj) {
    const date = new Date(dateObj.date);

    if (
        !tempSelection.value.start ||
        (tempSelection.value.start && tempSelection.value.end)
    ) {
        // Start new selection
        tempSelection.value = { start: date, end: null };
    } else if (!tempSelection.value.end) {
        // Complete the selection
        if (date < tempSelection.value.start) {
            tempSelection.value = {
                start: date,
                end: tempSelection.value.start,
            };
        } else {
            tempSelection.value.end = date;
        }
    }
}

function prevMonth() {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value -= 1;
    } else {
        currentMonth.value -= 1;
    }
}

function nextMonth() {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value += 1;
    } else {
        currentMonth.value += 1;
    }
}

function setRange(rangeType) {
    const today = new Date();
    let start, end;

    switch (rangeType) {
        case "today":
            start = new Date(today);
            end = new Date(today);
            break;
        case "yesterday":
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            start = yesterday;
            end = yesterday;
            break;
        case "this_week":
            const firstDayOfWeek = new Date(today);
            firstDayOfWeek.setDate(today.getDate() - today.getDay());
            const lastDayOfWeek = new Date(today);
            lastDayOfWeek.setDate(today.getDate() + (6 - today.getDay()));
            start = firstDayOfWeek;
            end = lastDayOfWeek;
            break;
        case "last_week":
            const lastWeek = new Date(today);
            lastWeek.setDate(today.getDate() - 7);
            const firstDayLastWeek = new Date(lastWeek);
            firstDayLastWeek.setDate(lastWeek.getDate() - lastWeek.getDay());
            const lastDayLastWeek = new Date(lastWeek);
            lastDayLastWeek.setDate(
                lastWeek.getDate() + (6 - lastWeek.getDay())
            );
            start = firstDayLastWeek;
            end = lastDayLastWeek;
            break;
        case "this_month":
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case "last_month":
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case "last_30_days":
            start = new Date(today);
            start.setDate(today.getDate() - 29); // 30 days including today
            end = today;
            break;
    }

    tempSelection.value = { start, end };
    applySelection();
}

function clearDates() {
    tempSelection.value = { start: null, end: null };
    selectedDates.value = { start: null, end: null };
    emit("update:modelValue", "");
    showCalendar.value = false;
}

function cancelSelection() {
    tempSelection.value = { ...selectedDates.value };
    showCalendar.value = false;
}

function applySelection() {
    if (tempSelection.value.start && tempSelection.value.end) {
        selectedDates.value = { ...tempSelection.value };

        // Format dates for output
        const formatForOutput = (date) => {
            return date.toISOString().split("T")[0]; // YYYY-MM-DD format
        };

        const output = `${formatForOutput(
            selectedDates.value.start
        )} to ${formatForOutput(selectedDates.value.end)}`;
        emit("update:modelValue", output);
        showCalendar.value = false;
    }
}
</script>

<template>
    <div class="relative">
        <!-- Input Display -->
        <div @click="showCalendar = !showCalendar" class="cursor-pointer">
            <TextInput
                :modelValue="displayValue"
                readonly
                :placeholder="placeholder"
                class="cursor-pointer"
            >
                <template #suffix>
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
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </template>
            </TextInput>
        </div>

        <!-- Calendar Dropdown -->
        <div
            v-show="showCalendar"
            class="absolute z-50 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 p-4 w-64"
        >
            <div class="flex justify-between items-center mb-4">
                <button
                    @click="prevMonth"
                    class="p-1 hover:bg-gray-100 rounded"
                >
                    <svg
                        class="w-5 h-5 text-gray-600"
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
                <div class="font-semibold text-gray-700">
                    {{ currentMonthName }} {{ currentYear }}
                </div>
                <button
                    @click="nextMonth"
                    class="p-1 hover:bg-gray-100 rounded"
                >
                    <svg
                        class="w-5 h-5 text-gray-600"
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

            <!-- Date Selection -->
            <div class="grid grid-cols-7 gap-1 mb-4">
                <div
                    v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']"
                    :key="day"
                    class="text-center text-xs font-medium text-gray-500 py-1"
                >
                    {{ day }}
                </div>
                <div
                    v-for="date in dates"
                    :key="date.date"
                    @click="selectDate(date)"
                    :class="[
                        'text-center p-2 text-sm rounded cursor-pointer hover:bg-gray-100',
                        date.isCurrentMonth ? 'text-gray-700' : 'text-gray-400',
                        date.isToday ? 'font-semibold' : '',
                        isSelectedDate(date)
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : '',
                        date.isInRange ? 'bg-blue-100' : '',
                    ]"
                >
                    {{ date.day }}
                </div>
            </div>

            <!-- Quick Range Selection -->
            <div class="space-y-2">
                <div class="text-xs font-medium text-gray-700 mb-2">
                    Quick Ranges:
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        @click="setRange('today')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Today
                    </button>
                    <button
                        @click="setRange('yesterday')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Yesterday
                    </button>
                    <button
                        @click="setRange('this_week')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        This Week
                    </button>
                    <button
                        @click="setRange('last_week')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Last Week
                    </button>
                    <button
                        @click="setRange('this_month')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        This Month
                    </button>
                    <button
                        @click="setRange('last_month')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Last Month
                    </button>
                    <button
                        @click="setRange('last_30_days')"
                        class="text-xs px-3 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Last 30 Days
                    </button>
                    <button
                        @click="clearDates"
                        class="text-xs px-3 py-2 border border-red-300 text-red-600 rounded hover:bg-red-50"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Apply/Cancel Buttons -->
            <div
                class="flex justify-between mt-4 pt-4 border-t border-gray-200"
            >
                <button
                    @click="cancelSelection"
                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800"
                >
                    Cancel
                </button>
                <button
                    @click="applySelection"
                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Apply
                </button>
            </div>
        </div>
    </div>
</template>
