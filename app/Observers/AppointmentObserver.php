<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Cache;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $this->bumpAppointmentsCacheVersion($appointment);
    }

    public function updated(Appointment $appointment): void
    {
        $this->bumpAppointmentsCacheVersion($appointment);
    }

    public function deleted(Appointment $appointment): void
    {
        $this->bumpAppointmentsCacheVersion($appointment);
    }

    protected function bumpAppointmentsCacheVersion(Appointment $appointment): void
    {

        try {
            $cache = Cache::store('redis');

            $cache->lock('appointments:version.lock', 10)->get(function () use ($cache) {

                $version = $cache->get('appointments:version');

                if ($version === null) {
                    // Initialize version with TTL
                    $cache->put('appointments:version', 1, now()->addDays(30));
                } else {
                    // Increment and re-set TTL in a Laravel-safe way
                    $newVersion = $cache->increment('appointments:version');
                    $cache->put('appointments:version', $newVersion, now()->addDays(30));
                }
            });

            //Log::info('Appointments cache version updated: ' . $cache->get('appointments:version'));
        } catch (\Throwable $e) {
            \Log::error('Failed to bump appointments cache version: ' . $e->getMessage());
        }
    }
}
