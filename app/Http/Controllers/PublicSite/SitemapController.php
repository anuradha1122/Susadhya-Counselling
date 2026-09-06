<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CounsellingService;
use App\Models\CounsellorProfile;
use App\Services\PublicSite\PublicCounsellorService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SitemapController extends Controller
{
    public function __invoke(
        PublicCounsellorService $counsellors
    ): Response {
        $urls = collect([
            [
                'loc' => route(
                    'public.home'
                ),
                'lastmod' => now()
                    ->toDateString(),
            ],
            [
                'loc' => route(
                    'public.services.index'
                ),
                'lastmod' => now()
                    ->toDateString(),
            ],
            [
                'loc' => route(
                    'public.counsellors.index'
                ),
                'lastmod' => now()
                    ->toDateString(),
            ],
            [
                'loc' => route(
                    'public.faq'
                ),
                'lastmod' => now()
                    ->toDateString(),
            ],
        ]);

        $this->appendCmsPages(
            $urls
        );

        $this->appendServices(
            $urls
        );

        $this->appendCounsellors(
            $urls,
            $counsellors
        );

        $urls = $urls
            ->unique('loc')
            ->values();

        $xml = view(
            'sitemap',
            [
                'urls' => $urls,
            ]
        )->render();

        return response(
            $xml,
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]
        );
    }

    private function appendCmsPages(
        $urls
    ): void {
        CmsPage::query()
            ->published()
            ->get()
            ->each(
                function (
                    CmsPage $page
                ) use ($urls): void {
                    $loc = match (
                        $page->slug
                    ) {
                        'home' => route(
                            'public.home'
                        ),

                        'about' => route(
                            'public.about'
                        ),

                        'services' => route(
                            'public.services.index'
                        ),

                        'counsellors' => route(
                            'public.counsellors.index'
                        ),

                        'faq' => route(
                            'public.faq'
                        ),

                        'contact' => route(
                            'public.contact'
                        ),

                        default => route(
                            'public.page.show',
                            [
                                'slug' => $page->slug,
                            ]
                        ),
                    };

                    $urls->push([
                        'loc' => $loc,
                        'lastmod' => $page
                            ->updated_at
                            ?->toDateString(),
                    ]);
                }
            );
    }

    private function appendServices(
        $urls
    ): void {
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

        $query
            ->get()
            ->each(
                function (
                    CounsellingService $service
                ) use ($urls): void {
                    if (
                        blank(
                            $service->slug
                        )
                    ) {
                        return;
                    }

                    $urls->push([
                        'loc' => route(
                            'public.services.show',
                            [
                                'service' => $service->slug,
                            ]
                        ),
                        'lastmod' => $service
                            ->updated_at
                            ?->toDateString(),
                    ]);
                }
            );
    }

    private function appendCounsellors(
        $urls,
        PublicCounsellorService $counsellors
    ): void {
        CounsellorProfile::query()
            ->get()
            ->each(
                function (
                    CounsellorProfile $profile
                ) use (
                    $urls,
                    $counsellors
                ): void {
                    try {
                        $publicProfile =
                            $counsellors
                                ->findPublic(
                                    $profile
                                );
                    } catch (
                        Throwable
                    ) {
                        return;
                    }

                    if (
                        blank(
                            $publicProfile->uuid
                        )
                    ) {
                        return;
                    }

                    $urls->push([
                        'loc' => route(
                            'public.counsellors.show',
                            [
                                'counsellor' => $publicProfile
                                    ->uuid,
                            ]
                        ),
                        'lastmod' => $publicProfile
                            ->updated_at
                            ?->toDateString(),
                    ]);
                }
            );
    }
}
