<?php

namespace App\Services\PublicSite;

use App\Models\CmsPage;
use App\Models\PublicSiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicSiteDataService
{
    public function settings(): array
    {
        $settings =
            PublicSiteSetting::query()
                ->first();

        if ($settings === null) {
            return $this->fallbackSettings();
        }

        return [
            'site_name' => $settings->site_name,

            'tagline' => $settings->tagline,

            'logo_url' => $this->assetUrl(
                $settings->logo_path
            ),

            'favicon_url' => $this->assetUrl(
                $settings->favicon_path
            ),

            'contact_email' => $settings->contact_email,

            'contact_phone' => $settings->contact_phone,

            'whatsapp_number' => $settings->whatsapp_number,

            'address' => $settings->address,

            'office_hours' => $settings->office_hours,

            'social_links' => $settings->social_links ?? [],

            'default_meta_title' => $settings->default_meta_title,

            'default_meta_description' => $settings->default_meta_description,

            'default_og_image_url' => $this->assetUrl(
                $settings->default_og_image_path
            ),

            'footer_text' => $settings->footer_text,

            'emergency_notice' => $settings->emergency_notice,

            'booking_cta_label' => $settings->booking_cta_label
                ?: 'Find a Counsellor',

            'booking_cta_url' => $settings->booking_cta_url
                ?: '/counsellors',
        ];
    }

    public function navigation(): array
    {
        return [
            'header' => $this->headerNavigation(),

            'footer' => $this->footerNavigation(),
        ];
    }

    public function seoDefaults(): array
    {
        $settings =
            $this->settings();

        return [
            'title' => $settings[
                    'default_meta_title'
                ]
                ?: $settings['site_name'],

            'description' => $settings[
                    'default_meta_description'
                ],

            'image' => $settings[
                    'default_og_image_url'
                ],

            'robots' => 'index,follow',
        ];
    }

    public function assetUrl(
        ?string $path
    ): ?string {
        if (blank($path)) {
            return null;
        }

        if (
            Str::startsWith(
                $path,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $path;
        }

        if (
            Str::startsWith(
                $path,
                '/storage/'
            )
        ) {
            return asset(
                ltrim(
                    $path,
                    '/'
                )
            );
        }

        if (
            Str::startsWith(
                $path,
                '/'
            )
        ) {
            return asset(
                ltrim(
                    $path,
                    '/'
                )
            );
        }

        if (
            Storage::disk('public')
                ->exists($path)
        ) {
            return Storage::disk(
                'public'
            )->url($path);
        }

        return asset($path);
    }

    private function headerNavigation(): array
    {
        $items = [
            [
                'label' => 'Home',

                'url' => route(
                    'public.home'
                ),

                'route_match' => 'public.home',
            ],
        ];

        $cmsPages =
            CmsPage::query()
                ->published()
                ->where(
                    'show_in_header',
                    true
                )
                ->whereNotIn(
                    'slug',
                    [
                        'home',
                        'contact',
                    ]
                )
                ->orderBy(
                    'menu_order'
                )
                ->orderBy('title')
                ->get();

        $about =
            $cmsPages
                ->firstWhere(
                    'slug',
                    'about'
                );

        if ($about !== null) {
            $items[] =
                $this->pageNavigationItem(
                    $about
                );

            $cmsPages =
                $cmsPages
                    ->reject(
                        fn (
                            CmsPage $page
                        ): bool => $page->id
                            === $about->id
                    );
        }

        $items[] = [
            'label' => 'Services',

            'url' => route(
                'public.services.index'
            ),

            'route_match' => 'public.services.*',
        ];

        $items[] = [
            'label' => 'Counsellors',

            'url' => route(
                'public.counsellors.index'
            ),

            'route_match' => 'public.counsellors.*',
        ];

        $items[] = [
            'label' => 'FAQ',

            'url' => route(
                'public.faq'
            ),

            'route_match' => 'public.faq',
        ];

        foreach (
            $cmsPages as $page
        ) {
            $items[] =
                $this->pageNavigationItem(
                    $page
                );
        }

        $contact =
            CmsPage::query()
                ->published()
                ->where(
                    'slug',
                    'contact'
                )
                ->first();

        if ($contact !== null) {
            $items[] = [
                'label' => $contact->menu_label
                    ?: 'Contact',

                'url' => route(
                    'public.contact'
                ),

                'route_match' => 'public.contact',
            ];
        }

        return $items;
    }

    private function footerNavigation(): array
    {
        $pages =
            CmsPage::query()
                ->published()
                ->where(
                    'show_in_footer',
                    true
                )
                ->orderBy(
                    'menu_order'
                )
                ->orderBy('title')
                ->get();

        $items =
            $pages
                ->map(
                    fn (
                        CmsPage $page
                    ): array => $this
                        ->pageNavigationItem(
                            $page
                        )
                )
                ->values();

        return $this
            ->deduplicateNavigation(
                $items
            )
            ->all();
    }

    private function pageNavigationItem(
        CmsPage $page
    ): array {
        $url =
            match ($page->slug) {
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

        return [
            'label' => $page->menu_label
                ?: $page->title,

            'url' => $url,

            'route_match' => match (
                $page->slug
            ) {
                'about' => 'public.about',

                'contact' => 'public.contact',

                default => 'public.page.show',
            },

            'slug' => $page->slug,
        ];
    }

    private function deduplicateNavigation(
        Collection $items
    ): Collection {
        return $items
            ->unique(
                fn (array $item): string => $item['url']
            )
            ->values();
    }

    private function fallbackSettings(): array
    {
        return [
            'site_name' => 'Susadhya Counselling',

            'tagline' => 'Professional Online Counselling',

            'logo_url' => asset(
                'images/brand/susadhya-logo.jpeg'
            ),

            'favicon_url' => null,

            'contact_email' => null,

            'contact_phone' => null,

            'whatsapp_number' => null,

            'address' => null,

            'office_hours' => null,

            'social_links' => [],

            'default_meta_title' => 'Susadhya Counselling',

            'default_meta_description' => 'Professional online counselling with confidential, compassionate support.',

            'default_og_image_url' => asset(
                'images/brand/susadhya-logo.jpeg'
            ),

            'footer_text' => 'Professional online counselling with care, privacy and respect.',

            'emergency_notice' => null,

            'booking_cta_label' => 'Find a Counsellor',

            'booking_cta_url' => '/counsellors',
        ];
    }
}
