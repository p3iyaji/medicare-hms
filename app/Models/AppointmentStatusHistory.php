<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentStatusHistory extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'appointment_id',
        'user_id',
        'old_status',
        'new_status',
        'notes'
    ];


    public static function boot()
    {
        parent::boot();

        static::creating(function ($appointmentStatusHistory) {
            $appointmentStatusHistory->id = (string) \Illuminate\Support\Str::uuid();
        });
    }
    protected $table = 'appointment_status_histories';

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
