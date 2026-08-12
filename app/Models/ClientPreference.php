<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'preferred_counselling_mode',
        'preferred_counsellor_gender',
        'preferred_language',
        'general_availability_notes',
        'accessibility_requirements',
        'additional_preferences',
        'created_by',
        'updated_by',
    ];

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
        );
    }
}
