<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    profile: {
        type: Object,
        default: () => ({}),
    },
    completionCalculator: {
        type: Function,
        default: null,
    }
});

const emit = defineEmits(['photoUpdated', 'photoDeleted', 'completionUpdated']);

// Photo upload state
const photoPreview = ref(null);
const photoInput = ref(null);
const isUploading = ref(false);
const uploadError = ref(null);
const uploadSuccess = ref(null);

// Form for photo upload
const photoForm = useForm({
    profile_image: null,
});

// Profile completion calculation (can be overridden by parent)
const profileCompletion = computed(() => {
    if (props.completionCalculator) {
        return props.completionCalculator(props.user, props.profile);
    }
    
    // Default calculation
    let completion = 0;
    const totalFields = 16;
    
    const userFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender', 'user_type'];
    const completedUserFields = userFields.filter(field => props.user[field] && props.user[field].toString().trim() !== '').length;
    
    const profileFields = ['blood_type', 'height', 'weight', 'emergency_contact_name', 
                           'emergency_contact_phone', 'insurance_provider', 'insurance_policy_number', 'allergies', 'chronic_conditions'];
    const completedProfileFields = profileFields.filter(field => props.profile[field] && props.profile[field].toString().trim() !== '').length;
    
    const hasProfileImage = props.user.profile_image ? 1 : 0;
    
    const completedFields = completedUserFields + completedProfileFields + hasProfileImage;
    completion = Math.round((completedFields / totalFields) * 100);
    
    return Math.min(completion, 100);
});

// Emit completion update when component mounts or when photo changes
onMounted(() => {
    emit('completionUpdated', profileCompletion.value);
});

// Select new photo
const selectNewPhoto = () => {
    photoInput.value.click();
};

// Update photo preview when file is selected
const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    
    if (!photo) return;
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(photo.type)) {
        uploadError.value = 'Please select a valid image file (JPEG, PNG, GIF, or WebP).';
        return;
    }
    
    // Validate file size (max 5MB)
    if (photo.size > 5 * 1024 * 1024) {
        uploadError.value = 'Image size must be less than 5MB.';
        return;
    }
    
    uploadError.value = null;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
    
    // Update form
    photoForm.profile_image = photo;
    
    // Auto-upload after preview
    uploadPhoto();
};

// Upload photo to server
const uploadPhoto = () => {
    if (!photoForm.profile_image) return;
    
    isUploading.value = true;
    uploadError.value = null;
    uploadSuccess.value = null;
    
    photoForm.post('/profile/photo', {
        preserveScroll: true,
        onSuccess: () => {
            isUploading.value = false;
            uploadSuccess.value = 'Profile photo updated successfully!';
            photoPreview.value = null;
            photoInput.value.value = null;
            
            // Emit events
            emit('photoUpdated');
            emit('completionUpdated', profileCompletion.value);
            
            // Clear success message after 3 seconds
            setTimeout(() => {
                uploadSuccess.value = null;
            }, 3000);
            // router.reload({
            //         only: ['auth.user'],
            //         preserveState: true,
            //         preserveScroll: true,
            //     });
        },
        onError: (errors) => {
            isUploading.value = false;
            uploadError.value = errors.profile_image || 'Failed to upload photo. Please try again.';
            photoPreview.value = null;
        },
    });
};

// Delete current photo
const deletePhoto = () => {
    if (!confirm('Are you sure you want to delete your profile photo?')) {
        return;
    }
    
    isUploading.value = true;
    uploadError.value = null;
    
    useForm({}).delete('/profile/photo', {
        preserveScroll: true,
        onSuccess: () => {
            isUploading.value = false;
            uploadSuccess.value = 'Profile photo removed successfully!';
            photoPreview.value = null;
            
            // Emit events
            emit('photoDeleted');
            emit('completionUpdated', profileCompletion.value);
            
            // Clear success message after 3 seconds
            setTimeout(() => {
                uploadSuccess.value = null;
            }, 3000);

        },
        onError: () => {
            isUploading.value = false;
            uploadError.value = 'Failed to delete photo. Please try again.';
        },
    });
};

