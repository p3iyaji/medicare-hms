<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_type',
        'genotype', 
        'height',
        'weight',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',
        'emergency_contact_address', 
        'same_as_users_address', 
        'primary_physician_id',
        'insurance_provider',
        'insurance_policy_number',
        'allergies',
        'chronic_conditions'
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'same_as_users_address' => 'boolean', 
        'allergies' => 'array',
        'chronic_conditions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_physician_id');
    }

    public function getEmergencyAddressAttribute()
{
    if ($this->same_as_users_address && $this->user) {
        return $this->user->address; // Assuming user has an address field
    }
    
    return $this->emergency_contact_address;
}
}