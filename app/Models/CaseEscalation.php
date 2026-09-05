<?php

namespace App\Models;

use Database\Factories\CaseEscalationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseEscalation extends Model
{
    /** @use HasFactory<CaseEscalationFactory> */
    use HasFactory;

    use HasUuids;

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_DISMISSED = 'dismissed';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const REASON_COORDINATION = 'care_coordination';

    public const REASON_SCHEDULING = 'scheduling';

    public const REASON_ACCESS = 'access_issue';

    public const REASON_SUPERVISOR = 'supervisor_attention';

    public const REASON_OTHER = 'other';

    protected $fillable = [
        'case_reference',
        'reason_code',
        'priority',
        'status',
        'assigned_to',
        'due_at',
        'admin_note',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return [
            'uuid',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_RESOLVED,
            self::STATUS_DISMISSED,
        ];
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];
    }

    public static function reasons(): array
    {
        return [
            self::REASON_COORDINATION,
            self::REASON_SCHEDULING,
            self::REASON_ACCESS,
            self::REASON_SUPERVISOR,
            self::REASON_OTHER,
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'resolved_by'
        );
    }
}
