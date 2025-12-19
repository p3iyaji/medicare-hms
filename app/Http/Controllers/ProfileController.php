<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
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
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'profile' => $profile,
            'status' => session('status'),
            'gender' => [
                'male' => 'Male',
                'female' => 'Female',
                'prefer_not_to_say'=> 'Prefer not to say',
            ],
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
       
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->date_of_birth  = $request->date_of_birth;
            $user->gender = $request->gender;
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
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'blood_type', 
                'height', 
                'weight', 
                'primary_physician_id'
            ]) + [
                'allergies' => $request->input('allergies', []),
                'chronic_conditions' => $request->input('chronic_conditions', [])
            ]
        );

        return back()->with('status', 'Medical profile updated successfully!');
    }

    public function updateEmergencyContact(Request $request)
    {
        $request->validate([
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'string', 'min:0', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        
        // Update or create medical profile
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'emergency_contact_name', 
                'emergency_contact_number', 
                'emergency_contact_relationship', 
                
            ]));

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
