<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellorQualification extends Model
{
    protected $fillable = [
        'counsellor_profile_id',
        'qualification',
        'institution',
        'field_of_study',
        'year_completed',
        'certificate_number',
    ];

    protected function casts(): array
    {
        return [
            'year_completed' => 'integer',
        ];
    }

    public function counsellorProfile(): BelongsTo
    {
        return $this->belongsTo(
            CounsellorProfile::class,
            'counsellor_profile_id',
            'id',
        );
    }
}
