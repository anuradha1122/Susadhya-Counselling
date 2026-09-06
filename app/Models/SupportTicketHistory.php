<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SupportTicketHistory extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'support_ticket_id',
        'actor_id',
        'event',
        'from_status',
        'to_status',
        'from_owner_id',
        'to_owner_id',
        'from_priority',
        'to_priority',
        'note',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
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

    protected static function booted(): void
    {
        static::updating(
            function (): never {
                throw new LogicException(
                    'Support ticket history is immutable.'
                );
            }
        );

        static::deleting(
            function (): never {
                throw new LogicException(
                    'Support ticket history is immutable.'
                );
            }
        );
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(
            SupportTicket::class,
            'support_ticket_id'
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }

    public function fromOwner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'from_owner_id'
        );
    }

    public function toOwner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'to_owner_id'
        );
    }
}
