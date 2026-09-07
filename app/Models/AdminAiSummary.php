<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminAiSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'created_by',
        'reviewed_by',
        'source_type',
        'source_label',
        'title',
        'source_text',
        'summary',
        'risk_flags',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'risk_flags' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $summary): void {
            $summary->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
