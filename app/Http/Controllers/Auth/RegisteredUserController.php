<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'userTypes' => [
                'patient' => 'Patient',
                'doctor' => 'Doctor',
                'nurse' => 'Nurse',
                'admin' => 'Administrator',
                'lab_technician' => 'Lab Technician',
                'caregiver' => 'Caregiver',
            ]
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|in:patient,doctor,nurse,admin,lab_technician,caregiver',
        ]);

       $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'uuid' => Str::uuid()->toString(),
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'is_active' => true,
                'is_verified' => false,
            ]);

            // Assign default role based on user type
            $roleName = $this->getDefaultRoleForUserType($request->user_type);
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->assignRole($role);

            // Create empty profile
            $user->profile()->create();

            event(new Registered($user));

            return $user;
        });

        Auth::login($user);


        return redirect(route('dashboard', absolute: false));
    }

    protected function getDefaultRoleForUserType($userType): string
    {
        $roleMap = [
            'patient' => 'patient',
            'doctor' => 'doctor',
            'nurse' => 'nurse',
            'admin' => 'admin',
            'lab_technician' => 'lab_technician',
            'caregiver' => 'caregiver',
        ];

        return $roleMap[$userType] ?? 'patient';
    }
}
