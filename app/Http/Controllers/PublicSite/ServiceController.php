<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CounsellingService;
use App\Services\Cms\FixedCmsContentService;
use App\Services\PublicSite\PublicContentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(
        Request $request,
        PublicContentService $content,
        FixedCmsContentService $fixed
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'mode' => [
                    'nullable',
                    'string',
                    'max:60',
                ],
            ]);

        $services =
            $content
                ->services(
                    search: $filters['search']
                        ?? null,

                    mode: $filters['mode']
                        ?? null,
                )
                ->paginate(
                    (int) config(
                        'public_site.services_per_page',
                        12
                    )
                )
                ->withQueryString()
                ->through(
                    fn (
                        CounsellingService $service
                    ): array => $content
                        ->transformService(
                            $service
                        )
                );

        $modes =
            CounsellingService::query()
                ->whereIn(
                    'status',
                    config(
                        'public_site.service_statuses',
                        [
                            'active',
                            'published',
                        ]
                    )
                )
                ->whereNotNull(
                    'service_mode'
                )
                ->select(
                    'service_mode'
                )
                ->distinct()
                ->orderBy(
                    'service_mode'
                )
                ->pluck(
                    'service_mode'
                )
                ->values();

        return Inertia::render(
            'Public/Services/Index',
            [
                'services' => $services,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',

                    'mode' => $filters['mode']
                        ?? '',
                ],

                'modes' => $modes,

                /*
                |--------------------------------------------------------------------------
                | Fixed CMS Website Content
                |--------------------------------------------------------------------------
                |
                | Supplies the fixed Services-page hero/content.
                | Admin can edit text/images, but cannot alter the layout.
                |
                */

                'websiteContent' => $fixed
                    ->publicPayload(
                        'services'
                    ),

                'seo' => [
                    'title' => 'Counselling Services',

                    'description' => 'Explore counselling services available through Susadhya Counselling.',

                    'canonical' => route(
                        'public.services.index'
                    ),

                    'robots' => 'index,follow',
                ],
            ]
        );
    }

    public function show(
        CounsellingService $service,
        PublicContentService $content
    ): Response {
        $publicService =
            $content
                ->services()
                ->whereKey(
                    $service->getKey()
                )
                ->firstOrFail();

        $data =
            $content
                ->transformService(
                    $publicService
                );

        return Inertia::render(
            'Public/Services/Show',
            [
                'service' => $data,

                'seo' => [
                    'title' => $publicService->name,

                    'description' => $publicService
                        ->short_description
                        ?: $publicService
                            ->description,

                    'canonical' => route(
                        'public.services.show',
                        $publicService
                    ),

                    'robots' => 'index,follow',
                ],
            ]
        );
    }
}
