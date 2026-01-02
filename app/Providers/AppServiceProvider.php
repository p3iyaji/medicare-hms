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

                            // Create an array with ALL user data including computed attributes
                            $userData = [
                                // Basic fields
                                'id' => $user->id,
                                'uuid' => $user->uuid,
                                'title' => $user->title,
                                'first_name' => $user->first_name,
                                'middle_name' => $user->middle_name,
                                'last_name' => $user->last_name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'user_type' => $user->user_type,
                                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                                'gender' => $user->gender,
                                'profile_image' => $user->profile_image
                                    ? (filter_var($user->profile_image, FILTER_VALIDATE_URL)
                                        ? $user->profile_image
                                        : asset('storage/' . $user->profile_image))
                                    : null,
                                'is_verified' => $user->is_verified,
                                'is_active' => $user->is_active,
                                'mfa_enabled' => $user->mfa_enabled,
                                'email_verified_at' => $user->email_verified_at,
                                'phone_verified_at' => $user->phone_verified_at,
                                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),

                                // Personal Information
                                'occupation' => $user->occupation,
                                'work_address' => $user->work_address,
                                'industry' => $user->industry,
                                'spoken_languages' => $user->spoken_languages ?: [],
                                'religion' => $user->religion,
                                'ethnic_region' => $user->ethnic_region,
                                'county' => $user->county,
                                'district' => $user->district,
                                'residential_address' => $user->residential_address,

                                // Foreign keys
                                'nationality_id' => $user->nationality_id,
                                'state_id' => $user->state_id,
                                'ethinic_region_id' => $user->ethinic_region_id,
                            ];

                            // Add computed attributes
                            $computedAttributes = [
                                'name' => $user->name, // Uses getNameAttribute()
                                'full_name' => $user->full_name, // Uses getFullNameAttribute()
                                'formal_name' => $user->formal_name, // Uses getFormalNameAttribute()
                                'initials' => $user->initials, // Uses getInitialsAttribute()
                                'age' => $user->age, // Uses getAgeAttribute()
                                'user_type_label' => $this->getUserTypeLabel($user->user_type),
                                'email_verified' => !is_null($user->email_verified_at),
                                'phone_verified' => !is_null($user->phone_verified_at),
                                'profile_complete' => $this->isProfileComplete($user),
                            ];

                            // Eager load relationships
                            $user->load(['profile', 'roles.permissions', 'nationality', 'state', 'ethnicRegion', 'specializations']);

                            $profile = $user->profile;

                            // Add relationships
                            $relationshipData = [
                                // Location Relationships
                                'nationality' => $user->nationality?->only(['id', 'name', 'code']),
                                'state' => $user->state?->only(['id', 'name']),
                                'ethnic_region' => $user->ethnicRegion?->only(['id', 'name']),

                                // Specializations (for doctors)
                                'specializations' => $user->specializations->map(function ($specialization) {
                                    return [
                                        'id' => $specialization->id,
                                        'name' => $specialization->name,
                                        'years_of_experience' => $specialization->pivot->years_of_experience,
                                        'certification_number' => $specialization->pivot->certification_number,
                                    ];
                                }),

                                // Roles & Permissions
                                'roles' => $user->getRoleNames(),
                                'permissions' => $user->getAllPermissions()->pluck('name'),

                                // Medical Profile Data
                                'profile_data' => $profile ? [
                                    'blood_type' => $profile->blood_type,
                                    'genotype' => $profile->genotype,
                                    'height' => $profile->height,
                                    'weight' => $profile->weight,
                                    'emergency_contact' => $profile->emergency_contact_name
                                        ? [
                                            'name' => $profile->emergency_contact_name,
                                            'phone' => $profile->emergency_contact_number,
                                            'relationship' => $profile->emergency_contact_relationship,
                                            'address' => $profile->emergency_contact_address,
                                            'same_as_users_address' => $profile->same_as_users_address,
                                            'emergency_address' => $profile->emergency_address, // Accessor
                                        ]
                                        : null,
                                    'primary_physician_id' => $profile->primary_physician_id,
                                    'primary_physician' => $profile->physician?->only(['id', 'name', 'full_name', 'formal_name']),
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

                            // Merge all data
                            return array_merge($userData, $computedAttributes, $relationshipData);
                        })()
                        : null,
                ];
            },

            // ... rest of your share method remains the same
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'warning' => Session::get('warning'),
                    'info' => Session::get('info'),
                    'status' => Session::get('status'),
                ];
            },

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

            'medical' => function () {
                return [
                    'blood_types' => [
                        'A+',
                        'A-',
                        'B+',
                        'B-',
                        'AB+',
                        'AB-',
                        'O+',
                        'O-'
                    ],
                    'genotypes' => [
                        'AA',
                        'AS',
                        'AC',
                        'SS',
                        'SC',
                        'CC'
                    ],
                    'genders' => [
                        'male',
                        'female',
                        'other',
                        'prefer not to say'
                    ],
                    'user_types' => [
                        'patient' => 'Patient',
                        'doctor' => 'Doctor',
                        'nurse' => 'Nurse',
                        'admin' => 'Administrator',
                        'lab_technician' => 'Lab Technician',
                        'caregiver' => 'Caregiver',
                    ],
                    'titles' => [
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Miss' => 'Miss',
                        'Ms' => 'Ms',
                        'Dr' => 'Dr',
                        'Prof' => 'Prof',
                        'Alhaji' => 'Alhaji',
                    ],
                    'religions' => [
                        'Christianity' => 'Christianity',
                        'Islam' => 'Islam',
                        'None' => 'None',
                        'Prefer not to say' => 'Prefer not to say'
                    ],
                ];
            },

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
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                return false;
            }
        }

        return true;
    }
}
