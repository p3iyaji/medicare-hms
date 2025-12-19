<script setup>
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true
    },
   
});

const emit = defineEmits(['updated']);

const isEditing = ref(false);

// Initialize form with profile data
const form = useForm({
    emergency_contact_name: props.profile.emergency_contact_name || '',
    emergency_contact_number: props.profile.emergency_contact_number || '',
    emergency_contact_relationship: props.profile.emergency_contact_relationship || '',
    
});

// Start editing
const startEditing = () => {
    isEditing.value = true;
    // Reset form with current profile data
    form.emergency_contact_name = props.profile.emergency_contact_name || '';
    form.emergency_contact_number = props.profile.emergency_contact_number || '';
    form.emergency_contact_relationship = props.profile.emergency_contact_relationship || '';
    form.clearErrors();
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    form.clearErrors();
};

// Submit form
const submitForm = () => {
    
    form.put('emergencycontact-update', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditing.value = false;
            emit('updated');
            
            // Reload page to get updated data
            router.reload({
                only: ['profile'],
                preserveScroll: true,
                preserveState: true
            });
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
        }
    });
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Emergency Contact</h3>
            <div class="flex items-center space-x-2">
                <button
                    v-if="!isEditing"
                    @click="startEditing"
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Emergency contact
                </button>
                <button
                    v-if="isEditing"
                    @click="cancelEditing"
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out"
                >
                    Cancel
                </button>
            </div>
        </div>
        
        <form v-if="isEditing" @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- contact name -->
                    <div>
                        <InputLabel for="emergency_contact_name" value="Emergency contact name" />
                      
                        <TextInput
                                type="text"
                                id="emergency_contact_name"
                                v-model="form.emergency_contact_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.emergency_contact_name }"
                            />
                        <p v-if="form.errors.emergency_contact_name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.emergency_contact_name }}
                        </p>
                    </div>
                    
                    <!-- Height and Weight -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="emergency_contact_number" value="Emergency contact number" />
                            
                            <TextInput
                                type="text"
                                id="emergency_contact_number"
                                v-model="form.emergency_contact_number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.emergency_contact_number }"
                            />
                            <p v-if="form.errors.emergency_contact_number" class="mt-1 text-sm text-red-600">
                                {{ form.errors.emergency_contact_number }}
                            </p>
                        </div>
                        
                    </div>
                                        
                </div>
                
                <!-- Right Column -->
                <div class="space-y-6">
                        <div>
                            <InputLabel for="emergency_contact_relationship" value="Emergency contact relationship" />
                               
                            <TextInput
                                type="text"
                                id="emergency_contact_relationship"
                                v-model="form.emergency_contact_relationship"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.emergency_contact_relationship }"
                            />
                            <p v-if="form.errors.emergency_contact_relationship" class="mt-1 text-sm text-red-600">
                                {{ form.errors.emergency_contact_relationship }}
                            </p>
                        </div>
                    
                    
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <button
                    type="button"
                    @click="cancelEditing"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </form>
        
        <!-- Read-only View -->
        <div v-else class="grid grid-cols-1 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                            <div class="p-6 bg-red-50 border border-red-100 rounded-xl">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">Emergency Contact</h4>
                                        <p class="mt-1 text-sm text-gray-600">This information is critical for emergency medical situations</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                                    <div class="text-lg font-medium text-gray-900">{{ profile.emergency_contact_name || 'Not set' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <div class="text-lg font-medium text-gray-900">{{ profile.emergency_contact_number || 'Not set' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                                    <div class="text-lg font-medium text-gray-900">{{ profile.emergency_contact_relationship || 'Not set' }}</div>
                                </div>
                            </div>
            </div>
            
        
        </div>
    </div>
</template>

