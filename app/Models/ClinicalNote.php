<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalNote extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SIGNED = 'signed';

    protected $fillable = [
        'client_case_id',
        'counselling_session_id',
        'author_id',
        'title',
        'note',
        'formulation',
        'intervention',
        'risk_assessment',
        'plan',
        'risk_level',
        'status',
        'version',
        'signed_by',
        'signed_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'signed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function clientCase(): BelongsTo
    {
        return $this->belongsTo(ClientCase::class);
    }

    public function counsellingSession(): BelongsTo
    {
        return $this->belongsTo(CounsellingSession::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ClinicalNoteVersion::class)
            ->orderByDesc('version');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null
            || $this->status === self::STATUS_SIGNED;
    }
}
