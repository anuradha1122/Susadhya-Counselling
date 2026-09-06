<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\SaveCmsPageRequest;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsPageController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                'string',
                'max:30',
            ],
        ]);

        $query =
            CmsPage::query()
                ->withCount('sections');

        if (
            filled(
                $filters['search']
                ?? null
            )
        ) {
            $search =
                $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'title',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'menu_label',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            filled(
                $filters['status']
                ?? null
            )
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        $pages =
            $query
                ->orderBy('menu_order')
                ->orderBy('title')
                ->paginate(20)
                ->withQueryString();

        return Inertia::render(
            'Admin/Cms/Pages/Index',
            [
                'pages' => $pages,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',

                    'status' => $filters['status']
                        ?? '',
                ],

                'statuses' => CmsPage::statuses(),
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Admin/Cms/Pages/Create',
            [
                'templates' => CmsPage::templates(),
            ]
        );
    }

    public function store(
        SaveCmsPageRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $page =
            CmsPage::query()
                ->create([
                    ...$data,

                    'status' => CmsPage::STATUS_DRAFT,

                    'created_by' => $request->user()->id,

                    'updated_by' => $request->user()->id,
                ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_created',
            action: 'create',
            subject: $page,
            actor: $request->user(),
            metadata: [
                'slug' => $page->slug,

                'status' => $page->status,
            ],
        );

        return redirect()
            ->route(
                'admin.cms.pages.edit',
                $page
            )
            ->with(
                'success',
                'CMS page created.'
            );
    }

    public function edit(
        CmsPage $cmsPage
    ): Response {
        $cmsPage->load([
            'sections' => fn ($query) => $query
                ->orderBy(
                    'display_order'
                )
                ->orderBy('id'),
        ]);

        $media =
            CmsMedia::query()
                ->where(
                    'is_active',
                    true
                )
                ->latest()
                ->limit(200)
                ->get()
                ->map(
                    fn (
                        CmsMedia $item
                    ): array => [
                        'uuid' => $item->uuid,

                        'url' => $item
                            ->publicUrl(),

                        'alt_text' => $item
                            ->alt_text,

                        'original_name' => $item
                            ->original_name,

                        'width' => $item->width,

                        'height' => $item->height,
                    ]
                )
                ->values();

        return Inertia::render(
            'Admin/Cms/Pages/Edit',
            [
                'page' => $cmsPage,

                'templates' => CmsPage::templates(),

                'sectionTypes' => CmsSection::types(),

                'media' => $media,
            ]
        );
    }

    public function update(
        SaveCmsPageRequest $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $before = [
            'title' => $cmsPage->title,

            'slug' => $cmsPage->slug,

            'template' => $cmsPage->template,
        ];

        $cmsPage->update([
            ...$request->validated(),

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_updated',
            action: 'update',
            subject: $cmsPage,
            actor: $request->user(),
            metadata: [
                'before' => $before,

                'after' => [
                    'title' => $cmsPage->title,

                    'slug' => $cmsPage->slug,

                    'template' => $cmsPage->template,
                ],
            ],
        );

        return back()->with(
            'success',
            'CMS page updated.'
        );
    }

    public function publish(
        Request $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.pages.manage'
            ),
            403
        );

        $cmsPage->update([
            'status' => CmsPage::STATUS_PUBLISHED,

            'published_at' => now(),

            'published_by' => $request->user()->id,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_published',
            action: 'publish',
            subject: $cmsPage,
            actor: $request->user(),
            metadata: [
                'slug' => $cmsPage->slug,
            ],
        );

        return back()->with(
            'success',
            'CMS page published.'
        );
    }

    public function unpublish(
        Request $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.pages.manage'
            ),
            403
        );

        $cmsPage->update([
            'status' => CmsPage::STATUS_DRAFT,

            'published_at' => null,

            'published_by' => null,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_unpublished',
            action: 'unpublish',
            subject: $cmsPage,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'CMS page returned to draft.'
        );
    }

    public function archive(
        Request $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.pages.manage'
            ),
            403
        );

        if ($cmsPage->slug === 'home') {
            return back()->with(
                'error',
                'The home page cannot be archived.'
            );
        }

        $cmsPage->update([
            'status' => CmsPage::STATUS_ARCHIVED,

            'published_at' => null,

            'published_by' => null,

            'show_in_header' => false,

            'show_in_footer' => false,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_archived',
            action: 'archive',
            subject: $cmsPage,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'CMS page archived.'
        );
    }

    public function restore(
        Request $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.pages.manage'
            ),
            403
        );

        $cmsPage->update([
            'status' => CmsPage::STATUS_DRAFT,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.page_restored',
            action: 'restore',
            subject: $cmsPage,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'CMS page restored as draft.'
        );
    }
}
