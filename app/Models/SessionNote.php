<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionNote extends Model
{
    use HasFactory;

    public const TYPE_PROGRESS = 'progress';

    public const TYPE_RISK = 'risk';

    public const TYPE_FOLLOW_UP = 'follow_up';

    public const TYPE_ADMIN = 'admin';

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_COUNSELLOR_TEAM = 'counsellor_team';

    public const VISIBILITY_ADMIN = 'admin';

    public const VISIBILITY_CLIENT = 'client';

    protected $fillable = [
        'counselling_session_id',
        'author_id',
        'note_type',
        'visibility',
        'content',
    ];

    public static function noteTypes(): array
    {
        return [
            self::TYPE_PROGRESS,
            self::TYPE_RISK,
            self::TYPE_FOLLOW_UP,
            self::TYPE_ADMIN,
        ];
    }

    public static function visibilities(): array
    {
        return [
            self::VISIBILITY_PRIVATE,
            self::VISIBILITY_COUNSELLOR_TEAM,
            self::VISIBILITY_ADMIN,
            self::VISIBILITY_CLIENT,
        ];
    }

    public function counsellingSession(): BelongsTo
    {
        return $this->belongsTo(CounsellingSession::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
