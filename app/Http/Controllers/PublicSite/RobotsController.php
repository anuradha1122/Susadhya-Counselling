<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode(
            PHP_EOL,
            [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin/',
                'Disallow: /client/',
                'Disallow: /counsellor/',
                'Disallow: /finance/',
                'Disallow: /compliance/',
                '',
                'Sitemap: '
                .route(
                    'public.sitemap'
                ),
                '',
            ]
        );

        return response(
            $content,
            200,
            [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]
        );
    }
}
