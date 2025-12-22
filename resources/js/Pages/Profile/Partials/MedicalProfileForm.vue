<script setup>
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true
    },
    physicians: {
        type: Array,
        default: () => []
    }
});

const bloodtypes = usePage().props.bloodtypes;
const genotypes = usePage().props.genotypes;
const emit = defineEmits(['updated']);

const isEditing = ref(false);

// Initialize form with profile data
const form = useForm({
    blood_type: props.profile.blood_type || '',
    genotype: props.profile.genotype || '',
    height: props.profile.height || '',
    weight: props.profile.weight || '',
    primary_physician_id: props.profile.primary_physician_id || '',
    allergies_text: props.profile.allergies ? props.profile.allergies.join('\n') : '',
    chronic_conditions_text: props.profile.chronic_conditions ? props.profile.chronic_conditions.join('\n') : ''
});

// Start editing
const startEditing = () => {
    isEditing.value = true;
    // Reset form with current profile data
    form.blood_type = props.profile.blood_type || '';
    form.genotype = props.profile.genotype || '';
    form.height = props.profile.height || '';
    form.weight = props.profile.weight || '';
    form.primary_physician_id = props.profile.primary_physician_id || '';
    form.allergies_text = props.profile.allergies ? props.profile.allergies.join('\n') : '';
    form.chronic_conditions_text = props.profile.chronic_conditions ? props.profile.chronic_conditions.join('\n') : '';
    form.clearErrors();
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    form.clearErrors();
};

// Submit form
const submitForm = () => {
    // Convert textarea input to arrays
    const formData = {
        ...form.data(),
        allergies: form.allergies_text ? form.allergies_text.split('\n').filter(item => item.trim() !== '') : [],
        chronic_conditions: form.chronic_conditions_text ? form.chronic_conditions_text.split('\n').filter(item => item.trim() !== '') : []
    };
    
    // Remove text fields from form data
    delete formData.allergies_text;
    delete formData.chronic_conditions_text;
    
    form.transform(() => formData).put('medical-update', {
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
            <h3 class="text-lg font-semibold text-gray-900">Medical Profile</h3>
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
                    Edit Medical Profile
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
                    <!-- Blood Type -->
                    <div>
                        <InputLabel for="blood_type" value="Blood type" />
                      
                        <select
                            id="blood_type"
                            v-model="form.blood_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': form.errors.blood_type }"
                        >
                            <option value="">Select Blood Type</option>
                            <option v-for="(label, value) in bloodtypes" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <p v-if="form.errors.blood_type" class="mt-1 text-sm text-red-600">
                            {{ form.errors.blood_type }}
                        </p>
                    </div>
                    <div>
                        <InputLabel for="genotype" value="Genotype" />

                        <select 
                            id="genotype"
                            v-model="form.genotype"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': form.errors.genotype }"
                         >
                            <option value="">Select Genotype</option>
                            <option v-for="(label, value) in genotypes" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>   
                    </div>
                    
                    <!-- Height and Weight -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="height" value="Height" />
                            
                            <TextInput
                                type="number"
                                id="height"
                                v-model="form.height"
                                min="0"
                                max="300"
                                step="0.1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.height }"
                            />
                            <p v-if="form.errors.height" class="mt-1 text-sm text-red-600">
                                {{ form.errors.height }}
                            </p>
                        </div>
                        <div>
                            <InputLabel for="weight" value="Weight" />
                               
                            <TextInput
                                type="number"
                                id="weight"
                                v-model="form.weight"
                                min="0"
                                max="500"
                                step="0.1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :class="{ 'border-red-300': form.errors.weight }"
                            />
                            <p v-if="form.errors.weight" class="mt-1 text-sm text-red-600">
                                {{ form.errors.weight }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Primary Physician -->
                    <div>
                        <InputLabel for="primary_physician_id" value="Primary Physician" />
                          
                        <select
                            id="primary_physician_id"
                            v-model="form.primary_physician_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': form.errors.primary_physician_id }"
                        >
                            <option value="">Select Physician</option>
                            <option v-for="physician in physicians" :key="physician.id" :value="physician.id">
                                Dr. {{ physician.name }} - {{ physician.specialty }}
                            </option>
                        </select>
                        <p v-if="form.errors.primary_physician_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.primary_physician_id }}
                        </p>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Allergies -->
                    <div>
                        <InputLabel for="allergies" value="Allergies (one per line)" />
                            
                        <textarea
                            id="allergies"
                            v-model="form.allergies_text"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': form.errors.allergies }"
                            placeholder="e.g., Penicillin
Peanuts
Latex"
                        ></textarea>
                        <p v-if="form.errors.allergies" class="mt-1 text-sm text-red-600">
                            {{ form.errors.allergies }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Enter each allergy on a new line</p>
                    </div>
                    
                    <!-- Chronic Conditions -->
                    <div>
                        <InputLabel for="chronic_conditions" value="Chronic conditions (one per line)" />
                            
                        <textarea
                            id="chronic_conditions"
                            v-model="form.chronic_conditions_text"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            :class="{ 'border-red-300': form.errors.chronic_conditions }"
                            placeholder="e.g., Type 2 Diabetes
Hypertension
Asthma"
                        ></textarea>
                        <p v-if="form.errors.chronic_conditions" class="mt-1 text-sm text-red-600">
                            {{ form.errors.chronic_conditions }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Enter each condition on a new line</p>
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
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-gray-900">{{ profile.blood_type || 'Not set' }}</div>
                        <div v-if="profile.blood_type" class="ml-4 px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                            Important Medical Info
                        </div>
                    </div>
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Genotype</label>
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-gray-900">{{ profile.genotype || 'Not set' }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                        <div class="text-lg font-medium text-gray-900">
                            {{ profile.height ? profile.height + ' cm' : 'Not set' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight</label>
                        <div class="text-lg font-medium text-gray-900">
                            {{ profile.weight ? profile.weight + ' kg' : 'Not set' }}
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Physician</label>
                    <div v-if="profile.primary_physician_id" class="flex items-center p-3 bg-blue-50 rounded-lg">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">Dr. {{ profile.primary_physician?.name || 'Physician' }}</div>
                            <div class="text-sm text-gray-500">Primary Care Physician</div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic">No primary physician assigned</div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                    <div v-if="profile.allergies && profile.allergies.length" class="p-4 bg-yellow-50 rounded-lg">
                        <div v-for="(allergy, index) in profile.allergies" :key="index" class="text-gray-900 whitespace-pre-line">
                            • {{ allergy }}
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic">No allergies recorded</div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Chronic Conditions</label>
                    <div v-if="profile.chronic_conditions && profile.chronic_conditions.length" class="p-4 bg-red-50 rounded-lg">
                        <div v-for="(condition, index) in profile.chronic_conditions" :key="index" class="text-gray-900 whitespace-pre-line">
                            • {{ condition }}
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic">No chronic conditions recorded</div>
                </div>
            </div>
        </div>
    </div>
</template>

