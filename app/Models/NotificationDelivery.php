<?php

namespace App\Models;

use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'notification_dispatch_id',
        'channel',
        'status',
        'attempt',
        'attempted_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'provider_message_id',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(
            NotificationDispatch::class,
            'notification_dispatch_id'
        );
    }

    public function markAttempting(): void
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'attempted_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markSent(?string $providerMessageId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'provider_message_id' => $providerMessageId,
            'error_message' => null,
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $message,
        ]);
    }

    public function markSkipped(string $message): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'error_message' => $message,
        ]);
    }

    public function markUnavailable(string $message): void
    {
        $this->update([
            'status' => self::STATUS_UNAVAILABLE,
            'error_message' => $message,
        ]);
    }
}
