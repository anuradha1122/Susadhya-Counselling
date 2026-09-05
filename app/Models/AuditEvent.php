<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class AuditEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid',
        'actor_id',
        'category',
        'event',
        'action',
        'subject_type',
        'subject_id',
        'subject_uuid',
        'result',
        'purpose_code',
        'reason',
        'metadata',
        'ip_address',
        'user_agent',
        'request_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $event): void {
                $event->uuid ??= (string) Str::uuid7();
                $event->occurred_at ??= now();
            }
        );

        static::updating(function (): never {
            throw new LogicException(
                'Audit events are immutable.'
            );
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Audit events cannot be deleted through Eloquent.'
            );
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }
}
