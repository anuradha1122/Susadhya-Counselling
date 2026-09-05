<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class DataBreach extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_INVESTIGATING =
        'investigating';

    public const STATUS_CONTAINED =
        'contained';

    public const STATUS_CLOSED =
        'closed';

    public const SEVERITY_LOW =
        'low';

    public const SEVERITY_MEDIUM =
        'medium';

    public const SEVERITY_HIGH =
        'high';

    public const SEVERITY_CRITICAL =
        'critical';

    protected $fillable = [
        'uuid',
        'reference',
        'title',
        'severity',
        'status',
        'reported_by',
        'assigned_to',
        'detected_at',
        'occurred_at',
        'contained_at',
        'reported_to_authority_at',
        'closed_at',
        'affected_subject_count',
        'data_categories',
        'systems_affected',
        'summary',
        'containment_actions',
        'notification_decision',
        'authority_reference',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'occurred_at' => 'datetime',
            'contained_at' => 'datetime',
            'reported_to_authority_at' => 'datetime',
            'closed_at' => 'datetime',

            'affected_subject_count' => 'integer',

            'data_categories' => 'array',
            'systems_affected' => 'array',

            'summary' => 'encrypted',
            'containment_actions' => 'encrypted',
            'notification_decision' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $breach): void {
                $breach->uuid ??=
                    (string) Str::uuid7();

                $breach->reference ??=
                    sprintf(
                        'BR-%s-%s',
                        now()->format('Y'),
                        Str::upper(
                            Str::random(10)
                        )
                    );
            }
        );

        static::deleting(function (): never {
            throw new LogicException(
                'Breach records are compliance evidence and cannot be deleted.'
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_INVESTIGATING,
            self::STATUS_CONTAINED,
            self::STATUS_CLOSED,
        ];
    }

    public static function severities(): array
    {
        return [
            self::SEVERITY_LOW,
            self::SEVERITY_MEDIUM,
            self::SEVERITY_HIGH,
            self::SEVERITY_CRITICAL,
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reported_by'
        );
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }
}
