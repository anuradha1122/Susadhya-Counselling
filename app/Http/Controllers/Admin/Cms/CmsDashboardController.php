<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsFaq;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsTestimonial;
use Inertia\Inertia;
use Inertia\Response;

class CmsDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render(
            'Admin/Cms/Dashboard',
            [
                'stats' => [
                    'pages' => CmsPage::query()
                        ->count(),

                    'publishedPages' => CmsPage::query()
                        ->where(
                            'status',
                            CmsPage::STATUS_PUBLISHED
                        )
                        ->count(),

                    'faqs' => CmsFaq::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->count(),

                    'testimonials' => CmsTestimonial::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->where(
                            'consent_confirmed',
                            true
                        )
                        ->count(),

                    'media' => CmsMedia::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->count(),
                ],

                'recentPages' => CmsPage::query()
                    ->latest('updated_at')
                    ->limit(8)
                    ->get([
                        'uuid',
                        'title',
                        'slug',
                        'status',
                        'updated_at',
                    ]),
            ]
        );
    }
}
