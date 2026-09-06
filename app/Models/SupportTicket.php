<?php

namespace App\Models;

use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory;

    use HasUuids;

    public const CATEGORY_GENERAL =
        'general';

    public const CATEGORY_TECHNICAL =
        'technical';

    public const CATEGORY_APPOINTMENT =
        'appointment';

    public const CATEGORY_ACCOUNT =
        'account';

    public const CATEGORY_COMPLAINT =
        'complaint';

    public const PRIORITY_LOW =
        'low';

    public const PRIORITY_NORMAL =
        'normal';

    public const PRIORITY_HIGH =
        'high';

    public const STATUS_OPEN =
        'open';

    public const STATUS_IN_PROGRESS =
        'in_progress';

    public const STATUS_WAITING_ON_CLIENT =
        'waiting_on_client';

    public const STATUS_RESOLVED =
        'resolved';

    public const STATUS_CLOSED =
        'closed';

    protected $fillable = [
        'requester_id',
        'guest_name',
        'guest_email',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'owner_id',
        'resolved_at',
        'closed_at',
        'last_activity_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_activity_at' => 'datetime',
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

    public static function categories(): array
    {
        return [
            self::CATEGORY_GENERAL,
            self::CATEGORY_TECHNICAL,
            self::CATEGORY_APPOINTMENT,
            self::CATEGORY_ACCOUNT,
            self::CATEGORY_COMPLAINT,
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_NORMAL,
            self::PRIORITY_HIGH,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_ON_CLIENT,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requester_id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id'
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

    public function replies(): HasMany
    {
        return $this->hasMany(
            SupportTicketReply::class
        );
    }

    public function histories(): HasMany
    {
        return $this
            ->hasMany(
                SupportTicketHistory::class
            )
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function isClosed(): bool
    {
        return $this->status
            === self::STATUS_CLOSED;
    }

    public function isResolved(): bool
    {
        return $this->status
            === self::STATUS_RESOLVED;
    }

    public function canTransitionTo(
        string $status
    ): bool {
        if ($status === $this->status) {
            return true;
        }

        $transitions = [
            self::STATUS_OPEN => [
                self::STATUS_IN_PROGRESS,
                self::STATUS_WAITING_ON_CLIENT,
                self::STATUS_RESOLVED,
            ],

            self::STATUS_IN_PROGRESS => [
                self::STATUS_OPEN,
                self::STATUS_WAITING_ON_CLIENT,
                self::STATUS_RESOLVED,
            ],

            self::STATUS_WAITING_ON_CLIENT => [
                self::STATUS_OPEN,
                self::STATUS_IN_PROGRESS,
                self::STATUS_RESOLVED,
            ],

            self::STATUS_RESOLVED => [
                self::STATUS_OPEN,
                self::STATUS_CLOSED,
            ],

            self::STATUS_CLOSED => [
                self::STATUS_OPEN,
            ],
        ];

        return in_array(
            $status,
            $transitions[$this->status]
                ?? [],
            true
        );
    }
}
