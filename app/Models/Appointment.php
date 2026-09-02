<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;
    use HasUuids;

    public const MODE_ONLINE = 'online';

    public const MODE_IN_PERSON = 'in_person';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_RESCHEDULED = 'rescheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'uuid',
        'client_profile_id',
        'counsellor_profile_id',
        'counselling_service_id',
        'appointment_date',
        'start_time',
        'end_time',
        'timezone',
        'mode',
        'status',
        'meeting_link',
        'location',
        'client_notes',
        'counsellor_notes',
        'admin_notes',
        'rescheduled_from_appointment_id',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'reminder_scheduled_at',
        'reminder_sent_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'cancelled_at' => 'datetime',
            'reminder_scheduled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public static function modes(): array
    {
        return [
            self::MODE_ONLINE,
            self::MODE_IN_PERSON,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_NO_SHOW,
        ];
    }

    public static function activeBookingStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ];
    }

    public static function terminalStatuses(): array
    {
        return [
            self::STATUS_RESCHEDULED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_NO_SHOW,
        ];
    }

    public function uniqueIds(): array
    {
        return [
            'uuid',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function counsellingService(): BelongsTo
    {
        return $this->belongsTo(CounsellingService::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function rescheduledFromAppointment(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'rescheduled_from_appointment_id',
        );
    }

    public function rescheduledAppointments(): HasMany
    {
        return $this->hasMany(
            self::class,
            'rescheduled_from_appointment_id',
        );
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActiveBooking(Builder $query): Builder
    {
        return $query->whereIn('status', self::activeBookingStatuses());
    }

    public function scopeForClientProfile(Builder $query, ClientProfile $clientProfile): Builder
    {
        return $query->where('client_profile_id', $clientProfile->id);
    }

    public function scopeForCounsellorProfile(
        Builder $query,
        CounsellorProfile $counsellorProfile
    ): Builder {
        return $query->where('counsellor_profile_id', $counsellorProfile->id);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    public function isOnline(): bool
    {
        return $this->mode === self::MODE_ONLINE;
    }

    public function isInPerson(): bool
    {
        return $this->mode === self::MODE_IN_PERSON;
    }

    public function isActiveBooking(): bool
    {
        return in_array($this->status, self::activeBookingStatuses(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::terminalStatuses(), true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ], true);
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ], true);
    }
}
