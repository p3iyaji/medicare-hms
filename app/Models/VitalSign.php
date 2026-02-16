<?php

namespace App\Models;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class VitalSign extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'recorded_by',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
        'notes',
        'recorded_at',
        'data_source',
        'device_id',
        'pain_scale',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($vitalSign) {
            $vitalSign->id = Str::uuid();
        });
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function recorded_by()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
