<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\SaveCmsTestimonialRequest;
use App\Models\CmsTestimonial;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsTestimonialController extends Controller
{
    public function index(): Response
    {
        return Inertia::render(
            'Admin/Cms/Testimonials/Index',
            [
                'testimonials' => CmsTestimonial::query()
                    ->orderBy(
                        'display_order'
                    )
                    ->orderByDesc('id')
                    ->paginate(25),
            ]
        );
    }

    public function store(
        SaveCmsTestimonialRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $testimonial =
            CmsTestimonial::query()
                ->create([
                    ...$data,

                    'consent_confirmed_at' => $data[
                            'consent_confirmed'
                        ]
                            ? now()
                            : null,

                    'published_at' => (
                        $data['is_active']
                        &&
                        $data[
                            'consent_confirmed'
                        ]
                    )
                            ? now()
                            : null,

                    'created_by' => $request->user()->id,

                    'updated_by' => $request->user()->id,
                ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.testimonial_created',
            action: 'create',
            subject: $testimonial,
            actor: $request->user(),
            metadata: [
                'consent_confirmed' => $testimonial
                    ->consent_confirmed,

                'is_active' => $testimonial
                    ->is_active,
            ],
        );

        return back()->with(
            'success',
            'Testimonial created.'
        );
    }

    public function update(
        SaveCmsTestimonialRequest $request,
        CmsTestimonial $cmsTestimonial,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $consentDate =
            $data['consent_confirmed']
                ? (
                    $cmsTestimonial
                        ->consent_confirmed_at
                    ?? now()
                )
                : null;

        $cmsTestimonial->update([
            ...$data,

            'consent_confirmed_at' => $consentDate,

            'published_at' => (
                $data['is_active']
                &&
                $data[
                    'consent_confirmed'
                ]
            )
                    ? (
                        $cmsTestimonial
                            ->published_at
                        ?? now()
                    )
                    : null,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.testimonial_updated',
            action: 'update',
            subject: $cmsTestimonial,
            actor: $request->user(),
            metadata: [
                'consent_confirmed' => $cmsTestimonial
                    ->consent_confirmed,

                'is_active' => $cmsTestimonial
                    ->is_active,
            ],
        );

        return back()->with(
            'success',
            'Testimonial updated.'
        );
    }

    public function archive(
        Request $request,
        CmsTestimonial $cmsTestimonial
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.testimonials.manage'
            ),
            403
        );

        $cmsTestimonial->update([
            'is_active' => false,
            'published_at' => null,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Testimonial archived.'
        );
    }
}
