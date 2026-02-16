<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteTemplate extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'doctor_id',
        'name',
        'content',
        'category',
        'placeholders',
        'is_shared',
        'usage_count',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_shared' => 'boolean',
        'usage_count' => 'integer'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($notetemplate) {
            if (empty($notetemplate->id)) {
                $notetemplate->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }
}
