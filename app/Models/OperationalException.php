<?php

namespace App\Models;

use Database\Factories\OperationalExceptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalException extends Model
{
    /** @use HasFactory<OperationalExceptionFactory> */
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

    public const TYPE_BOOKING = 'booking_exception';

    public const TYPE_AVAILABILITY = 'availability_exception';

    public const TYPE_NOTIFICATION = 'notification_delivery';

    public const TYPE_ACCOUNT = 'account_access';

    public const TYPE_CONTENT = 'content_issue';

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'type',
        'priority',
        'status',
        'source_type',
        'source_reference',
        'title',
        'description',
        'assigned_to',
        'due_at',
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

    public static function types(): array
    {
        return [
            self::TYPE_BOOKING,
            self::TYPE_AVAILABILITY,
            self::TYPE_NOTIFICATION,
            self::TYPE_ACCOUNT,
            self::TYPE_CONTENT,
            self::TYPE_OTHER,
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

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
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
