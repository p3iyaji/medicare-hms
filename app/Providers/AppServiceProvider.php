<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            // Authentication data
            'auth' => function () {
                return [
                    'user' => Auth::check()
                        ? (function () {
                            /** @var User $user */
                            $user = Auth::user();
                            
                            // Eager load relationships
                            $user->load(['profile', 'roles.permissions']);
                            
                            $profile = $user->profile;
                            
                            return [
                                'id' => $user->id,
                                'uuid' => $user->uuid,
                                'name' => $user->name, // Uses getNameAttribute()
                                'full_name' => $user->full_name, // Uses getFullNameAttribute()
                                'first_name' => $user->first_name,
                                'middle_name' => $user->middle_name,
                                'last_name' => $user->last_name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'user_type' => $user->user_type,
                                'user_type_label' => $this->getUserTypeLabel($user->user_type),
                                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'), // Format for Vue
                                'gender' => $user->gender,
                                'profile_image' => $user->profile_image 
                                    ? (filter_var($user->profile_image, FILTER_VALIDATE_URL) 
                                        ? $user->profile_image 
                                        : asset('storage/' . $user->profile_image))
                                    : null,
                                'is_verified' => $user->is_verified,
                                'is_active' => $user->is_active,
                                'mfa_enabled' => $user->mfa_enabled,
                                'email_verified' => !is_null($user->email_verified_at),
                                'phone_verified' => !is_null($user->phone_verified_at),
                                'profile_complete' => $this->isProfileComplete($user),
                                'initials' => $user->initials,
                                
                                // Roles & Permissions
                                'roles' => $user->getRoleNames(),
                                'permissions' => $user->getAllPermissions()->pluck('name'),
                                
                                // Medical Profile Data
                                'profile_data' => $profile ? [
                                    'blood_type' => $profile->blood_type,
                                    'height' => $profile->height,
                                    'weight' => $profile->weight,
                                    'emergency_contact' => $profile->emergency_contact_name 
                                        ? [
                                            'name' => $profile->emergency_contact_name,
                                            'phone' => $profile->emergency_contact_phone,
                                            'relationship' => $profile->emergency_contact_relationship,
                                        ]
                                        : null,
                                    'primary_physician_id' => $profile->primary_physician_id,
                                    'insurance' => $profile->insurance_provider
                                        ? [
                                            'provider' => $profile->insurance_provider,
                                            'policy_number' => $profile->insurance_policy_number,
                                        ]
                                        : null,
                                    'allergies' => $profile->allergies ?: [],
                                    'chronic_conditions' => $profile->chronic_conditions ?: [],
                                ] : null,
                            ];
                        })()
                        : null,
                ];
            },
            
            // Flash messages
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'warning' => Session::get('warning'),
                    'info' => Session::get('info'),
                    'status' => Session::get('status'),
                ];
            },
            
            // Application data
            'app' => function () {
                return [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                    'fallback_locale' => config('app.fallback_locale'),
                ];
            },
            
            // Medical-specific shared data
            'medical' => function () {
                return [
                    'blood_types' => [
                        'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
                    ],
                    'genders' => [
                        'male', 'female', 'other', 'prefer_not_to_say'
                    ],
                    'user_types' => [
                        'patient' => 'Patient',
                        'doctor' => 'Doctor',
                        'nurse' => 'Nurse',
                        'admin' => 'Administrator',
                        'lab_technician' => 'Lab Technician',
                        'caregiver' => 'Caregiver',
                    ],
                ];
            },
            
            // CSRF token for forms
            'csrf_token' => function () {
                return csrf_token();
            },
        ]);
    }
    
    /**
     * Get user-friendly label for user type
     */
    private function getUserTypeLabel(string $userType): string
    {
        $labels = [
            'patient' => 'Patient',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'admin' => 'Administrator',
            'lab_technician' => 'Lab Technician',
            'caregiver' => 'Caregiver',
        ];
        
        return $labels[$userType] ?? 'User';
    }
    
    /**
     * Check if user profile is complete
     */
    private function isProfileComplete(User $user): bool
    {
        $requiredFields = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
        
        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                return false;
            }
        }
        
        return true;
    }
}