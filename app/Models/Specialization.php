<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_specializations')
                    ->withPivot('years_of_experience', 'certification_number')
                    ->withTimestamps();
    }

    public function doctors()
    {
        return $this->users()->where('user_type', 'doctor');
    }
}