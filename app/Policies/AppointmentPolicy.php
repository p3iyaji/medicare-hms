<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Appointment $appointment)
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        if ($user->user_type === 'doctor') {
            return $appointment->doctor_id === $user->id;
        }

        if ($user->user_type === 'patient') {
            return $appointment->patient_id === $user->id;
        }

        return false;
    }

    public function update(User $user, Appointment $appointment)
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        if ($user->user_type === 'doctor') {
            return $appointment->doctor_id === $user->id;
        }

        return false;
    }

    public function consult(User $user, Appointment $appointment)
    {
        return $user->user_type === 'doctor' && $appointment->doctor_id === $user->id;
    }

    public function delete(User $user, Appointment $appointment)
    {
        return $user->user_type === 'admin';
    }
}
