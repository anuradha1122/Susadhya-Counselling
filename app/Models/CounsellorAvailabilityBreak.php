<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellorAvailabilityBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'counsellor_availability_rule_id',
        'title',
        'start_time',
        'end_time',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }

    public function availabilityRule(): BelongsTo
    {
        return $this->belongsTo(
            CounsellorAvailabilityRule::class,
            'counsellor_availability_rule_id'
        );
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

    public function overlaps(string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return self::query()
            ->where('counsellor_availability_rule_id', $this->counsellor_availability_rule_id)
            ->where('is_active', true)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
