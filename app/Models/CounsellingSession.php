<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CounsellingSession extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const RISK_LOW = 'low';

    public const RISK_MODERATE = 'moderate';

    public const RISK_HIGH = 'high';

    public const RISK_URGENT = 'urgent';

    protected $fillable = [
        'appointment_id',
        'client_profile_id',
        'counsellor_profile_id',
        'status',
        'mode',
        'started_at',
        'ended_at',
        'completed_at',
        'presenting_summary',
        'intervention_summary',
        'outcome_summary',
        'client_visible_summary',
        'homework',
        'private_notes',
        'admin_notes',
        'clinical_risk_level',
        'follow_up_recommended',
        'follow_up_notes',
        'next_session_recommended_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'completed_at' => 'datetime',
        'follow_up_recommended' => 'boolean',
        'next_session_recommended_at' => 'date',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function riskLevels(): array
    {
        return [
            self::RISK_LOW,
            self::RISK_MODERATE,
            self::RISK_HIGH,
            self::RISK_URGENT,
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SessionNote::class);
    }

    public function privateNotes(): HasMany
    {
        return $this->hasMany(SessionNote::class)
            ->where('visibility', SessionNote::VISIBILITY_PRIVATE);
    }

    public function clientVisibleNotes(): HasMany
    {
        return $this->hasMany(SessionNote::class)
            ->where('visibility', SessionNote::VISIBILITY_CLIENT);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForClientProfile(Builder $query, ClientProfile $clientProfile): Builder
    {
        return $query->where('client_profile_id', $clientProfile->id);
    }

    public function scopeForCounsellorProfile(Builder $query, CounsellorProfile $counsellorProfile): Builder
    {
        return $query->where('counsellor_profile_id', $counsellorProfile->id);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    public function canBeEditedByCounsellor(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_IN_PROGRESS,
        ], true);
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_IN_PROGRESS,
        ], true);
    }

    public function canBeViewedByClient(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class);
    }

    public function secureDocuments(): HasMany
    {
        return $this->hasMany(
            SecureDocument::class
        );
    }
}
