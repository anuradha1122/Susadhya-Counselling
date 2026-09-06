<?php

namespace App\Models;

use Database\Factories\CmsFaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CmsFaq extends Model
{
    /** @use HasFactory<CmsFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'display_order',
        'is_active',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (CmsFaq $faq): void {
                $faq->uuid ??=
                    (string) Str::uuid();
            }
        );
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
