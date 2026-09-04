<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;
    use HasUuids;

    public const METHOD_GATEWAY = 'gateway';

    public const METHOD_MANUAL = 'manual';

    public const METHOD_WAIVED = 'waived';

    public const PROVIDER_MANUAL = 'manual';

    public const PROVIDER_SYSTEM = 'system';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    public const STATUS_REFUNDED = 'refunded';

    public const RECONCILIATION_PENDING = 'pending';

    public const RECONCILIATION_MATCHED = 'matched';

    public const RECONCILIATION_MISMATCH = 'mismatch';

    public const RECONCILIATION_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'appointment_id',
        'client_profile_id',
        'provider',
        'method',
        'provider_payment_id',
        'provider_reference',
        'idempotency_key',
        'amount',
        'currency',
        'status',
        'provider_status',
        'provider_amount',
        'provider_currency',
        'reconciliation_status',
        'provider_synced_at',
        'paid_at',
        'failed_at',
        'reconciled_at',
        'failure_code',
        'failure_message',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_amount' => 'decimal:2',
            'provider_synced_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'reconciled_at' => 'datetime',
            'metadata' => 'array',
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

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_PAID,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_PARTIALLY_REFUNDED,
            self::STATUS_REFUNDED,
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(
            Appointment::class
        );
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(
            ClientProfile::class
        );
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(
            Invoice::class
        );
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(
            Refund::class
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(
            PaymentEvent::class
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function isPaid(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PAID,
                self::STATUS_PARTIALLY_REFUNDED,
                self::STATUS_REFUNDED,
            ],
            true
        );
    }

    public function canRequestRefund(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PAID,
                self::STATUS_PARTIALLY_REFUNDED,
            ],
            true
        );
    }
}
