<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CmsFaq;
use App\Services\Cms\FixedCmsContentService;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __invoke(
        FixedCmsContentService $fixed
    ): Response {
        $faqs =
            CmsFaq::query()
                ->visible()
                ->orderBy(
                    'display_order'
                )
                ->orderBy('id')
                ->get([
                    'uuid',
                    'category',
                    'question',
                    'answer',
                ])
                ->groupBy(
                    fn (
                        CmsFaq $faq
                    ): string => $faq->category
                        ?: 'General'
                )
                ->map(
                    fn ($items) => $items
                        ->values()
                );

        $websiteContent =
            $fixed
                ->publicPayload(
                    'faq'
                );

        return Inertia::render(
            'Public/Faq',
            [
                'faqGroups' => $faqs,

                /*
                |--------------------------------------------------------------------------
                | Fixed Website Content
                |--------------------------------------------------------------------------
                |
                | Supplies the admin-editable FAQ page hero and SEO content.
                | The page layout itself remains fixed in React.
                |
                */

                'websiteContent' => $websiteContent,

                /*
                |--------------------------------------------------------------------------
                | SEO Fallback
                |--------------------------------------------------------------------------
                |
                | The frontend can prefer websiteContent.page.seo.
                | This remains as a safe fallback.
                |
                */

                'seo' => [
                    'title' => 'Frequently Asked Questions',

                    'description' => 'Answers to common questions about using Susadhya Counselling.',

                    'canonical' => route(
                        'public.faq'
                    ),

                    'robots' => 'index,follow',
                ],
            ]
        );
    }
}
