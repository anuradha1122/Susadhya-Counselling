<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'display_order',
        'status',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(CounsellingService::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by'
        );
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function hasActiveServices(): bool
    {
        return $this->services()
            ->where('status', 'active')
            ->exists();
    }
}
