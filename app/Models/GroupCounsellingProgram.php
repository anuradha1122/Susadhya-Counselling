<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GroupCounsellingProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'counselling_service_id',
        'lead_counsellor_profile_id',
        'title',
        'slug',
        'description',
        'mode',
        'location',
        'capacity',
        'starts_on',
        'ends_on',
        'price',
        'requires_approval',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'price' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $program): void {
            $program->uuid ??= (string) Str::uuid();
            $program->slug = Str::slug($program->slug ?: $program->title);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(CounsellingService::class, 'counselling_service_id');
    }

    public function leadCounsellorProfile(): BelongsTo
    {
        return $this->belongsTo(CounsellorProfile::class, 'lead_counsellor_profile_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GroupCounsellingSession::class, 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(GroupCounsellingEnrollment::class, 'program_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
