<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'name',
        'relationship',
        'phone',
        'alternate_phone',
        'email',
        'may_contact_in_emergency',
        'is_primary',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'may_contact_in_emergency' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

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
