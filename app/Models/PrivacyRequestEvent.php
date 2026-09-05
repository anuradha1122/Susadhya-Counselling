<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class PrivacyRequestEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'privacy_request_id',
        'actor_id',
        'event',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'notes' => 'encrypted',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $event): void {
                $event->uuid ??=
                    (string) Str::uuid7();
            }
        );

        static::updating(function (): never {
            throw new LogicException(
                'Privacy request events are immutable.'
            );
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Privacy request events cannot be deleted through Eloquent.'
            );
        });
    }

    public function privacyRequest(): BelongsTo
    {
        return $this->belongsTo(
            PrivacyRequest::class
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }
}
