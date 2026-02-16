<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'patient_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'route',
        'form',
        'quantity',
        'refills',
        'start_date',
        'end_date',
        'dispensed',
        'dispensed_at',
        'status',
        'metadata'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'dispensed' => 'boolean',
        'dispensed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
