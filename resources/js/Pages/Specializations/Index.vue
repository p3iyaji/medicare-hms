<script setup>
import { router, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, watch } from "vue";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    specializations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
        }),
    },
});

const page = usePage();
const auth = computed(() => page.props.auth || { user: null });

const welcomeMessage = computed(() => {
    return "Manage specialization records and accounts";
});

const createSpecialization = () => {
    router.visit("/specializations/create");
};

// search and filters
const search = ref(props.filters.search || "");

// Modal state
const showDeleteModal = ref(false);
const selectedSpecialization = ref(null);

// Processing state
const processingAction = ref(false);

// computed property for active filters
const hasActiveFilters = computed(() => {
    return search.value;
});

// Apply filters with debounce
let filterTimeout = null;
const applyFilters = () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }

    filterTimeout = setTimeout(() => {
        router.get(
            "/specializations",
            {
                search: search.value,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                only: ["specializations", "filters"],
            }
        );
    }, 300); // 300ms debounce
};

//Filter options
watch(
    [search],
    () => {
        applyFilters();
    },
    { deep: true }
);

const clearFilters = () => {
    search.value = "";
};

const editSpecialization = (specialization) => {
    router.visit(`/specializations/edit/${specialization.id}`);
};
// Action handlers
const handleDelete = (specialization) => {
    selectedSpecialization.value = specialization;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!selectedSpecialization.value) return;

    processingAction.value = true;
    try {
        await router.delete(
            `/specializations/destroy/${selectedSpecialization.value.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteModal.value = false;
                    selectedSpecialization.value = null;
                },
                onFinish: () => {
                    processingAction.value = false;
                },
            }
        );
    } catch (error) {
        processingAction.value = false;
    }
};
</script>

<template>
    <AppLayout title="Specializations Management" :subtitle="welcomeMessage">
        <template #headerActions>
            <PrimaryButton
                v-if="
                    auth.user?.user_type === 'admin' ||
                    auth.user?.user_type === 'doctor' ||
                    auth.user?.user_type === 'nurse'
                "
                @click="createSpecialization"
                class="w-full sm:w-auto"
            >
                <span class="hidden sm:inline">Add New Specialization</span>
                <span class="sm:hidden">Add New</span>
            </PrimaryButton>
        </template>

        <!-- Search and Filters Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-4">
                    <!-- Search -->
                    <div class="w-full">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <TextInput
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Search specializations..."
                                class="w-full pl-10"
                            />
                        </div>
                    </div>

                    <!-- Filters Actions -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="text-sm text-gray-600">
                            Showing {{ specializations.from || 0 }} to
                            {{ specializations.to || 0 }} of
                            {{ specializations.total || 0 }} specializations
                        </div>

                        <!-- Clear filters -->
                        <div v-if="hasActiveFilters">
                            <SecondaryButton
                                @click="clearFilters"
                                class="w-full sm:w-auto text-sm text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap"
                            >
                                <span class="hidden sm:inline"
                                    >Clear all filters</span
                                >
                                <span class="sm:hidden">Clear Filters</span>
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <div
                        v-if="hasActiveFilters"
                        class="pt-4 border-t border-gray-200"
                    >
                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-2"
                        >
                            <span
                                class="text-sm font-medium text-gray-700 whitespace-nowrap"
                                >Active filters:</span
                            >
                            <div class="flex flex-wrap gap-2">
                                <!-- Search Filter Badge -->
                                <div
                                    v-if="search"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-blue-100 text-blue-800"
                                >
                                    <span class="truncate max-w-[150px]"
                                        >"{{ search }}"</span
                                    >
                                    <button
                                        @click="search = ''"
                                        class="ml-1 text-blue-600 hover:text-blue-800"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Specializations Cards/Table -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-500">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
                >
                    <h3 class="text-lg font-medium text-white">
                        Specializations
                    </h3>
                    <div class="text-sm text-blue-100">
                        Total: {{ specializations.total || 0 }}
                    </div>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="sm:hidden">
                <div
                    v-if="specializations.data?.length === 0"
                    class="text-center py-12"
                >
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
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No specializations found
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            search
                                ? "Try adjusting your search"
                                : "Get started by adding a new specialization."
                        }}
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="specialization in specializations.data"
                        :key="specialization.id"
                        class="p-4 hover:bg-gray-50"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <h4
                                    class="text-sm font-semibold text-gray-900 truncate"
                                >
                                    {{ specialization.name }}
                                </h4>
                                <p
                                    class="mt-1 text-sm text-gray-600 line-clamp-2"
                                >
                                    {{
                                        specialization.description ||
                                        "No description"
                                    }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <div class="flex items-center space-x-2">
                                    <template
                                        v-if="
                                            auth.user?.user_type === 'admin' ||
                                            auth.user?.user_type === 'doctor'
                                        "
                                    >
                                        <button
                                            @click="
                                                handleDelete(specialization)
                                            "
                                            class="text-red-600 hover:text-red-900 p-1"
                                            title="Delete"
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
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Specialization
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Description
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="specialization in specializations"
                            :key="specialization.id"
                            class="hover:bg-gray-50"
                        >
                            <!-- Specialization Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ specialization.name }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="px-6 py-4">
                                <div
                                    class="text-sm text-gray-900 max-w-md truncate"
                                >
                                    {{
                                        specialization.description ||
                                        "No description"
                                    }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                            >
                                <div class="flex items-center space-x-2">
                                    <template
                                        v-if="
                                            auth.user?.user_type === 'admin' ||
                                            auth.user?.user_type === 'doctor'
                                        "
                                    >
                                        <button
                                            @click="
                                                editSpecialization(
                                                    specialization
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded-md hover:bg-blue-100 tranistion-colors"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="
                                                handleDelete(specialization)
                                            "
                                            class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md hover:bg-red-100 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State for Desktop -->
            <div
                v-if="specializations.data?.length === 0"
                class="hidden sm:block text-center py-12"
            >
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
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No specializations found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        search
                            ? "Try adjusting your search"
                            : "Get started by adding a new specialization."
                    }}
                </p>
                <div
                    v-if="
                        auth.user?.user_type === 'admin' ||
                        auth.user?.user_type === 'doctor'
                    "
                    class="mt-6"
                >
                    <PrimaryButton @click="createSpecialization">
                        Add New Specialization
                    </PrimaryButton>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="specializations.links?.length > 3"
                class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div class="text-sm text-gray-700 text-center sm:text-left">
                        Page {{ specializations.current_page }} of
                        {{ specializations.last_page }}
                    </div>
                    <div class="flex flex-wrap justify-center gap-1 sm:gap-2">
                        <template
                            v-for="(link, index) in specializations.links"
                            :key="index"
                        >
                            <a
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1.5 sm:py-1 rounded-md text-sm font-medium min-w-[40px] text-center',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'text-gray-700 hover:bg-gray-100',
                                ]"
                                v-html="link.label"
                            >
                            </a>
                            <span
                                v-else
                                class="px-3 py-1.5 sm:py-1 text-gray-500 text-center"
                                v-html="link.label"
                            >
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

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
                        Delete Specialization
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to delete
                            <span class="font-semibold">{{
                                selectedSpecialization?.name
                            }}</span
                            >?
                        </p>
                        <p class="text-sm text-red-600 font-medium mt-2">
                            This action cannot be undone. All specialization
                            data will be permanently removed.
                        </p>
                    </div>
                </div>
                <div
                    class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-3 sm:gap-0"
                >
                    <SecondaryButton
                        @click="showDeleteModal = false"
                        class="w-full sm:w-auto"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        :disabled="processingAction"
                        class="w-full sm:w-auto"
                    >
                        <span v-if="processingAction">Processing...</span>
                        <span v-else>Delete Specialization</span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Add line clamping for description in mobile view */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive table cell truncation */
@media (max-width: 640px) {
    .max-w-md {
        max-width: 100%;
    }
}
</style>
