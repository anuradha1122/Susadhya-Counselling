<?php

namespace App\Models;

use Database\Factories\CmsSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CmsSection extends Model
{
    /** @use HasFactory<CmsSectionFactory> */
    use HasFactory;

    public const TYPE_HERO = 'hero';

    public const TYPE_RICH_TEXT = 'rich_text';

    public const TYPE_FEATURE_GRID = 'feature_grid';

    public const TYPE_STATS = 'stats';

    public const TYPE_STEPS = 'steps';

    public const TYPE_IMAGE_TEXT = 'image_text';

    public const TYPE_SERVICES = 'services';

    public const TYPE_COUNSELLORS = 'counsellors';

    public const TYPE_FAQ = 'faq';

    public const TYPE_TESTIMONIALS = 'testimonials';

    public const TYPE_CTA = 'cta';

    public const TYPE_CONTACT = 'contact';

    protected $fillable = [
        'cms_page_id',
        'key',
        'type',
        'heading',
        'subheading',
        'content',
        'settings',
        'display_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                CmsSection $section
            ): void {
                $section->uuid ??=
                    (string) Str::uuid();
            }
        );
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function types(): array
    {
        return [
            self::TYPE_HERO,
            self::TYPE_RICH_TEXT,
            self::TYPE_FEATURE_GRID,
            self::TYPE_STATS,
            self::TYPE_STEPS,
            self::TYPE_IMAGE_TEXT,
            self::TYPE_SERVICES,
            self::TYPE_COUNSELLORS,
            self::TYPE_FAQ,
            self::TYPE_TESTIMONIALS,
            self::TYPE_CTA,
            self::TYPE_CONTACT,
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(
            CmsPage::class,
            'cms_page_id'
        );
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
