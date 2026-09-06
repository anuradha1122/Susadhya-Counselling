<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\CounsellorProfile;
use App\Services\PublicSite\PublicCounsellorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CounsellorController extends Controller
{
    public function index(
        Request $request,
        PublicCounsellorService $counsellors
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],
            ]);

        return Inertia::render(
            'Public/Counsellors/Index',
            [
                'counsellors' => $counsellors
                    ->paginate(
                        search: $filters[
                                'search'
                            ]
                            ?? null,

                        perPage: (int) config(
                            'public_site.counsellors_per_page',
                            12
                        ),
                    ),

                'filters' => [
                    'search' => $filters[
                            'search'
                        ]
                        ?? '',
                ],

                'seo' => [
                    'title' => 'Find a Counsellor',

                    'description' => 'Explore available professional counsellor profiles at Susadhya Counselling.',

                    'canonical' => route(
                        'public.counsellors.index'
                    ),

                    'robots' => 'index,follow',
                ],
            ]
        );
    }

    public function show(
        CounsellorProfile $counsellor,
        PublicCounsellorService $counsellors
    ): Response {
        $profile =
            $counsellors
                ->findPublic(
                    $counsellor
                );

        $data =
            $counsellors
                ->transform(
                    $profile
                );

        return Inertia::render(
            'Public/Counsellors/Show',
            [
                'counsellor' => $data,

                'seo' => [
                    'title' => $data['name'],

                    'description' => $data['headline']
                        ?: $data['bio']
                        ?: 'Professional counsellor profile at Susadhya Counselling.',

                    'canonical' => route(
                        'public.counsellors.show',
                        $profile
                    ),

                    'robots' => 'index,follow',
                ],
            ]
        );
    }
}
