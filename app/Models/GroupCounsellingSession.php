<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GroupCounsellingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'program_id',
        'counsellor_profile_id',
        'title',
        'starts_at',
        'ends_at',
        'meeting_url',
        'location',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            $session->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(GroupCounsellingProgram::class, 'program_id');
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class, 'counsellor_profile_id');
    }
}
