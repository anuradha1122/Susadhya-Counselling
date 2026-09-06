<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\Cms\FixedCmsContentService;
use App\Services\PublicSite\PublicContentService;
use Inertia\Inertia;
use Inertia\Response;

class CmsPageController extends Controller
{
    /**
     * Fixed website page slugs are controlled by application routes.
     *
     * These pages must never be exposed through /pages/{slug}.
     */
    private const FIXED_PAGE_SLUGS = [
        'home',
        'about',
        'services',
        'counsellors',
        'faq',
        'contact',
    ];

    public function about(
        FixedCmsContentService $content
    ): Response {
        return Inertia::render(
            'Public/About',
            $content->publicPayload(
                'about'
            )
        );
    }

    public function show(
        string $slug,
        PublicContentService $content
    ): Response {
        abort_if(
            in_array(
                $slug,
                self::FIXED_PAGE_SLUGS,
                true
            ),
            404
        );

        return $this->render(
            $slug,
            $content
        );
    }

    private function render(
        string $slug,
        PublicContentService $content
    ): Response {
        $page = $content->page(
            $slug
        );

        return Inertia::render(
            'Public/CmsPage',
            [
                'page' => $content
                    ->transformPage(
                        $page
                    ),
            ]
        );
    }
}
