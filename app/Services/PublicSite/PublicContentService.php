<?php

namespace App\Services\PublicSite;

use App\Models\CmsFaq;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTestimonial;
use App\Models\CounsellingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PublicContentService
{
    public function __construct(
        private readonly PublicCounsellorService $counsellors,
        private readonly PublicSiteDataService $siteData
    ) {}

    public function page(
        string $slug
    ): CmsPage {
        return CmsPage::query()
            ->published()
            ->where(
                'slug',
                $slug
            )
            ->with([
                'activeSections' => fn ($query) => $query
                    ->orderBy(
                        'display_order'
                    )
                    ->orderBy('id'),
            ])
            ->firstOrFail();
    }

    public function transformPage(
        CmsPage $page
    ): array {
        return [
            'uuid' => $page->uuid,

            'title' => $page->title,

            'slug' => $page->slug,

            'excerpt' => $page->excerpt,

            'body' => $page->body,

            'template' => $page->template,

            'sections' => $page
                ->activeSections
                ->map(
                    fn (
                        CmsSection $section
                    ): array => $this
                        ->transformSection(
                            $section
                        )
                )
                ->values()
                ->all(),

            'seo' => $this->seoForPage(
                $page
            ),
        ];
    }

    public function services(
        ?string $search = null,
        ?string $mode = null,
        ?int $limit = null
    ): Builder {
        $query =
            CounsellingService::query();

        if (
            Schema::hasColumn(
                'counselling_services',
                'status'
            )
        ) {
            $query->whereIn(
                'status',
                config(
                    'public_site.service_statuses',
                    [
                        'active',
                        'published',
                    ]
                )
            );
        }

        if (
            Schema::hasColumn(
                'counselling_services',
                'archived_at'
            )
        ) {
            $query->whereNull(
                'archived_at'
            );
        }

        if (filled($search)) {
            $query->where(
                function (
                    Builder $query
                ) use ($search): void {
                    $query
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'short_description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            filled($mode)
            &&
            Schema::hasColumn(
                'counselling_services',
                'service_mode'
            )
        ) {
            $query->where(
                'service_mode',
                $mode
            );
        }

        $query
            ->orderBy(
                'display_order'
            )
            ->orderBy('name');

        if ($limit !== null) {
            $query->limit(
                $limit
            );
        }

        return $query;
    }

    public function transformService(
        CounsellingService $service
    ): array {
        return [
            'slug' => $service->slug,

            'name' => $service->name,

            'short_description' => $service
                ->short_description,

            'description' => $service->description,

            'duration_minutes' => $service
                ->duration_minutes,

            'service_mode' => $service
                ->service_mode,

            'target_age_group' => $service
                ->target_age_group,

            'minimum_age' => $service->minimum_age,

            'maximum_age' => $service->maximum_age,

            'price' => $service->price,

            'currency' => $service->currency,
        ];
    }

    public function seoForPage(
        CmsPage $page
    ): array {
        $defaults =
            $this->siteData
                ->seoDefaults();

        return [
            'title' => $page->meta_title
                ?: $page->title,

            'description' => $page->meta_description
                ?: $page->excerpt
                ?: $defaults[
                    'description'
                ],

            'canonical' => $page->canonical_url
                ?: $this
                    ->canonicalForPage(
                        $page
                    ),

            'og_title' => $page->og_title
                ?: $page->meta_title
                ?: $page->title,

            'og_description' => $page->og_description
                ?: $page->meta_description
                ?: $page->excerpt
                ?: $defaults[
                    'description'
                ],

            'og_image' => $this->siteData
                ->assetUrl(
                    $page->og_image_path
                )
                ?: $defaults['image'],

            'robots' => sprintf(
                '%s,%s',
                $page->robots_index
                    ? 'index'
                    : 'noindex',
                $page->robots_follow
                    ? 'follow'
                    : 'nofollow'
            ),
        ];
    }

    private function transformSection(
        CmsSection $section
    ): array {
        $content =
            $section->content ?? [];

        $payload =
            match ($section->type) {
                CmsSection::TYPE_SERVICES => $this->serviceSection(
                    $content
                ),

                CmsSection::TYPE_COUNSELLORS => $this->counsellorSection(
                    $content
                ),

                CmsSection::TYPE_FAQ => $this->faqSection(
                    $content
                ),

                CmsSection::TYPE_TESTIMONIALS => $this->testimonialSection(
                    $content
                ),

                default => null,
            };

        return [
            'uuid' => $section->uuid,

            'key' => $section->key,

            'type' => $section->type,

            'heading' => $section->heading,

            'subheading' => $section->subheading,

            'content' => $content,

            'settings' => $section->settings ?? [],

            'payload' => $payload,
        ];
    }

    private function serviceSection(
        array $content
    ): array {
        $limit =
            (int) (
                $content['limit']
                ?? config(
                    'public_site.home_services_limit',
                    6
                )
            );

        return $this
            ->services(
                limit: max(
                    1,
                    min(
                        $limit,
                        12
                    )
                )
            )
            ->get()
            ->map(
                fn (
                    CounsellingService $service
                ): array => $this
                    ->transformService(
                        $service
                    )
            )
            ->values()
            ->all();
    }

    private function counsellorSection(
        array $content
    ): array {
        $limit =
            (int) (
                $content['limit']
                ?? config(
                    'public_site.home_counsellors_limit',
                    4
                )
            );

        return $this
            ->counsellors
            ->featured(
                max(
                    1,
                    min(
                        $limit,
                        12
                    )
                )
            );
    }

    private function faqSection(
        array $content
    ): array {
        $limit =
            (int) (
                $content['limit']
                ?? config(
                    'public_site.home_faq_limit',
                    6
                )
            );

        return CmsFaq::query()
            ->visible()
            ->orderBy(
                'display_order'
            )
            ->limit(
                max(
                    1,
                    min(
                        $limit,
                        20
                    )
                )
            )
            ->get([
                'uuid',
                'category',
                'question',
                'answer',
            ])
            ->toArray();
    }

    private function testimonialSection(
        array $content
    ): array {
        $limit =
            (int) (
                $content['limit']
                ?? config(
                    'public_site.home_testimonials_limit',
                    6
                )
            );

        return CmsTestimonial::query()
            ->visible()
            ->orderByDesc(
                'is_featured'
            )
            ->orderBy(
                'display_order'
            )
            ->limit(
                max(
                    1,
                    min(
                        $limit,
                        12
                    )
                )
            )
            ->get([
                'uuid',
                'display_name',
                'role_label',
                'quote',
                'rating',
                'image_path',
            ])
            ->map(
                function (
                    CmsTestimonial $testimonial
                ): array {
                    return [
                        'uuid' => $testimonial->uuid,

                        'display_name' => $testimonial
                            ->display_name,

                        'role_label' => $testimonial
                            ->role_label,

                        'quote' => $testimonial->quote,

                        'rating' => $testimonial->rating,

                        'image_url' => $this
                            ->siteData
                            ->assetUrl(
                                $testimonial
                                    ->image_path
                            ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function canonicalForPage(
        CmsPage $page
    ): string {
        return match (
            $page->slug
        ) {
            'home' => route(
                'public.home'
            ),

            'about' => route(
                'public.about'
            ),

            'contact' => route(
                'public.contact'
            ),

            default => route(
                'public.page.show',
                $page->slug
            ),
        };
    }
}
