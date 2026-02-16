<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'started_at',
        'ended_at',
        'doctor_notes',
        'diagnosis',
        'prescription',
        'lab_orders',
        'follow_up_instructions',
        'follow_up_date',
        'status',
        'vitals_id',
        'metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'follow_up_date' => 'date',
        'metadata' => 'array',
    ];


    public static function boot()
    {
        parent::boot();

        static::creating(function ($consultation) {
            $consultation->id = (string) \Illuminate\Support\Str::uuid();
        });
    }


    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function vitals()
    {
        return $this->belongsTo(VitalSign::class, 'vitals_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
