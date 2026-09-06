<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\SaveCmsFaqRequest;
use App\Models\CmsFaq;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CmsFaqController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],
            ]);

        $query =
            CmsFaq::query();

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
                            'question',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'category',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        return Inertia::render(
            'Admin/Cms/Faqs/Index',
            [
                'faqs' => $query
                    ->orderBy(
                        'display_order'
                    )
                    ->orderBy('id')
                    ->paginate(25)
                    ->withQueryString(),

                'filters' => [
                    'search' => $filters['search']
                        ?? '',
                ],
            ]
        );
    }

    public function store(
        SaveCmsFaqRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $faq =
            CmsFaq::query()
                ->create([
                    ...$data,

                    'published_at' => $data['is_active']
                            ? now()
                            : null,

                    'created_by' => $request->user()->id,

                    'updated_by' => $request->user()->id,
                ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.faq_created',
            action: 'create',
            subject: $faq,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'FAQ created.'
        );
    }

    public function update(
        SaveCmsFaqRequest $request,
        CmsFaq $cmsFaq,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $cmsFaq->update([
            ...$data,

            'published_at' => $data['is_active']
                    ? (
                        $cmsFaq
                            ->published_at
                        ?? now()
                    )
                    : null,

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.faq_updated',
            action: 'update',
            subject: $cmsFaq,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'FAQ updated.'
        );
    }

    public function toggle(
        Request $request,
        CmsFaq $cmsFaq
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.faqs.manage'
            ),
            403
        );

        $active =
            ! $cmsFaq->is_active;

        $cmsFaq->update([
            'is_active' => $active,

            'published_at' => $active
                    ? (
                        $cmsFaq
                            ->published_at
                        ?? now()
                    )
                    : null,

            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'FAQ visibility updated.'
        );
    }
}
