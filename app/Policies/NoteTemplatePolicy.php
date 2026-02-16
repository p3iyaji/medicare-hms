<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NoteTemplate;

class NoteTemplatePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, NoteTemplate $noteTemplate)
    {
        return $user->user_type === 'doctor' && $noteTemplate->doctor_id === $user->id;
    }

    public function update(User $user, NoteTemplate $noteTemplate)
    {
        return $user->user_type === 'doctor' && $noteTemplate->doctor_id === $user->id;
    }

    public function delete(User $user, NoteTemplate $noteTemplate)
    {
        return $user->user_type === 'doctor' && $noteTemplate->doctor_id === $user->id;
    }
}
