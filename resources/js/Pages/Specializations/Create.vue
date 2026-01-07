<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";

const page = usePage();

const props = defineProps({
    specialization: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => props.specialization !== null);
const pageTitle = computed(() =>
    isEditing.value ? "Edit Specialization" : "Create New Specialization"
);
const pageSubtitle = computed(() =>
    isEditing.value
        ? "Update specialization information"
        : "Register a new specialization"
);

const form = useForm({
    name: props.specialization?.name || "",
    description: props.specialization?.description || "",
});

// Form submission
const submitForm = () => {
    if (isEditing.value) {
        form.put("/specializations/update/" + props.specialization.id, {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            },
        });
    } else {
        form.post("/specializations/store", {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            },
        });
    }
};

// Cancel handler
const handleCancel = () => {
    router.visit("/specializations");
};

// Delete confirmation
const showDeleteModal = ref(false);
const confirmDelete = () => {
    if (props.specialization) {
        router.delete("/specializations/destroy/" + props.specialization.id, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                router.visit("/specializations");
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="pageTitle" :subtitle="pageSubtitle">
        <template #headerActions>
            <div class="flex flex-wrap gap-2">
                <SecondaryButton
                    @click="handleCancel"
                    class="flex items-center justify-center w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2"
                >
                    <span class="hidden sm:inline">Cancel</span>
                    <span class="sm:hidden">←</span>
                </SecondaryButton>
                <PrimaryButton
                    @click="submitForm"
                    :disabled="form.processing"
                    class="flex items-center justify-center w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2"
                >
                    <span v-if="form.processing" class="hidden sm:inline"
                        >Saving...</span
                    >
                    <span v-else class="hidden sm:inline">
                        {{ isEditing ? "Update" : "Create" }}
                    </span>
                    <span class="sm:hidden">✓</span>
                </PrimaryButton>
                <DangerButton
                    v-if="isEditing"
                    @click="showDeleteModal = true"
                    class="flex items-center justify-center w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2 ml-2"
                >
                    <span class="hidden sm:inline">Delete</span>
                    <span class="sm:hidden">🗑️</span>
                </DangerButton>
            </div>
        </template>

        <!-- Main Form Container -->
        <div class="max-w-4xl mx-auto">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 sm:mb-0"
            >
                <!-- Specialization Header -->
                <div
                    v-if="isEditing"
                    class="px-4 sm:px-6 py-3 border-b border-gray-200 bg-blue-50"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
                    >
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">
                                Updating.... {{ specialization.name }}
                            </h3>
                        </div>
                        <div class="text-xs text-gray-600">
                            Created
                            {{
                                new Date(
                                    specialization.created_at
                                ).toLocaleDateString()
                            }}
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-4 sm:p-6 space-y-6">
                    <!-- Form Section -->
                    <div class="space-y-6">
                        <!-- Name Field -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <InputLabel
                                    for="name"
                                    value="Specialization Name"
                                    class="text-sm font-medium text-gray-900"
                                />
                                <span class="text-xs text-red-600"
                                    >* Required</span
                                >
                            </div>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g., Cardiology, Pediatrics"
                                class="w-full text-sm sm:text-base"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                            <p v-else class="mt-1 text-xs text-gray-500">
                                Enter the name of the medical specialization
                            </p>
                        </div>

                        <!-- Description Field -->
                        <div>
                            <InputLabel
                                for="description"
                                value="Description"
                                class="text-sm font-medium text-gray-900 mb-2"
                            />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Enter a brief description of this specialization..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base resize-y min-h-[100px]"
                                :class="{
                                    'border-red-500': form.errors.description,
                                }"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                            <p v-else class="mt-1 text-xs text-gray-500">
                                Optional: Provide details about this
                                specialization
                            </p>
                        </div>

                        <!-- Form Status -->
                        <div
                            v-if="form.recentlySuccessful || form.hasErrors"
                            class="p-3 rounded-md text-sm"
                            :class="{
                                'bg-green-50 text-green-800 border border-green-200':
                                    form.recentlySuccessful,
                                'bg-red-50 text-red-800 border border-red-200':
                                    form.hasErrors,
                            }"
                        >
                            <div class="flex items-start">
                                <svg
                                    v-if="form.recentlySuccessful"
                                    class="w-5 h-5 mr-2 text-green-600 flex-shrink-0 mt-0.5"
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
                                <svg
                                    v-if="form.hasErrors"
                                    class="w-5 h-5 mr-2 text-red-600 flex-shrink-0 mt-0.5"
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
                                    <span v-if="form.recentlySuccessful">
                                        Specialization
                                        {{ isEditing ? "updated" : "created" }}
                                        successfully!
                                    </span>
                                    <span v-else-if="form.hasErrors">
                                        Please check the form for errors.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Form Actions Footer -->
                <div
                    class="hidden sm:block px-6 py-4 bg-gray-50 border-t border-gray-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span v-if="isEditing">
                                Last updated:
                                {{
                                    new Date(
                                        specialization.updated_at
                                    ).toLocaleDateString()
                                }}
                            </span>
                            <span v-else>
                                <span class="text-red-600">*</span> Required
                                fields
                            </span>
                        </div>
                        <div class="flex space-x-3">
                            <SecondaryButton @click="handleCancel">
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                @click="submitForm"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>
                                    {{ isEditing ? "Update" : "Create" }}
                                    Specialization
                                </span>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Form Actions Footer -->
            <div
                class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-10 shadow-lg"
            >
                <div class="flex space-x-3">
                    <SecondaryButton @click="handleCancel" class="flex-1">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="submitForm"
                        :disabled="form.processing"
                        class="flex-1"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>
                            {{ isEditing ? "Update" : "Save" }}
                        </span>
                    </PrimaryButton>
                </div>
            </div>

            <!-- Spacer for mobile fixed footer -->
            <div class="h-20 sm:h-0"></div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-4 sm:p-6">
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
                        Delete Specialization
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete the
                            <span class="font-semibold text-gray-900">
                                "{{ specialization?.name }}"
                            </span>
                            specialization?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All data associated
                            with this specialization will be permanently
                            removed.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-3"
                >
                    <SecondaryButton
                        @click="showDeleteModal = false"
                        class="w-full sm:w-auto order-2 sm:order-1"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        class="w-full sm:w-auto order-1 sm:order-2"
                    >
                        Delete Specialization
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Mobile-specific optimizations */
@media (max-width: 640px) {
    /* Improve touch targets */
    :deep(button),
    :deep(input[type="submit"]),
    :deep(input[type="button"]) {
        min-height: 44px;
        min-width: 44px;
    }

    /* Prevent iOS zoom on focus */
    :deep(input[type="text"]),
    :deep(input[type="email"]),
    :deep(input[type="tel"]),
    :deep(input[type="number"]),
    :deep(textarea),
    :deep(select) {
        font-size: 16px;
    }

    /* Better spacing for mobile */
    :deep(.space-y-6 > * + *) {
        margin-top: 1.5rem;
    }

    /* Adjust form labels for mobile */
    :deep(.text-sm) {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    /* Improve textarea on mobile */
    :deep(textarea) {
        line-height: 1.5;
    }
}

/* Tablet optimizations */
@media (min-width: 641px) and (max-width: 1024px) {
    :deep(.max-w-4xl) {
        max-width: 100%;
        padding: 0 1rem;
    }
}

/* Hide scrollbar but keep functionality */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Smooth transitions */
:deep(.transition-all) {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus styles for better accessibility */
:deep(:focus-visible) {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
