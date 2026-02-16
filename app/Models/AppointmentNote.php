<?php

namespace App\Models;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentNote extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'appointment_id',
        'doctor_id',
        'content',
        'is_private',
        'metadata',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'metadata' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($appointmentNote) {
            $appointmentNote->id = (string) \Illuminate\Support\Str::uuid();
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

    public function scopeVisibleForUser($query, $user)
    {
        if ($user->user_type === 'doctor') {
            return $query->where(function ($q) use ($user) {
                $q->where('doctor_id', $user->id)
                    ->orWhere('is_private', false);
            });
        }

        return $query->where('is_private', false);
    }
}
