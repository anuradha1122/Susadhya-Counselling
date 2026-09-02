<?php

namespace App\Models;

use Database\Factories\CounsellingServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CounsellingService extends Model
{
    /** @use HasFactory<CounsellingServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'duration_minutes',
        'service_mode',
        'target_age_group',
        'minimum_age',
        'maximum_age',
        'price',
        'currency',
        'display_order',
        'status',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'minimum_age' => 'integer',
            'maximum_age' => 'integer',
            'price' => 'decimal:2',
            'display_order' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ServiceCategory::class,
            'service_category_id'
        );
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by'
        );
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->whereHas(
                'category',
                fn (Builder $query) => $query
                    ->where('status', 'active')
            );
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
