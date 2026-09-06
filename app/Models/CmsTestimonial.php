<?php

namespace App\Models;

use Database\Factories\CmsTestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CmsTestimonial extends Model
{
    /** @use HasFactory<CmsTestimonialFactory> */
    use HasFactory;

    protected $fillable = [
        'display_name',
        'role_label',
        'quote',
        'rating',
        'image_path',
        'is_featured',
        'is_active',
        'display_order',
        'consent_confirmed',
        'consent_confirmed_at',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                CmsTestimonial $testimonial
            ): void {
                $testimonial->uuid ??=
                    (string) Str::uuid();
            }
        );
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'consent_confirmed' => 'boolean',
            'consent_confirmed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeVisible(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'is_active',
                true
            )
            ->where(
                'consent_confirmed',
                true
            )
            ->where(
                function (
                    Builder $query
                ): void {
                    $query
                        ->whereNull(
                            'published_at'
                        )
                        ->orWhere(
                            'published_at',
                            '<=',
                            now()
                        );
                }
            );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
