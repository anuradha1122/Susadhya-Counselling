<?php

namespace App\Models;

use Database\Factories\CounsellorProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CounsellorProfile extends Model
{
    /** @use HasFactory<CounsellorProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_number',
        'professional_title',
        'nic',
        'date_of_birth',
        'gender',
        'years_of_experience',
        'biography',
        'address',
        'city',
        'status',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date:Y-m-d',
            'years_of_experience' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by',
        );
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(
            CounsellorQualification::class,
            'counsellor_profile_id',
            'id',
        )->orderByDesc('year_completed');
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(
            Specialization::class,
        );
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(
            Language::class,
        )->withPivot('proficiency');
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function availabilityRules(): HasMany
    {
        return $this->hasMany(CounsellorAvailabilityRule::class);
    }

    public function availabilityBreaks(): HasMany
    {
        return $this->hasManyThrough(
            CounsellorAvailabilityBreak::class,
            CounsellorAvailabilityRule::class
        );
    }

    public function blockedSlots(): HasMany
    {
        return $this->hasMany(CounsellorBlockedSlot::class);
    }

    public function leaveDays(): HasMany
    {
        return $this->hasMany(CounsellorLeaveDay::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function counsellingSessions(): HasMany
    {
        return $this->hasMany(CounsellingSession::class);
    }
}
