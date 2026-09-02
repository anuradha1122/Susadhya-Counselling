<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientIntake extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_REQUIRES_FOLLOW_UP = 'requires_follow_up';

    public const RISK_LOW = 'low';

    public const RISK_MODERATE = 'moderate';

    public const RISK_HIGH = 'high';

    public const RISK_URGENT = 'urgent';

    protected $fillable = [
        'client_profile_id',
        'status',
        'presenting_concerns',
        'current_symptoms',
        'counselling_goals',
        'preferred_session_mode',
        'previous_counselling',
        'previous_counselling_notes',
        'medication_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'consent_terms_accepted',
        'consent_privacy_accepted',
        'consent_telehealth_accepted',
        'consent_data_processing_accepted',
        'consent_given_at',
        'consent_version',
        'risk_level',
        'risk_notes',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'reviewer_notes',
    ];

    protected $casts = [
        'previous_counselling' => 'boolean',
        'consent_terms_accepted' => 'boolean',
        'consent_privacy_accepted' => 'boolean',
        'consent_telehealth_accepted' => 'boolean',
        'consent_data_processing_accepted' => 'boolean',
        'consent_given_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_REQUIRES_FOLLOW_UP,
        ];
    }

    public static function reviewStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_REQUIRES_FOLLOW_UP,
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

    public static function preferredSessionModes(): array
    {
        return [
            'online',
            'in_person',
            'either',
        ];
    }

    public static function screeningQuestions(): array
    {
        return [
            [
                'key' => 'mood_low',
                'label' => 'Feeling down, sad, hopeless, or emotionally exhausted',
                'category' => 'Mood',
            ],
            [
                'key' => 'anxiety_worry',
                'label' => 'Feeling anxious, worried, tense, or unable to relax',
                'category' => 'Anxiety',
            ],
            [
                'key' => 'sleep_issues',
                'label' => 'Difficulty sleeping, staying asleep, or sleeping too much',
                'category' => 'Sleep',
            ],
            [
                'key' => 'stress_overload',
                'label' => 'Feeling overwhelmed by work, studies, family, or life pressure',
                'category' => 'Stress',
            ],
            [
                'key' => 'relationship_issues',
                'label' => 'Relationship, family, communication, or conflict concerns',
                'category' => 'Relationships',
            ],
            [
                'key' => 'daily_functioning',
                'label' => 'Difficulty managing daily activities, responsibilities, or focus',
                'category' => 'Functioning',
            ],
            [
                'key' => 'self_harm_thoughts',
                'label' => 'Thoughts of self-harm, suicide, or not wanting to live',
                'category' => 'Risk',
                'risk_flag' => true,
            ],
        ];
    }

    public static function screeningScoreOptions(): array
    {
        return [
            [
                'value' => 0,
                'label' => 'Not at all',
            ],
            [
                'value' => 1,
                'label' => 'Several days',
            ],
            [
                'value' => 2,
                'label' => 'More than half the days',
            ],
            [
                'value' => 3,
                'label' => 'Nearly every day',
            ],
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function screeningAnswers(): HasMany
    {
        return $this->hasMany(ClientScreeningAnswer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeSubmittedForReview(Builder $query): Builder
    {
        return $query->whereIn('status', self::reviewStatuses());
    }

    public function canBeEditedByClient(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REQUIRES_FOLLOW_UP,
        ], true);
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_REVIEWED,
            self::STATUS_REQUIRES_FOLLOW_UP,
        ], true);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function riskBadgeLabel(): string
    {
        return str($this->risk_level)->replace('_', ' ')->title()->toString();
    }
}
