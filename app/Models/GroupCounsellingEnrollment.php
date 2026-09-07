<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GroupCounsellingEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'program_id',
        'client_profile_id',
        'status',
        'enrolled_at',
        'approved_at',
        'cancelled_at',
        'client_note',
        'admin_note',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            $enrollment->uuid ??= (string) Str::uuid();
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

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
