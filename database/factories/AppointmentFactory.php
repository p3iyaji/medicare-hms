<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_no' => Str::random(10),
            'patient_id' => User::factory(),
            'doctor_id' => User::factory(),
            'appointment_type' => 'consultation',
            'status' => 'scheduled',
            'scheduled_date' => fake()->date,
            'scheduled_time' => fake()->time,
            'estimated_duration' => '30mins',
            'actual_start_time' => now(),
            'actual_end_time' => now(),
            'reason' => fake()->sentence,
            'symptoms' => fake()->sentence,
            'priority' => 'low'

        ];
    }
}
