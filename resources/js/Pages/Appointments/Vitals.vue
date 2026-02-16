<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    appointment: Object,
    vitals: Array,
});
</script>

<template>
    <AppLayout
        :title="`Vital Signs - ${appointment.patient.first_name} ${appointment.patient.last_name}`"
    >
        <!-- Your vitals display component here -->
    </AppLayout>
</template>
