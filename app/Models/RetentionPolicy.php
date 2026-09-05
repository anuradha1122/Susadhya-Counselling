<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class RetentionPolicy extends Model
{
    use HasFactory;

    public const ACTION_REVIEW = 'review';

    public const ACTION_ANONYMIZE = 'anonymize';

    public const ACTION_DELETE = 'delete';

    public const CATEGORY_CLINICAL =
        'clinical_records';

    public const CATEGORY_FINANCIAL =
        'financial_records';

    public const CATEGORY_DOCUMENTS =
        'documents';

    public const CATEGORY_NOTIFICATIONS =
        'notifications';

    public const CATEGORY_AUDIT =
        'audit_logs';

    public const CATEGORY_OPERATIONAL =
        'operational_records';

    protected $fillable = [
        'uuid',
        'category',
        'name',
        'description',
        'retention_days',
        'action',
        'enabled',
        'automatic_execution',
        'legal_basis',
        'updated_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'enabled' => 'boolean',
            'automatic_execution' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $policy): void {
                $policy->uuid ??=
                    (string) Str::uuid7();
            }
        );

        static::deleting(function (): never {
            throw new LogicException(
                'Retention policies must be disabled, not deleted.'
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_CLINICAL,
            self::CATEGORY_FINANCIAL,
            self::CATEGORY_DOCUMENTS,
            self::CATEGORY_NOTIFICATIONS,
            self::CATEGORY_AUDIT,
            self::CATEGORY_OPERATIONAL,
        ];
    }

    public static function actions(): array
    {
        return [
            self::ACTION_REVIEW,
            self::ACTION_ANONYMIZE,
            self::ACTION_DELETE,
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function runs(): HasMany
    {
        return $this->hasMany(
            RetentionRun::class
        );
    }
}
