<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientCase extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_ON_HOLD = 'on_hold';

    public const STATUS_CLOSED = 'closed';

    public const RISK_LOW = 'low';

    public const RISK_MODERATE = 'moderate';

    public const RISK_HIGH = 'high';

    public const RISK_URGENT = 'urgent';

    protected $fillable = [
        'client_profile_id',
        'counsellor_profile_id',
        'opened_by',
        'status',
        'summary',
        'formulation',
        'risk_level',
        'risk_flag',
        'risk_notes',
        'opened_at',
        'closed_at',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'risk_flag' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_ON_HOLD,
            self::STATUS_CLOSED,
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

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(CaseGoal::class)->latest();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(CaseFollowUp::class)->latest();
    }

    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class)->latest();
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ClinicalRecordAccessLog::class)->latest();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function secureDocuments(): HasMany
    {
        return $this->hasMany(
            SecureDocument::class
        );
    }
}
