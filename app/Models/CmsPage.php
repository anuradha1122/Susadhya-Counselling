<?php

namespace App\Models;

use Database\Factories\CmsPageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    /** @use HasFactory<CmsPageFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const TEMPLATE_STANDARD = 'standard';

    public const TEMPLATE_LANDING = 'landing';

    public const TEMPLATE_LEGAL = 'legal';

    protected $fillable = [
        'title',
        'slug',
        'menu_label',
        'excerpt',
        'body',
        'template',
        'status',
        'show_in_header',
        'show_in_footer',
        'menu_order',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_path',
        'robots_index',
        'robots_follow',
        'published_at',
        'published_by',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (CmsPage $page): void {
                $page->uuid ??=
                    (string) Str::uuid();

                if (
                    blank($page->slug)
                    && filled($page->title)
                ) {
                    $page->slug =
                        Str::slug(
                            $page->title
                        );
                }
            }
        );
    }

    protected function casts(): array
    {
        return [
            'show_in_header' => 'boolean',

            'show_in_footer' => 'boolean',

            'robots_index' => 'boolean',

            'robots_follow' => 'boolean',

            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_ARCHIVED,
        ];
    }

    public static function templates(): array
    {
        return [
            self::TEMPLATE_STANDARD,
            self::TEMPLATE_LANDING,
            self::TEMPLATE_LEGAL,
        ];
    }

    public function scopePublished(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                self::STATUS_PUBLISHED
            )
            ->where(
                function (
                    Builder $query
                ): void {
                    $query
                        ->whereNull(
                            'published_at'
                        )
                        ->orWhere(
                            'published_at',
                            '<=',
                            now()
                        );
                }
            );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(
            CmsSection::class
        )
            ->orderBy(
                'display_order'
            );
    }

    public function activeSections(): HasMany
    {
        return $this
            ->sections()
            ->where(
                'is_active',
                true
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

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'published_by'
        );
    }
}
