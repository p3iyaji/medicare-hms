<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;

/**
 * @property Carbon|null $date_of_birth
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'email',
        'phone',
        'password',
        'user_type',
        'title',
        'first_name', 
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'profile_image',
        'nationality_id',
        'state_id',
        'occupation',
        'work_address',
        'industry',
        'ethinic_region_id',
        'spoken_languages',
        'religion',
        'region',
        'county',
        'district',
        'residential_address',
        'is_verified',
        'is_active',
        'last_login_at',
        'mfa_enabled',
        'email_verified_at',
        'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['name', 'full_name', 'initials', 'age'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',  
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date:Y-m-d',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'spoken_languages' => 'array', 
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = \Illuminate\Support\Str::uuid()->toString();
            }
        });
    }

    // Relationships
    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nationality_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function ethnicRegion()
    {
        return $this->belongsTo(EthnicRegion::class, 'ethinic_region_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'user_specializations')  
            ->withPivot('years_of_experience', 'certification_number')
            ->withTimestamps();
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function primaryPhysician()
    {
        return $this->belongsTo(User::class, 'primary_physician_id');
    }

    public function patients()
    {
        return $this->hasMany(UserProfile::class, 'primary_physician_id');
    }

    // Proper attribute getter
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    // getNameAttribute for compatibility
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    // Add initials attribute
    public function getInitialsAttribute(): string
    {
        $initials = '';
        if ($this->first_name) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if ($this->middle_name) {
            $initials .= strtoupper(substr($this->middle_name, 0, 1));   
        }

        if ($this->last_name) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }
        return $initials ?: 'U';
    }

    // Add formatted title with name attribute
    public function getFormalNameAttribute(): string
    {
        $parts = [];
        if ($this->title) {
            $parts[] = $this->title;
        }
        if ($this->first_name) {
            $parts[] = $this->first_name;
        }
        if ($this->last_name) {
            $parts[] = $this->last_name;
        }
        
        return implode(' ', $parts) ?: 'User';
    }

    // Calculate age from date_of_birth
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    public function scopeDoctors($query)
    {
        return $query->where('user_type', 'doctor');
    }

    public function scopePatients($query)
    {
        return $query->where('user_type', 'patient');
    }

    public function scopeNurses($query)
    {
        return $query->where('user_type', 'nurse');
    }

    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    // Additional helper methods
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
}