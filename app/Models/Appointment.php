<?php

namespace App\Models;

use App\Models\Consultation;
use App\Models\AppointmentNote;
use App\Models\AppointmentStatusHistory;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'appointment_no',
        'patient_id',
        'doctor_id',
        'appointment_type',
        'status',
        'scheduled_date',
        'scheduled_time',
        'consultation_started_at',
        'consultation_ended_at',
        'doctor_notes',
        'diagnosis',
        'prescription',
        'lab_orders',
        'follow_up_instructions',
        'follow_up_date',
        'estimated_duration',
        'actual_start_time',
        'actual_end_time',
        'reason',
        'symptoms',
        'priority',
        'recorded_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            $appointment->id = Str::uuid();
        });
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function notes()
    {
        return $this->hasMany(AppointmentNote::class, 'appointment_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(AppointmentStatusHistory::class)->latest();
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }


    public function updateStatus(string $newStatus, ?string $notes = null): void
    {
        $oldStatus = $this->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $this->update(['status' => $newStatus]);

        // Create status history record
        $this->statusHistory()->create([
            'user_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes
        ]);

        // Additional logic based on status
        switch ($newStatus) {
            case 'in-progress':
                $this->update(['consultation_started_at' => now()]);
                break;
            case 'completed':
                $this->update(['consultation_ended_at' => now()]);
                break;
            case 'cancelled':
                $this->update([
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id()
                ]);
                break;
        }
    }

    /**
     * Start consultation
     */
    // public function startConsultation(): Consultation
    // {
    //     $consultation = $this->consultation()->create([
    //         'doctor_id' => $this->doctor_id,
    //         'patient_id' => $this->patient_id,
    //         'started_at' => now(),
    //         'status' => 'in-progress'
    //     ]);

    //     $this->updateStatus('in-progress', 'Consultation started');

    //     return $consultation;
    // }

    /**
     * End consultation
     */
    // public function endConsultation(): void
    // {
    //     if ($this->consultation) {
    //         $this->consultation->update([
    //             'ended_at' => now(),
    //             'status' => 'completed'
    //         ]);
    //     }

    //     $this->updateStatus('completed', 'Consultation completed');
    // }

    public function startConsultation()
    {
        $consultation = $this->consultation()->create([
            'doctor_id' => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->update([
            'status' => 'in_progress',
            'consultation_started_at' => now(),
            'actual_start_time' => now(),
        ]);

        $this->statusHistory()->create([
            'user_id' => auth()->id(),
            'old_status' => $this->status,
            'new_status' => 'in_progress',
            'notes' => 'Consultation started'
        ]);

        return $consultation;
    }

    public function endConsultation()
    {
        if ($this->consultation) {
            $this->consultation->update([
                'ended_at' => now(),
                'status' => 'completed',
            ]);
        }

        $this->update([
            'status' => 'completed',
            'consultation_ended_at' => now(),
            'actual_end_time' => now(),
        ]);

        $this->statusHistory()->create([
            'user_id' => auth()->id(),
            'old_status' => $this->status,
            'new_status' => 'completed',
            'notes' => 'Consultation completed'
        ]);
    }

    public function getCurrentConsultationAttribute()
    {
        return $this->consultation()->whereNull('ended_at')->latest()->first();
    }


    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('scheduled_date', '>', today())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }
}
