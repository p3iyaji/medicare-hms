<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use App\Models\User;
use App\Models\Nationality;
use App\Models\State;
use App\Models\EthnicRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!in_array($user->user_type, ['admin'])) {
            abort(403, "Unauthorized action.");
        }

        $doctors = User::where('user_type', 'doctor')->with('specializations:id,name')->paginate(10);

        return inertia('Doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        if (!in_array($user->user_type,  ['admin', 'doctor'])) {
            abort(403, 'unauthorized action.');
        }

        return inertia('Doctors/Create', [
            'nationalities' => Nationality::all(),
            'states' => State::all(),
            'ethnicRegions' => EthnicRegion::all(),
            'specializations' => Specialization::all(),
        ]);
    }

    public function edit($id)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $doctor = User::where('user_type', 'doctor')->with('specializations')->findOrFail($id);


        return Inertia::render('Doctors/Create', [
            'doctor' => $doctor->load('profile', 'nationality', 'state', 'ethnicRegion'),
            'nationalities' => Nationality::all(),
            'states' => State::all(),
            'ethnicRegions' => EthnicRegion::all(),
            'specializations' => Specialization::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'genotype' => ['nullable', 'string', 'in:AA,AS,AC,SS,SC,CC'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'primary_physician_id' => ['nullable', 'exists:users,id'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string', 'max:255'],
            'chronic_conditions' => ['nullable', 'array'],
            'chronic_conditions.*' => ['string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'string', 'min:0', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'same_as_users_address' => 'boolean',
            'insurance_provider' => ['nullable', 'string', 'max:100'],
            'insurance_policy_number' => ['nullable', 'string', 'min:0', 'max:50'],
        ]);

        $doctor = User::create([
            'title' => $request->input('title', null),
            'first_name' => $validated['first_name'],
            'middle_name' => $request->input('middle_name', null),
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'doctor',
            'doctor_no' => 'NMCD-' . random_int(100000, 999999),
            'phone' => $request->input('phone', null),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $request->input('gender', null),
            'nationality_id' => $request->input('national_id', null),
            'state_id' => $request->input('state_id', null),
            'primary_physician_id' => $validated['primary_physician_id'] ?? null,
            'residential_address' => $request->input('residential_address', null),
            'is_verified' => true,
            'is_active' => true,
            'spoken_languages' => $request->input('spoken_languages', null),
            'religion' => $request->input('religion', null),
            'ethnic_region_id' => $request->input('ethnic_region_id', null),
            'occupation' => $request->input('occupation', null),
            'work_address' => $request->input('work_address', null),
            'industry' => $request->input('industry', null),
            'region' => $request->input('region', null),
            'county' => $request->input('county', null),
            'district' => $request->input('district', null),
        ]);

        $doctorprofile = $doctor->profile()->create([
            'blood_type' => $validated['blood_type'] ?? null,
            'genotype' => $validated['genotype'] ?? null,
            'height' => $validated['height'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'insurance_provider' => $validated['insurance_provider'] ?? null,
            'insurance_policy_number' => $validated['insurance_policy_number'] ?? null,
            'same_as_users_address' => $request->input('same_as_users_address', false),
            'allergies' => $validated['allergies'] ?? [],
            'chronic_conditions' => $validated['chronic_conditions'] ?? [],
        ]);

        // if specialization data is provided
        if ($request->has('specializations_data') && is_array($request->specializations_data)) {
            $specializationsData = [];
            foreach ($request->specializations_data as $spec) {
                $specializationsData[$spec['specialization_id']] = [
                    'years_of_experience' => $spec['years_of_experience'] ?? null,
                    'certification_number' => $spec['certification_number'] ?? null,
                ];
            }
            $doctor->specializations()->sync($specializationsData);
        }

        return redirect()->route('doctors')->with('success', 'Doctor created successfully.');
    }

    public function update(Request $request, User $doctor)
    {

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $doctor->id,
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
        ]);

        // Update user
        $doctor->update($validated);

        // Update or create profile
        $profileData = $request->only([
            'blood_type',
            'genotype',
            'height',
            'weight',
            'emergency_contact_name',
            'emergency_contact_number',
            'emergency_contact_relationship',
            'emergency_contact_address',
            'same_as_users_address',
            'primary_physician_id',
            'insurance_provider',
            'insurance_policy_number',
            'allergies',
            'chronic_conditions'
        ]);

        if ($doctor->profile) {
            $doctor->profile()->update($profileData);
        } else {
            $doctor->profile()->create($profileData);
        }

        // if specialization data is provided
        if ($request->has('specializations_data') && is_array($request->specializations_data)) {
            $specializationsData = [];
            foreach ($request->specializations_data as $spec) {
                $specializationsData[$spec['specialization_id']] = [
                    'years_of_experience' => $spec['years_of_experience'] ?? null,
                    'certification_number' => $spec['certification_number'] ?? null,
                ];
            }
            $doctor->specializations()->sync($specializationsData);
        }

        return redirect()->route('doctors')
            ->with('success', 'Doctor updated successfully.');
    }

    public function show(User $doctor)
    {
        if ($doctor->user_type !== 'doctor') {
            abort(404, 'Doctor not found.');
        }

        /**
         * @var User $user
         */
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action.');
        }

        // Load all related data
        $doctor->load([
            'nationality',
            'state',
            'profile',
            'specialization',
            'primaryPhysician',
            'profile.medicalHistory',
            'profile.allergies',
        ]);
        return inertia('Doctors/Show', [
            'doctor' => $doctor,
        ]);
    }

    public function deactivate(User $doctor)
    {
        if ($doctor->user_type !== 'doctor') {
            abort(404, 'Doctor not found.');
        }
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        // check if already inactive
        if (!$doctor->is_active) {
            return back()->with('error', 'Doctor account is already inactive.');
        }
        $doctor->update([
            'is_active' => false
        ]);

        // Log the action
        activity()
            ->causedBy($user)
            ->performedOn($doctor)
            ->log('Deactivated doctor account');

        return back()->with('success', 'Doctor account deactivated successfully.');
    }

    public function activate(User $doctor)
    {
        // Ensure the user is a doctor
        if ($doctor->user_type !== 'doctor') {
            abort(404);
        }

        // Only admin and doctors can activate doctors
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already active
        if ($doctor->is_active) {
            return back()->with('error', 'Doctor account is already active.');
        }

        $doctor->update(['is_active' => true]);

        // Log the action
        activity()
            ->causedBy($user)
            ->performedOn($doctor)
            ->log('Activated doctor account');

        return back()->with('success', 'Doctor account activated successfully.');
    }

    public function destroy(User $doctor)
    {
        // Ensure the user is a doctor
        if ($doctor->user_type !== 'doctor') {
            abort(404);
        }

        // Only admin can permanently delete doctors
        if (Auth::user()->user_type !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Use soft delete
        $doctor->delete();

        // Log the action
        activity()
            ->causedBy(Auth::user())
            ->performedOn($doctor)
            ->log('Deleted doctor account');

        return redirect()->route('doctors')->with('success', 'Doctor account deleted successfully.');
    }
}
