<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\UpdateFixedCmsPageRequest;
use App\Http\Requests\Admin\Cms\UpdateFixedCmsSectionRequest;
use App\Models\CmsMedia;
use App\Services\Cms\FixedCmsContentService;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FixedCmsContentController extends Controller
{
    public function index(
        FixedCmsContentService $content
    ): Response {
        return Inertia::render(
            'Admin/Cms/Content/Index',
            [
                'pages' => $content
                    ->fixedPages(),
            ]
        );
    }

    public function edit(
        string $page,
        FixedCmsContentService $content
    ): Response {
        $payload =
            $content
                ->editorPayload(
                    $page
                );

        return Inertia::render(
            'Admin/Cms/Content/Edit',
            [
                ...$payload,

                'pages' => $content
                    ->fixedPages(),

                'media' => CmsMedia::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->latest()
                    ->limit(250)
                    ->get()
                    ->map(
                        fn (
                            CmsMedia $item
                        ): array => [
                            'uuid' => $item
                                ->uuid,

                            'url' => $item
                                ->publicUrl(),

                            'original_name' => $item
                                ->original_name,

                            'alt_text' => $item
                                ->alt_text,

                            'width' => $item
                                ->width,

                            'height' => $item
                                ->height,
                        ]
                    )
                    ->values(),
            ]
        );
    }

    public function updatePage(
        UpdateFixedCmsPageRequest $request,
        string $page,
        FixedCmsContentService $content,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $model =
            $content
                ->page($page);

        $model->update([
            ...$request
                ->validated(),

            'updated_by' => $request
                ->user()
                ->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.fixed_page_updated',
            action: 'update',
            subject: $model,
            actor: $request->user(),
            metadata: [
                'page' => $page,
            ],
        );

        return back()->with(
            'success',
            'Page settings updated.'
        );
    }

    public function updateSection(
        UpdateFixedCmsSectionRequest $request,
        string $page,
        string $section,
        FixedCmsContentService $content,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $model =
            $content
                ->saveSection(
                    pageSlug: $page,
                    sectionKey: $section,
                    data: $request
                        ->validated(),
                    userId: $request
                        ->user()
                        ->id,
                );

        $auditLogger->record(
            category: 'cms',
            event: 'cms.fixed_content_updated',
            action: 'update',
            subject: $model,
            actor: $request->user(),
            metadata: [
                'page' => $page,

                'section' => $section,
            ],
        );

        return back()->with(
            'success',
            'Website content updated.'
        );
    }
}
