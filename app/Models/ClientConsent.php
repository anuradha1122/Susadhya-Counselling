<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientConsent extends Model
{
    use HasFactory;

    public const TYPE_TERMS = 'terms';

    public const TYPE_PRIVACY_POLICY = 'privacy_policy';

    public const TYPE_COMMUNICATION = 'communication';

    public const TYPE_EMERGENCY_CONTACT = 'emergency_contact';

    public const CURRENT_TERMS_VERSION = 'module-05-initial';

    public const CURRENT_PRIVACY_POLICY_VERSION = 'module-05-initial';

    protected $fillable = [
        'client_profile_id',
        'consent_type',
        'version',
        'accepted_at',
        'ip_address',
        'user_agent',
        'metadata',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by',
        );
    }
}
