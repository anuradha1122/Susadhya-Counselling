<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CmsFaq;
use App\Models\CmsTestimonial;
use App\Models\CounsellingService;
use App\Services\Cms\FixedCmsContentService;
use App\Services\PublicSite\PublicContentService;
use App\Services\PublicSite\PublicCounsellorService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        FixedCmsContentService $fixed,
        PublicContentService $content,
        PublicCounsellorService $counsellors
    ): Response {
        $payload =
            $fixed
                ->publicPayload(
                    'home'
                );

        $serviceLimit =
            (int) (
                $payload[
                    'sections'
                ]['services']['content']['limit']
                ?? 6
            );

        $counsellorLimit =
            (int) (
                $payload[
                    'sections'
                ]['counsellors']['content']['limit']
                ?? 4
            );

        $testimonialLimit =
            (int) (
                $payload[
                    'sections'
                ]['testimonials']['content']['limit']
                ?? 6
            );

        $faqLimit =
            (int) (
                $payload[
                    'sections'
                ]['faq']['content']['limit']
                ?? 6
            );

        return Inertia::render(
            'Public/Home',
            [
                ...$payload,

                'services' => $content
                    ->services(
                        limit: $serviceLimit
                    )
                    ->get()
                    ->map(
                        fn (
                            CounsellingService $service
                        ): array => $content
                            ->transformService(
                                $service
                            )
                    )
                    ->values(),

                'counsellors' => $counsellors
                    ->featured(
                        $counsellorLimit
                    ),

                'testimonials' => CmsTestimonial::query()
                    ->visible()
                    ->orderByDesc(
                        'is_featured'
                    )
                    ->orderBy(
                        'display_order'
                    )
                    ->limit(
                        $testimonialLimit
                    )
                    ->get([
                        'uuid',
                        'display_name',
                        'quote',
                        'rating',
                    ]),

                'faqs' => CmsFaq::query()
                    ->visible()
                    ->orderBy(
                        'display_order'
                    )
                    ->limit(
                        $faqLimit
                    )
                    ->get([
                        'uuid',
                        'question',
                        'answer',
                    ]),
            ]
        );
    }
}
