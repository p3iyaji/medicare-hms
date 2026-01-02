<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use App\Models\State;
use App\Models\User;
use App\Models\UserProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $profile = Auth::user()->profile;
        $nationalities = Nationality::all();
        $states = State::all();
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'profile' => $profile,
            'status' => session('status'),
            'nationalities'=> $nationalities,
            'states'=> $states,
                    
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
           

        ]);

        $user = User::find($request->user()->id);
       
            $user->title = $request->title;
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->date_of_birth  = $request->date_of_birth;
            $user->gender = $request->gender;
            $user->user_type = $request->user_type;
            $user->occupation = $request->occupation;
            $user->work_address = $request->work_address;   
            $user->residential_address = $request->residential_address;
            $user->nationality_id = $request->nationality_id;
            $user->state_id = $request->state_id;
            $user->industry = $request->industry;
            $user->religion = $request->religion;
            $user->spoken_languages = $request->input('spoken_languages', []);

            $user->update();
    

        //$request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function phone(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string',''],
            ]);
            $user = User::find($request->user()->id);
            $user->phone = $request->phone;
            $user->save();

            return Redirect::route('profile.edit');
    }

    // In your ProfileController.php
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|max:5120', // 5MB max
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old photo if exists
            if ($request->user()->profile_image) {
                Storage::disk('public')->delete($request->user()->profile_image);
            }

            // Store new photo
            $path = $request->file('profile_image')->store('profile-photos', 'public');
            
            // Update user record
            $request->user()->update([
                'profile_image' => $path,
            ]);

            return back()->with('status', 'Profile photo updated successfully.');
        }

        return back()->withErrors(['profile_image' => 'No image file provided.']);
    }

    public function deletePhoto(Request $request)
    {
        if ($request->user()->profile_image) {
            Storage::disk('public')->delete($request->user()->profile_image);
            
            $request->user()->update([
                'profile_image' => null,
            ]);

            return back()->with('status', 'Profile photo removed successfully.');
        }

        return back()->withErrors(['profile_image' => 'No profile photo to delete.']);
    }

    public function updateMedical(Request $request)
    {
        $request->validate([
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'genotype' => ['nullable', 'string', 'in:AA,AS,AC,SS,SC,CC'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'primary_physician_id' => ['nullable', 'exists:users,id'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:255'],
            'chronic_conditions' => ['nullable', 'array'],
            'chronic_conditions.*' => ['string', 'max:255'],
            
        ]);

        $user = Auth::user();
        
        // Update or create medical profile
        $user_profile = UserProfile::where('user_id', $user->id)->first();
        $user_profile->blood_type = $request->blood_type;
        $user_profile->genotype = $request->genotype;
        $user_profile->height = $request->height;
        $user_profile->weight = $request->weight;
        $user_profile->primary_physician_id = $request->primary_physician_id;
        $user_profile->allergies = $request->input('allergies', []);
        $user_profile->chronic_conditions = $request->input('chronic_conditions', []);
        $user_profile->save();
       
        return back()->with('status', 'Medical profile updated successfully!');
    }

    public function updateEmergencyContact(Request $request)
    {
        $request->validate([
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'string', 'min:0', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'same_as_users_address' => 'boolean',

        ]);

        $user = Auth::user();
        

        if($request->same_as_users_address == true) {
            $user_profile = UserProfile::where('user_id', $user->id)->first();
            $user_profile->emergency_contact_name = $request->emergency_contact_name;
            $user_profile->emergency_contact_number = $request->emergency_contact_number;
            $user_profile->emergency_contact_address = $user->residential_address;
            $user_profile->save();
        } else {
            $user_profile = UserProfile::where('user_id', $user->id)->first();
            $user_profile->emergency_contact_name = $request->emergency_contact_name;
            $user_profile->emergency_contact_number = $request->emergency_contact_number;
            $user_profile->emergency_contact_address = $request->emergency_contact_address;
            $user_profile->save();
        }
        
        // Update or create medical profile
        // UserProfile::updateOrCreate(
        //     ['user_id' => $user->id],
        //     $request->only([
        //         'emergency_contact_name', 
        //         'emergency_contact_number', 
        //         'emergency_contact_relationship', 
        //         'emergency_contact_address',
        //         'same_as_users_address || auth()->user()->residential_address', 
                
        //     ]));

        return back()->with('status', 'Emergency contact updated successfully!');
    }

    public function insuranceInfoUpdate(Request $request)
    {
        $request->validate([
            'insurance_provider' => ['nullable', 'string', 'max:100'],
            'insurance_policy_number' => ['nullable', 'string', 'min:0', 'max:50'],
        ]);

        $user = Auth::user();
        
        // Update or create medical profile
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'insurance_provider', 
                'insurance_policy_number', 
                
            ]));

        return back()->with('status', 'Insurance info updated successfully!');
    }

        /**
     * Export user profile as PDF
     */
   public function exportPdf(Request $request)
{
    $user = Auth::user();
    $profile = $user->profile ?? new UserProfile();
    
    // Format data for PDF (same as before)
    $data = [
        'user' => [
            'title' => $user->title,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth?->format('F j, Y'),
            'age' => $user->age,
            'gender' => $user->gender,
            'nationality' => $user->nationality?->name,
            'state' => $user->state?->name,
            'occupation' => $user->occupation,
            'work_address' => $user->work_address,
            'industry' => $user->industry,
            'ethnic_region' => $user->ethnicRegion?->name,
            'spoken_languages' => is_array($user->spoken_languages) 
                ? implode(', ', $user->spoken_languages) 
                : $user->spoken_languages,
            'religion' => $user->religion,
            'region' => $user->region,
            'county' => $user->county,
            'district' => $user->district,
            'residential_address' => $user->residential_address,
            'user_type' => $user->user_type,
            'is_verified' => $user->is_verified ? 'Yes' : 'No',
            'last_login' => $user->last_login_at?->format('F j, Y, g:i A'),
        ],
        'medical' => [
            'blood_type' => $profile->blood_type,
            'genotype' => $profile->genotype,
            'height' => $profile->height ? $profile->height . ' cm' : 'Not set',
            'weight' => $profile->weight ? $profile->weight . ' kg' : 'Not set',
            'bmi' => $profile->height && $profile->weight 
                ? round($profile->weight / (($profile->height/100) ** 2), 1) 
                : 'Not calculable',
        ],
        'emergency' => [
            'contact_name' => $profile->emergency_contact_name,
            'contact_number' => $profile->emergency_contact_number,
            'relationship' => $profile->emergency_contact_relationship,
            'address' => $profile->same_as_users_address 
                ? 'Same as user address: ' . $user->residential_address
                : $profile->emergency_contact_address,
            'same_as_user_address' => $profile->same_as_users_address ? 'Yes' : 'No',
        ],
        'insurance' => [
            'provider' => $profile->insurance_provider,
            'policy_number' => $profile->insurance_policy_number,
        ],
        'health' => [
            'allergies' => is_array($profile->allergies) 
                ? implode(', ', $profile->allergies)
                : ($profile->allergies ?? 'None recorded'),
            'chronic_conditions' => is_array($profile->chronic_conditions)
                ? implode(', ', $profile->chronic_conditions)
                : ($profile->chronic_conditions ?? 'None recorded'),
        ],
        'export_date' => now()->format('F j, Y, g:i A'),
    ];
    
    // Generate PDF
    $pdf = Pdf::loadView('pdf.profile', $data);
    
    // Return PDF as download response
    $filename = 'medical-profile-' . $user->uuid . '-' . now()->format('Y-m-d') . '.pdf';

    return Pdf::loadView('pdf.profile', $data)->download($filename);
}

/**
 * Export blank medical profile form as PDF
 */
public function exportBlankForm()
{
    $data = [
        'export_date' => now()->format('F j, Y'),
        'form_id' => 'MF-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
    ];
    
    $pdf = Pdf::loadView('pdf.blank-profile-form', $data);
    
    return $pdf->download('medical-profile-blank-form-' . date('Y-m-d') . '.pdf');
}

/**
 * Export compact blank form (1-page version)
 */
public function exportCompactBlankForm()
{
    $data = [
        'export_date' => now()->format('F j, Y')
    ];
    
    $pdf = Pdf::loadView('pdf.blank-profile-form-compact', $data);
    
    return $pdf->download('medical-profile-quick-form-' . date('Y-m-d') . '.pdf');
}
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
