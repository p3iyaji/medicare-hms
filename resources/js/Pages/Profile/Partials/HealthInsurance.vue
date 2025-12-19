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
    insurance_provider: props.profile.insurance_provider || '',
    insurance_policy_number: props.profile.insurance_policy_number || '',
    
});

// Start editing
const startEditing = () => {
    isEditing.value = true;
    // Reset form with current profile data
    form.insurance_provider = props.profile.insurance_provider || '';
    form.insurance_policy_number = props.profile.insurance_policy_number || '';
    form.clearErrors();
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    form.clearErrors();
};

// Submit form
const submitForm = () => {
    
    form.put('insuranceinfo-update', {
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
            <h3 class="text-lg font-semibold text-gray-900">Insurance details</h3>
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
                    Edit Insurance details
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
                    <!-- insurance provider-->
                    <div>
                        <InputLabel for="insurance_provider" value="Insurance provider" />
                      
                            <TextInput
                                type="text"
                                id="insurance_provider"
                                v-model="form.insurance_provider"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.insurance_provider }"
                            />
                        <p v-if="form.errors.insurance_provider" class="mt-1 text-sm text-red-600">
                            {{ form.errors.insurance_provider }}
                        </p>
                    </div>
                    
                   
                                        
                </div>
                
                <!-- Right Column -->
                <div class="space-y-6">
                         <!-- insurance policy number -->
                        <div>
                            <InputLabel for="insurance_policy_number" value="Emergency contact number" />
                            
                            <TextInput
                                type="text"
                                id="insurance_policy_number"
                                v-model="form.insurance_policy_number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.insurance_policy_number }"
                            />
                            <p v-if="form.errors.insurance_policy_number" class="mt-1 text-sm text-red-600">
                                {{ form.errors.insurance_policy_number }}
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Insurance Provider</label>
                                    <div class="text-lg font-medium text-gray-900">{{ profile.insurance_provider || 'Not set' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Policy Number</label>
                                    <div class="text-lg font-medium text-gray-900 font-mono">{{ profile.insurance_policy_number || 'Not set' }}</div>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm text-gray-700">
                                        Insurance information is stored securely and only accessible to authorized medical personnel.
                                    </div>
                                </div>
                            </div>
                        </div>              
            
        
        </div>
    </div>
</template>

