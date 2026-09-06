<?php

namespace App\Models;

use Database\Factories\SessionFeedbackFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionFeedback extends Model
{
    /** @use HasFactory<SessionFeedbackFactory> */
    use HasFactory;

    use HasUuids;

    protected $table =
        'session_feedback';

    protected $fillable = [
        'appointment_id',
        'client_profile_id',
        'overall_rating',
        'technical_rating',
        'comment',
        'would_recommend',
        'consent_to_follow_up',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_rating' => 'integer',
            'technical_rating' => 'integer',
            'would_recommend' => 'boolean',
            'consent_to_follow_up' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return [
            'uuid',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(
            Appointment::class
        );
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(
            ClientProfile::class
        );
    }
}
