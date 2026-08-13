<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellorBlockedSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'counsellor_profile_id',
        'blocked_date',
        'start_time',
        'end_time',
        'is_full_day',
        'reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_full_day' => 'boolean',
        ];
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForCounsellorProfile(
        Builder $query,
        CounsellorProfile $counsellorProfile
    ): Builder {
        return $query->where('counsellor_profile_id', $counsellorProfile->id);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('blocked_date', $date);
    }

    public function overlaps(string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return self::query()
            ->where('counsellor_profile_id', $this->counsellor_profile_id)
            ->whereDate('blocked_date', $date)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where(function (Builder $query) use ($startTime, $endTime): void {
                $query
                    ->where('is_full_day', true)
                    ->orWhere(function (Builder $query) use ($startTime, $endTime): void {
                        $query
                            ->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
            })
            ->exists();
    }
}
