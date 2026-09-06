<?php

namespace App\Models;

use Database\Factories\CmsMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsMedia extends Model
{
    /** @use HasFactory<CmsMediaFactory> */
    use HasFactory;

    protected $table = 'cms_media';

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum',
        'alt_text',
        'width',
        'height',
        'is_active',
        'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                CmsMedia $media
            ): void {
                $media->uuid ??=
                    (string) Str::uuid();
            }
        );
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function publicUrl(): string
    {
        return Storage::disk(
            $this->disk
        )->url(
            $this->path
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}
