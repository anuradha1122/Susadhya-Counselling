<?php

namespace App\Models;

use Database\Factories\PublicSiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicSiteSetting extends Model
{
    /** @use HasFactory<PublicSiteSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'site_name',
        'tagline',
        'logo_path',
        'favicon_path',
        'contact_email',
        'contact_phone',
        'whatsapp_number',
        'address',
        'office_hours',
        'social_links',
        'default_meta_title',
        'default_meta_description',
        'default_og_image_path',
        'footer_text',
        'emergency_notice',
        'booking_cta_label',
        'booking_cta_url',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                PublicSiteSetting $settings
            ): void {
                $settings->uuid ??=
                    (string) Str::uuid();
            }
        );
    }

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
