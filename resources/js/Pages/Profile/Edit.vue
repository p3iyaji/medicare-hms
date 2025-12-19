<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePhoneForm from './Partials/UpdatePhoneForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ProfilePhotoUpload from './Partials/ProfilePhotoUpload.vue';
import MedicalProfileForm from './Partials/MedicalProfileForm.vue';
import EmergencyContactForm from './Partials/EmergencyContactForm.vue';
import HealthInsurance from './Partials/HealthInsurance.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    
    profile: {
        type: Object,
        required: true
    },
});


const page = usePage();
const auth = computed(() => page.props.auth || { user: null});
const user = computed(() => usePage().props.auth.user);



// Active tab state
const activeTab = ref('personal');

// Custom completion calculation
const customCompletionCalculator = (user, profile) => {
    // You can customize this logic as needed
    let completion = 0;
    const totalFields = 16; // Including profile photo as a field
    
    const userFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender', 'user_type'];
    const completedUserFields = userFields.filter(field => user[field] && user[field].toString().trim() !== '').length;
    
    const profileFields = ['blood_type', 'height', 'weight', 'emergency_contact_name', 
                           'emergency_contact_phone', 'emergency_contact_relationship',
                           'insurance_provider', 'insurance_policy_number', 'allergies', 'chronic_conditions'];
    const completedProfileFields = profileFields.filter(field => profile[field] && profile[field].toString().trim() !== '').length;
    
    const hasProfileImage = user.profile_image ? 2 : 0; // Give more weight to profile photo
    
    const completedFields = completedUserFields + completedProfileFields + hasProfileImage;
    completion = Math.round((completedFields / totalFields) * 100);
    
    return Math.min(completion, 100);
};

// Handle photo update events
const handlePhotoUpdated = () => {
    console.log('Photo updated - refresh user data if needed');
    
};

const handlePhotoDeleted = () => {
    console.log('Photo deleted - refresh user data if needed');
    
};

const handleCompletionUpdated = (completion) => {
    console.log('Profile completion updated:', completion + '%');
};
// photo update end

// Handle medical profile

const handleMedicalProfileUpdated = () => {
    console.log('Medical profile updated - refreshing data...');
    
    router.reload({
        only: ['profile'],
        preserveScroll: true,
        preserveState: true
    });
};

const handleEmergencyContactUpdated = () => {
    console.log('Emergency contact profile updated - refreshing data ...');
    router.reload({
        only: ['profile'],
        preserveScroll: true,
        preserveState: true
    });
}

const handleInsuranceInfoUpdated = () => {
    console.log('Insurance info updated - refreshing data ...');
    router.reload({
        only: ['profile'],
        preserveScroll: true,
        preserveState: true
    });
}



</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Profile Management
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Complete and manage your medical profile information
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    Last updated: {{ new Date(user.updated_at).toLocaleDateString() }}
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Profile Header -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-8">
                        <div class="flex items-center">
                            <div class="mr-4">
                                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-lg">
                                    <svg v-if="!user.profile_image" class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <img v-else :src="'/storage/' + user.profile_image" alt="Profile" class="w-full h-full rounded-full object-cover">
                                </div>
                            </div>
                            <div class="text-white">
                                <h1 class="text-2xl font-bold">
                                    {{ user.full_name }} 
                                </h1>
                                <p class="text-blue-100">{{ user.email }}</p>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-800 bg-opacity-50">
                                        {{ user.user_type?.replace('_', ' ') || 'Medical Professional' }}
                                    </span>
                                    <span v-if="user.is_verified" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-800 bg-opacity-50">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Verified
                                    </span>
                                    <span v-if="user.mfa_enabled" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-800 bg-opacity-50">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        2FA Enabled
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="mb-8">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 overflow-x-auto">
                            <button
                                @click="activeTab = 'personal'"
                                :class="[
                                    activeTab === 'personal'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Personal Info
                            </button>
                            <button
                                @click="activeTab = 'medical'"
                                :class="[
                                    activeTab === 'medical'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                Medical Profile
                            </button>
                            <button
                                @click="activeTab = 'emergency'"
                                :class="[
                                    activeTab === 'emergency'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Emergency Contact
                            </button>
                            <button
                                @click="activeTab = 'insurance'"
                                :class="[
                                    activeTab === 'insurance'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Insurance
                            </button>
                            <button
                                @click="activeTab = 'security'"
                                :class="[
                                    activeTab === 'security'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Security
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="space-y-8">
                    <!-- Personal Information Tab -->
                    <div v-show="activeTab === 'personal'" class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                            <span class="text-sm text-gray-500">Last login: {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Left Column -->
                             <UpdateProfileInformationForm />
                          
                            
                            <!-- Middle Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <div class="flex items-center">
                                        <div class="text-lg font-medium text-gray-900">{{ user.email }}</div>
                                        <span v-if="user.email_verified_at" class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Verified
                                        </span>
                                        <span v-else class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <UpdatePhoneForm />
                                    <div class="flex items-center">
                                        <span v-if="user.phone_verified_at" class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Verified
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                                    <div class="text-lg font-medium text-gray-900">{{ user.user_type?.replace('_', ' ') || 'Not specified' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                                    <span v-if="user.is_active" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                    <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Right Column - Profile Image -->
                            <ProfilePhotoUpload 
                                :user="user"
                                :profile="profile"
                                :completion-calculator="customCompletionCalculator"
                                @photo-updated="handlePhotoUpdated"
                                @photo-deleted="handlePhotoDeleted"
                                @completion-updated="handleCompletionUpdated"
                            />
                        </div>
                    </div>

                    <!-- Medical Profile Tab -->
                    <div v-show="activeTab === 'medical'" class="bg-white rounded-xl shadow-lg p-6">
                        
                        <MedicalProfileForm 
                            :profile="profile"
                            @updated="handleMedicalProfileUpdated"
                        />
                    </div>

                    <!-- Emergency Contact Tab -->
                    <div v-show="activeTab === 'emergency'" class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Emergency Contact Information</h3>
                        
                       <EmergencyContactForm 
                       :profile="profile"
                       @updated="handleEmergencyContactUpdated"
                       />

                    </div>

                    <!-- Insurance Tab -->
                    <div v-show="activeTab === 'insurance'" class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Insurance Information</h3>
                        
                        <HealthInsurance 
                        :profile="profile"
                        @updated="handleInsuranceInfoUpdated"
                        />

                    </div>

                    <!-- Security Tab -->
                    <div v-show="activeTab === 'security'" class="space-y-6">                       

                        <!-- Update Password -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">Security Settings</h3>
                            <UpdatePasswordForm class="max-w-3xl" />
                            
                            
                            <!-- Two-Factor Authentication -->
                            <div class="mt-8 pt-8 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-base font-medium text-gray-900">Two-Factor Authentication</h4>
                                        <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
                                    </div>
                                    <div>
                                        <span v-if="user.mfa_enabled" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Enabled
                                        </span>
                                        <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            Disabled
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Account -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <DeleteUserForm class="max-w-3xl" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for tabs */
nav::-webkit-scrollbar {
    height: 4px;
}

nav::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

nav::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 2px;
}

/* Smooth transitions for tab content */
[v-show] {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Styling for read-only data display */
.text-lg.font-medium.text-gray-900 {
    min-height: 2rem;
    display: flex;
    align-items: center;
}

/* Profile image hover effect */
.w-32.h-32.rounded-full:hover img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}
</style>