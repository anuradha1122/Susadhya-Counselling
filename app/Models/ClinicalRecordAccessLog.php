<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRecordAccessLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'client_case_id',
        'record_type',
        'record_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function clientCase(): BelongsTo
    {
        return $this->belongsTo(ClientCase::class);
    }
}
