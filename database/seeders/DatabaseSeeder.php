<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(NationalitiesSeeder::class);
        $this->call(NigerianStatesSeeder::class);
        $this->call(EthnicRegionsSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        User::factory()->create([
            'first_name' => 'Paul',
            'last_name' => 'Iyaji',
            'email' => 'p3.iyaji@gmail.com',
            'password' => bcrypt('nano1234'),
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'phone' => '+447859959495',
            'phone_verified_at' => now(),
            'is_active' => true,
            'is_verified' => true,
        ])->assignRole('admin');

        //$this->call(AppointmentSeeder::class);
    }
}