// Expose methods if needed
defineExpose({
    selectNewPhoto,
    deletePhoto,
    profileCompletion
});
</script>

<template>
    <div class="flex flex-col items-center justify-center space-y-4">
        <!-- Hidden file input -->
        <input
            ref="photoInput"
            type="file"
            class="hidden"
            @change="updatePhotoPreview"
            accept="image/*"
        >
        
        <!-- Profile Image Container -->
        <div class="relative group">
            <!-- Profile Image -->
            <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-white shadow-lg overflow-hidden">
                <!-- New photo preview -->
                <img v-if="photoPreview" :src="photoPreview" alt="Preview" class="w-full h-full object-cover">
                <!-- Current profile image -->
<img v-else-if="user.profile_image" :src="'/storage/' + user.profile_image" alt="Profile" class="w-full h-full object-cover">                <!-- Default avatar -->
                <div v-else class="w-full h-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                
                <!-- Upload overlay -->
                <div v-if="isUploading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <!-- Hover overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <div class="text-white text-center">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-xs font-medium">Change Photo</span>
                    </div>
                </div>
            </div>
            
            <!-- Delete button (only shown when there's an existing photo) -->
            <button
                v-if="user.profile_image && !photoPreview && !isUploading"
                @click="deletePhoto"
                type="button"
                class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors"
                title="Remove photo"
                aria-label="Remove profile photo"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Upload/Change Button -->
        <button
            @click="selectNewPhoto"
            :disabled="isUploading"
            :class="[
                'px-4 py-2 rounded-lg transition-colors text-sm font-medium',
                isUploading 
                    ? 'bg-gray-400 cursor-not-allowed text-gray-200' 
                    : 'bg-blue-600 hover:bg-blue-700 text-white'
            ]"
            aria-label="Upload or change profile photo"
        >
            <span v-if="isUploading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Uploading...
            </span>
            <span v-else>
                {{ user.profile_image || photoPreview ? 'Change Photo' : 'Upload Photo' }}
            </span>
        </button>
        
        <!-- Error Message -->
        <div v-if="uploadError" class="text-red-600 text-sm text-center max-w-xs animate-fadeIn">
            <div class="flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ uploadError }}
            </div>
        </div>
        
        <!-- Success Message -->
        <div v-if="uploadSuccess" class="text-green-600 text-sm text-center max-w-xs animate-fadeIn">
            <div class="flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ uploadSuccess }}
            </div>
        </div>
        
        <!-- Profile Completion -->
        <div class="text-center text-sm text-gray-500">
            <p>Profile completion: {{ profileCompletion }}%</p>
            <div class="mt-2 w-48 bg-gray-200 rounded-full h-2">
                <div 
                    :class="[
                        'h-2 rounded-full transition-all duration-500',
                        profileCompletion >= 80 ? 'bg-green-500' : 
                        profileCompletion >= 60 ? 'bg-blue-500' : 
                        profileCompletion >= 40 ? 'bg-yellow-500' : 'bg-red-500'
                    ]" 
                    :style="{ width: profileCompletion + '%' }"
                ></div>
            </div>
            <p class="mt-1 text-xs">
                <span v-if="profileCompletion < 40" class="text-red-500">Please complete more profile information</span>
                <span v-else-if="profileCompletion < 80" class="text-yellow-500">Good progress! Keep going</span>
                <span v-else class="text-green-500">Excellent! Profile is well maintained</span>
            </p>
        </div>
        
        <!-- Photo Requirements Info -->
        <div class="text-xs text-gray-400 text-center max-w-xs">
            <p>Supported formats: JPEG, PNG, GIF, WebP</p>
            <p>Max file size: 5MB</p>
            <p>Recommended size: 500x500 pixels</p>
        </div>
        <div class="space-y-4"></div>
       <div class="space-y-4">
        
       </div>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Profile image hover effect */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

/* Smooth transitions for progress bar */
.h-2.rounded-full {
    transition: width 0.5s ease-in-out, background-color 0.3s ease;
}
</style>