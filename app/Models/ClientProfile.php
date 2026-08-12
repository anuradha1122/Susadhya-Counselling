<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'preferred_name',
        'date_of_birth',
        'gender',
        'pronouns',
        'alternate_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'province',
        'postal_code',
        'preferred_language',
        'preferred_contact_method',
        'occupation',
        'marital_status',
        'profile_completed_at',
        'terms_accepted_at',
        'privacy_policy_accepted_at',
        'communication_consent',
        'communication_consent_at',
        'emergency_contact_permission',
        'emergency_contact_permission_at',
        'privacy_preferences',
        'status',
        'created_by',
        'updated_by',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date:Y-m-d',
            'profile_completed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_policy_accepted_at' => 'datetime',
            'communication_consent' => 'boolean',
            'communication_consent_at' => 'datetime',
            'emergency_contact_permission' => 'boolean',
            'emergency_contact_permission_at' => 'datetime',
            'privacy_preferences' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(ClientEmergencyContact::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(ClientPreference::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(ClientConsent::class);
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

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by',
        );
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function completionMissingFields(): array
    {
        $missing = [];

        if (blank($this->first_name)) {
            $missing[] = 'first_name';
        }

        if (blank($this->last_name)) {
            $missing[] = 'last_name';
        }

        if (blank($this->date_of_birth)) {
            $missing[] = 'date_of_birth';
        }

        if (blank($this->preferred_language)) {
            $missing[] = 'preferred_language';
        }

        if (blank($this->preferred_contact_method)) {
            $missing[] = 'preferred_contact_method';
        }

        if (! $this->terms_accepted_at) {
            $missing[] = 'terms_acceptance';
        }

        if (! $this->privacy_policy_accepted_at) {
            $missing[] = 'privacy_policy_acceptance';
        }

        if (! $this->relationLoaded('user')) {
            $this->load('user');
        }

        if (blank($this->user?->phone)) {
            $missing[] = 'phone';
        }

        if (! $this->relationLoaded('emergencyContacts')) {
            $this->load('emergencyContacts');
        }

        if (
            $this->emergency_contact_permission
            && $this->emergencyContacts->where('may_contact_in_emergency', true)->isEmpty()
        ) {
            $missing[] = 'emergency_contact';
        }

        return $missing;
    }

    public function isComplete(): bool
    {
        return $this->completionMissingFields() === [];
    }

    public function refreshCompletionStatus(): void
    {
        $completedAt = $this->isComplete()
            ? ($this->profile_completed_at ?? now())
            : null;

        $this->forceFill([
            'profile_completed_at' => $completedAt,
        ])->save();
    }
}
