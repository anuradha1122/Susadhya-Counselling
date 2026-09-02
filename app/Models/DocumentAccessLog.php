<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'secure_document_id',
        'document_uuid',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_id'
        );
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            SecureDocument::class,
            'secure_document_id'
        );
    }
}
