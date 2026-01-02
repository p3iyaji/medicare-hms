<?php

namespace App\Services\Auth;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function attempt(Request $request): User
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();


        // Record login attempt (initially failed)
        $attempt = LoginAttempt::create([
            'user_id' => $user?->id,
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => false,
        ]);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        if (! $user->is_active === true) {
            throw new \Exception('Account deactivated');
        }

        // Mark attempt successful
        $attempt->update(['successful' => true]);

        // Update last login
        $user->update(['last_login_at' => now()]);

        return $user;
    }
}
