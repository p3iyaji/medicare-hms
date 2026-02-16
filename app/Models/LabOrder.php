<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'doctor_id',
        'patient_id',
        'test_name',
        'instructions',
        'priority',
        'status',
        'ordered_at',
        'collected_at',
        'resulted_at',
        'results',
        'result_file',
        'performed_by',
        'reviewed_by',
        'reviewed_at',
        'metadata'
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'collected_at' => 'datetime',
        'resulted_at' => 'datetime',
        'reviewed_at' => 'datetime',
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

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
