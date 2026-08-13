<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CounsellorAvailabilityRule extends Model
{
    use HasFactory;

    public const MODE_ONLINE = 'online';

    public const MODE_IN_PERSON = 'in_person';

    public const MODE_BOTH = 'both';

    public const SUNDAY = 0;

    public const MONDAY = 1;

    public const TUESDAY = 2;

    public const WEDNESDAY = 3;

    public const THURSDAY = 4;

    public const FRIDAY = 5;

    public const SATURDAY = 6;

    protected $fillable = [
        'counsellor_profile_id',
        'day_of_week',
        'start_time',
        'end_time',
        'mode',
        'slot_duration_minutes',
        'buffer_minutes',
        'capacity_per_slot',
        'timezone',
        'effective_from',
        'effective_until',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'slot_duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'capacity_per_slot' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public static function modes(): array
    {
        return [
            self::MODE_ONLINE,
            self::MODE_IN_PERSON,
            self::MODE_BOTH,
        ];
    }

    public static function days(): array
    {
        return [
            self::SUNDAY => 'Sunday',
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
        ];
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(CounsellorAvailabilityBreak::class);
    }

    public function activeBreaks(): HasMany
    {
        return $this->breaks()->where('is_active', true);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCounsellorProfile(
        Builder $query,
        CounsellorProfile $counsellorProfile
    ): Builder {
        return $query->where('counsellor_profile_id', $counsellorProfile->id);
    }

    public function scopeForDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function overlaps(string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return self::query()
            ->where('counsellor_profile_id', $this->counsellor_profile_id)
            ->where('day_of_week', $this->day_of_week)
            ->where('is_active', true)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
