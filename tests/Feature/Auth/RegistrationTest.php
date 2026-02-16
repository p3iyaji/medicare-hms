<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Paul',
            'last_name' => 'Iyaji',
            'email' => 'test@example.com',
            'password' => 'nano1234',
            'password_confirmation' => 'nano1234',
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'phone' => '+447859959495',
            'phone_verified_at' => now(),
            'is_active' => true,
            'is_verified' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
