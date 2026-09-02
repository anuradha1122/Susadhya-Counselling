<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalNoteVersion extends Model
{
    protected $fillable = [
        'clinical_note_id',
        'version',
        'title',
        'note',
        'formulation',
        'intervention',
        'risk_assessment',
        'plan',
        'risk_level',
        'status',
        'changed_by',
        'change_reason',
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

    public function clinicalNote(): BelongsTo
    {
        return $this->belongsTo(ClinicalNote::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
