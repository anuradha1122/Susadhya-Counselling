<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecureDocument extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const CATEGORY_CLIENT_UPLOAD =
        'client_upload';

    public const CATEGORY_CONSENT =
        'consent';

    public const CATEGORY_CLINICAL =
        'clinical';

    public const CATEGORY_REPORT =
        'report';

    public const CATEGORY_ADMINISTRATIVE =
        'administrative';

    public const CATEGORY_OTHER =
        'other';

    public const SCOPE_CLIENT =
        'client';

    public const SCOPE_CARE_TEAM =
        'care_team';

    public const SCOPE_CLINICAL =
        'clinical';

    public const SCOPE_SUPERVISOR =
        'supervisor';

    public const SCOPE_ADMIN =
        'admin';

    public const SCOPE_SHARED =
        'shared';

    public const SCAN_PENDING =
        'pending';

    public const SCAN_CLEAN =
        'clean';

    public const SCAN_UNAVAILABLE =
        'unavailable';

    public const SCAN_QUARANTINED =
        'quarantined';

    public const SCAN_FAILED =
        'failed';

    protected $fillable = [
        'client_profile_id',
        'client_case_id',
        'counselling_session_id',
        'uploaded_by',
        'category',
        'access_scope',
        'title',
        'original_name',
        'stored_name',
        'disk',
        'path',
        'mime_type',
        'extension',
        'size_bytes',
        'sha256',
        'scan_status',
        'scanner',
        'scan_message',
        'scanned_at',
        'quarantined_at',
        'metadata',
        'deleted_by',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'scanned_at' => 'datetime',
        'quarantined_at' => 'datetime',
        'metadata' => 'array',
    ];

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

    public static function categories(): array
    {
        return [
            self::CATEGORY_CLIENT_UPLOAD,
            self::CATEGORY_CONSENT,
            self::CATEGORY_CLINICAL,
            self::CATEGORY_REPORT,
            self::CATEGORY_ADMINISTRATIVE,
            self::CATEGORY_OTHER,
        ];
    }

    public static function scopes(): array
    {
        return [
            self::SCOPE_CLIENT,
            self::SCOPE_CARE_TEAM,
            self::SCOPE_CLINICAL,
            self::SCOPE_SUPERVISOR,
            self::SCOPE_ADMIN,
            self::SCOPE_SHARED,
        ];
    }

    public static function scanStatuses(): array
    {
        return [
            self::SCAN_PENDING,
            self::SCAN_CLEAN,
            self::SCAN_UNAVAILABLE,
            self::SCAN_QUARANTINED,
            self::SCAN_FAILED,
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(
            ClientProfile::class
        );
    }

    public function clientCase(): BelongsTo
    {
        return $this->belongsTo(
            ClientCase::class
        );
    }

    public function counsellingSession(): BelongsTo
    {
        return $this->belongsTo(
            CounsellingSession::class
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(
            DocumentAccessLog::class
        );
    }

    public function isQuarantined(): bool
    {
        return $this->scan_status
            === self::SCAN_QUARANTINED;
    }

    public function isDownloadBlocked(): bool
    {
        if (
            in_array(
                $this->scan_status,
                [
                    self::SCAN_QUARANTINED,
                    self::SCAN_FAILED,
                ],
                true
            )
        ) {
            return true;
        }

        if (
            in_array(
                $this->scan_status,
                [
                    self::SCAN_PENDING,
                    self::SCAN_UNAVAILABLE,
                ],
                true
            )
            && ! config(
                'documents.allow_unscanned_downloads',
                true
            )
        ) {
            return true;
        }

        return false;
    }
}
