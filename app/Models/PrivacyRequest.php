<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class PrivacyRequest extends Model
{
    use HasFactory;

    public const TYPE_ACCESS = 'access';

    public const TYPE_EXPORT = 'export';

    public const TYPE_CORRECTION = 'correction';

    public const TYPE_DELETION = 'deletion';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_IDENTITY_VERIFIED =
        'identity_verified';

    public const STATUS_UNDER_REVIEW =
        'under_review';

    public const STATUS_APPROVED =
        'approved';

    public const STATUS_REJECTED =
        'rejected';

    public const STATUS_EXPORT_READY =
        'export_ready';

    public const STATUS_COMPLETED =
        'completed';

    public const STATUS_CANCELLED =
        'cancelled';

    protected $fillable = [
        'uuid',
        'requester_user_id',
        'subject_user_id',
        'type',
        'status',
        'request_details',
        'scope',
        'submitted_at',
        'identity_verified_at',
        'identity_verified_by',
        'reviewed_at',
        'reviewed_by',
        'decision',
        'review_notes',
        'legal_basis',
        'due_at',
        'export_disk',
        'export_path',
        'export_checksum',
        'export_prepared_at',
        'deletion_strategy',
        'execution_notes',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'request_details' => 'encrypted',
            'review_notes' => 'encrypted',
            'execution_notes' => 'encrypted',
            'scope' => 'array',
            'submitted_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'due_at' => 'datetime',
            'export_prepared_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (self $request): void {
                $request->uuid ??=
                    (string) Str::uuid7();

                $request->submitted_at ??=
                    now();
            }
        );

        static::deleting(function (): never {
            throw new LogicException(
                'Privacy requests are compliance records and cannot be deleted.'
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function types(): array
    {
        return [
            self::TYPE_ACCESS,
            self::TYPE_EXPORT,
            self::TYPE_CORRECTION,
            self::TYPE_DELETION,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_IDENTITY_VERIFIED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_EXPORT_READY,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requester_user_id'
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'subject_user_id'
        );
    }

    public function identityVerifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'identity_verified_by'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by'
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(
            PrivacyRequestEvent::class
        )->oldest('created_at');
    }
}
