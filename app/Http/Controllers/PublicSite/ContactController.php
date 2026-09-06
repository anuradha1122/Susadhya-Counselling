<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\Cms\FixedCmsContentService;
use App\Services\PublicSite\PublicContentService;
use App\Services\PublicSite\PublicSiteDataService;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __invoke(
        PublicContentService $content,
        PublicSiteDataService $site,
        FixedCmsContentService $fixed
    ): Response {
        $page =
            $content->page(
                'contact'
            );

        return Inertia::render(
            'Public/Contact',
            [
                /*
                |--------------------------------------------------------------------------
                | Existing CMS Page Data
                |--------------------------------------------------------------------------
                |
                | Retained for page body and backward-compatible page information.
                |
                */

                'page' => $content
                    ->transformPage(
                        $page
                    ),

                /*
                |--------------------------------------------------------------------------
                | Website Contact Settings
                |--------------------------------------------------------------------------
                |
                | Email, phone, WhatsApp, address, office hours and emergency notice
                | continue to come from Website Settings.
                |
                */

                'contact' => $site->settings(),

                /*
                |--------------------------------------------------------------------------
                | Fixed Website Content
                |--------------------------------------------------------------------------
                |
                | Supplies the fixed Contact page hero image, hero heading,
                | supporting text and SEO information.
                |
                | The admin can edit the content, but cannot alter the layout.
                |
                */

                'websiteContent' => $fixed
                    ->publicPayload(
                        'contact'
                    ),
            ]
        );
    }
}
