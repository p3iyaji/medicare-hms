<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Models\Nationality;
use App\Models\State;
use App\Models\EthnicRegion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request)
    {

        /** @var User $user */
        $user = $request->user();

        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action!');
        }

        $query = User::where('user_type', 'patient')
            ->with('nationality', 'state')
            ->latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'inactive') {
                $query->where('is_active', '=', 0);
            } else if ($request->status === 'active') {
                $query->where('is_active', '=', 1);
            }
        }

        if ($request->filled('gender')) {
            $query->where('gender', '=', $request->gender);
        }

        if ($request->filled('verified')) {
            if ($request->verified === 'unverified') {
                $query->where('is_verified', '=', 0);
            } else if ($request->verified === 'verified') {
                $query->where('is_verified', '=', 1);
            }
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                    $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // Log error or handle invalid date format
                    Log::error('Invalid date range format: ' . $request->date_range);
                }
            }
        }

        $patients = $query->paginate(100)->withQueryString();

        $dateRangeOptions = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This week',
            'last_week' => 'Last week',
            'this_month' => 'This month',
            'last_month' => 'Last month',
            'last_30_days' => 'Last 30 days',
        ];

        Log::info($patients->toArray());

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search', 'status', 'gender', 'verified', 'date_range']),
            'dateRangeOptions' => $dateRangeOptions,
        ]);
    }


    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Patients/Create', [
            'nationalities' => Nationality::all(),
            'states' => State::all(),
            'ethnicRegions' => EthnicRegion::all(),
            'physicians' => User::doctors()->with('specializations')->get(),
        ]);
    }

    public function edit($id)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action.');
        }

        $patient = User::where('user_type', 'patient')->findOrFail($id);

        return Inertia::render('Patients/Create', [
            'patient' => $patient->load('profile', 'nationality', 'state', 'ethnicRegion'),
            'nationalities' => Nationality::all(),
            'states' => State::all(),
            'ethnicRegions' => EthnicRegion::all(),
            'physicians' => User::doctors()->with('specializations')->get(),

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

        $patient = User::create([
            'title' => $request->input('title', null),
            'first_name' => $validated['first_name'],
            'middle_name' => $request->input('middle_name', null),
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'patient',
            'patient_no' => 'NMC-' . random_int(100000, 999999),
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

        $patientprofile = $patient->profile()->create([
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

        return redirect()->route('patients')->with('success', 'Patient created successfully.');
    }

    public function update(Request $request, User $patient)
    {
       
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $patient->id,
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
        ]);

        // Update user
        $patient->update($validated);

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

        if ($patient->profile) {
            $patient->profile()->update($profileData);
        } else {
            $patient->profile()->create($profileData);
        }

        return redirect()->route('patients')
            ->with('success', 'Patient updated successfully.');
    }

    public function show(User $patient)
    {
        if ($patient->user_type !== 'patient') {
            abort(404, 'Patient not found.');
        }

        /**
         * @var User $user
         */
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action.');
        }

        // Load all related data
        $patient->load([
            'nationality',
            'state',
            'profile',
            'specialization',
            'primaryPhysician',
            'profile.medicalHistory',
            'profile.allergies',
        ]);
        return inertia('Patients/Show', [
            'patient' => $patient,
        ]);
    }

    public function deactivate(User $patient)
    {
        if ($patient->user_type !== 'patient') {
            abort(404, 'Patient not found.');
        }
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        // check if already inactive
        if (!$patient->is_active) {
            return back()->with('error', 'Patient account is already inactive.');
        }
        $patient->update([
            'is_active' => false
        ]);

        // Log the action
        activity()
            ->causedBy($user)
            ->performedOn($patient)
            ->log('Deactivated patient account');

        return back()->with('success', 'Patient account deactivated successfully.');
    }

    public function activate(User $patient)
    {
        // Ensure the user is a patient
        if ($patient->user_type !== 'patient') {
            abort(404);
        }

        // Only admin and doctors can activate patients
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already active
        if ($patient->is_active) {
            return back()->with('error', 'Patient account is already active.');
        }

        $patient->update(['is_active' => true]);

        // Log the action
        activity()
            ->causedBy($user)
            ->performedOn($patient)
            ->log('Activated patient account');

        return back()->with('success', 'Patient account activated successfully.');
    }

    public function destroy(User $patient)
    {
        // Ensure the user is a patient
        if ($patient->user_type !== 'patient') {
            abort(404);
        }

        // Only admin can permanently delete patients
        if (Auth::user()->user_type !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Use soft delete
        $patient->delete();

        // Log the action
        activity()
            ->causedBy(Auth::user())
            ->performedOn($patient)
            ->log('Deleted patient account');

        return redirect()->route('patients')->with('success', 'Patient account deleted successfully.');
    }
}
