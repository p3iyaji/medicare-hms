<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        $this->bumpPatientsCacheVersionIfPatient($user);
    }

    public function updated(User $user): void
    {
        $this->bumpPatientsCacheVersionIfPatient($user);
    }

    public function deleted(User $user): void
    {
        $this->bumpPatientsCacheVersionIfPatient($user);
    }

    public function restored(User $user): void
    {
        $this->bumpPatientsCacheVersionIfPatient($user);
    }

    public function forceDeleted(User $user): void
    {
        $this->bumpPatientsCacheVersionIfPatient($user);
    }

    protected function bumpPatientsCacheVersionIfPatient(User $user): void
    {
        if ($user->user_type !== 'patient') {
            return;
        }

        try {
            $cache = Cache::store('redis');

            $cache->lock('patients:version.lock', 10)->get(function () use ($cache) {

                $version = $cache->get('patients:version');

                if ($version === null) {
                    // Initialize version with TTL
                    $cache->put('patients:version', 1, now()->addDays(30));
                } else {
                    // Increment and re-set TTL in a Laravel-safe way
                    $newVersion = $cache->increment('patients:version');
                    $cache->put('patients:version', $newVersion, now()->addDays(30));
                }
            });

            //Log::info('Patients cache version updated: ' . $cache->get('patients:version'));
        } catch (\Throwable $e) {
            Log::error('Failed to bump patients cache version: ' . $e->getMessage());
        }
    }
}
