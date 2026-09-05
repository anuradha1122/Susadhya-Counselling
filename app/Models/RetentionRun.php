<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class RetentionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'retention_policy_id',
        'initiated_by',
        'mode',
        'status',
        'candidate_count',
        'processed_count',
        'skipped_count',
        'failed_count',
        'summary',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'candidate_count' => 'integer',
            'processed_count' => 'integer',
            'skipped_count' => 'integer',
            'failed_count' => 'integer',
            'summary' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $run): void {
                $run->uuid ??=
                    (string) Str::uuid7();

                $run->started_at ??=
                    now();
            }
        );

        static::deleting(function (): never {
            throw new LogicException(
                'Retention run history is compliance evidence and cannot be deleted.'
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(
            RetentionPolicy::class,
            'retention_policy_id'
        );
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'initiated_by'
        );
    }
}
