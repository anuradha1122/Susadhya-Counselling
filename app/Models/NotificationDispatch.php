<?php

namespace App\Models;

use Database\Factories\NotificationDispatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NotificationDispatch extends Model
{
    /** @use HasFactory<NotificationDispatchFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'event_type',
        'template_key',
        'channels',
        'source_type',
        'source_id',
        'url',
        'payload',
        'deduplication_key',
        'dispatched_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (NotificationDispatch $dispatch): void {
            if (! $dispatch->id) {
                $dispatch->id = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'payload' => 'array',
            'dispatched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(
            NotificationDelivery::class,
            'notification_dispatch_id'
        );
    }

    public function template(): ?NotificationTemplate
    {
        return NotificationTemplate::query()
            ->where('key', $this->template_key)
            ->first();
    }
}
