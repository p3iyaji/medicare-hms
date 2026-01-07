<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSpecialization extends Model
{
    protected $fillable = [
        'user_id',
        'specialization_id',
        'years_of_experience',
        'certification_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }
}
