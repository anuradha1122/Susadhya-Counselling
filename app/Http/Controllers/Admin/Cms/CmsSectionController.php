<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\SaveCmsSectionRequest;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CmsSectionController extends Controller
{
    public function store(
        SaveCmsSectionRequest $request,
        CmsPage $cmsPage,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data =
            $request->validated();

        $this->validateUniqueKey(
            $cmsPage,
            $data['key'] ?? null
        );

        $section =
            $cmsPage
                ->sections()
                ->create([
                    'key' => $data['key']
                        ?? null,

                    'type' => $data['type'],

                    'heading' => $data['heading']
                        ?? null,

                    'subheading' => $data['subheading']
                        ?? null,

                    'content' => $data['content']
                        ?? [],

                    'settings' => [],

                    'display_order' => $data[
                            'display_order'
                        ],

                    'is_active' => $data['is_active'],

                    'created_by' => $request
                        ->user()
                        ->id,

                    'updated_by' => $request
                        ->user()
                        ->id,
                ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.section_created',
            action: 'create',
            subject: $section,
            actor: $request->user(),
            metadata: [
                'page_uuid' => $cmsPage->uuid,

                'type' => $section->type,

                'key' => $section->key,
            ],
        );

        return back()->with(
            'success',
            'Page section added.'
        );
    }

    public function update(
        SaveCmsSectionRequest $request,
        CmsPage $cmsPage,
        CmsSection $cmsSection,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $this->assertBelongsToPage(
            $cmsPage,
            $cmsSection
        );

        $data =
            $request->validated();

        $this->validateUniqueKey(
            $cmsPage,
            $data['key'] ?? null,
            $cmsSection
        );

        $cmsSection->update([
            'key' => $data['key']
                ?? null,

            'type' => $data['type'],

            'heading' => $data['heading']
                ?? null,

            'subheading' => $data['subheading']
                ?? null,

            'content' => $data['content']
                ?? [],

            'display_order' => $data[
                    'display_order'
                ],

            'is_active' => $data['is_active'],

            'updated_by' => $request
                ->user()
                ->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.section_updated',
            action: 'update',
            subject: $cmsSection,
            actor: $request->user(),
            metadata: [
                'page_uuid' => $cmsPage->uuid,

                'type' => $cmsSection->type,
            ],
        );

        return back()->with(
            'success',
            'Page section updated.'
        );
    }

    public function toggle(
        Request $request,
        CmsPage $cmsPage,
        CmsSection $cmsSection,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request
                ->user()
                ?->can(
                    'cms.sections.manage'
                ),
            403
        );

        $this->assertBelongsToPage(
            $cmsPage,
            $cmsSection
        );

        $cmsSection->update([
            'is_active' => ! $cmsSection
                ->is_active,

            'updated_by' => $request
                ->user()
                ->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.section_toggled',
            action: 'update',
            subject: $cmsSection,
            actor: $request->user(),
            metadata: [
                'is_active' => $cmsSection
                    ->is_active,
            ],
        );

        return back()->with(
            'success',
            'Section visibility updated.'
        );
    }

    public function move(
        Request $request,
        CmsPage $cmsPage,
        CmsSection $cmsSection,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request
                ->user()
                ?->can(
                    'cms.sections.manage'
                ),
            403
        );

        $this->assertBelongsToPage(
            $cmsPage,
            $cmsSection
        );

        $data =
            $request->validate([
                'direction' => [
                    'required',
                    Rule::in([
                        'up',
                        'down',
                    ]),
                ],
            ]);

        $operator =
            $data['direction']
            === 'up'
                ? '<'
                : '>';

        $order =
            $data['direction']
            === 'up'
                ? 'desc'
                : 'asc';

        $other =
            $cmsPage
                ->sections()
                ->where(
                    'display_order',
                    $operator,
                    $cmsSection
                        ->display_order
                )
                ->orderBy(
                    'display_order',
                    $order
                )
                ->orderBy(
                    'id',
                    $order
                )
                ->first();

        if ($other === null) {
            return back();
        }

        DB::transaction(
            function () use (
                $cmsSection,
                $other,
                $request
            ): void {
                $currentOrder =
                    $cmsSection
                        ->display_order;

                $cmsSection->update([
                    'display_order' => $other
                        ->display_order,

                    'updated_by' => $request
                        ->user()
                        ->id,
                ]);

                $other->update([
                    'display_order' => $currentOrder,

                    'updated_by' => $request
                        ->user()
                        ->id,
                ]);
            }
        );

        $auditLogger->record(
            category: 'cms',
            event: 'cms.section_reordered',
            action: 'update',
            subject: $cmsSection,
            actor: $request->user(),
            metadata: [
                'direction' => $data['direction'],
            ],
        );

        return back();
    }

    public function archive(
        Request $request,
        CmsPage $cmsPage,
        CmsSection $cmsSection,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request
                ->user()
                ?->can(
                    'cms.sections.manage'
                ),
            403
        );

        $this->assertBelongsToPage(
            $cmsPage,
            $cmsSection
        );

        $cmsSection->update([
            'is_active' => false,

            'updated_by' => $request
                ->user()
                ->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.section_archived',
            action: 'archive',
            subject: $cmsSection,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Section archived.'
        );
    }

    private function validateUniqueKey(
        CmsPage $page,
        ?string $key,
        ?CmsSection $ignore = null
    ): void {
        if (blank($key)) {
            return;
        }

        $query =
            $page
                ->sections()
                ->where(
                    'key',
                    $key
                );

        if ($ignore !== null) {
            $query->whereKeyNot(
                $ignore->id
            );
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'key' => 'That section key is already used on this page.',
            ]);
        }
    }

    private function assertBelongsToPage(
        CmsPage $page,
        CmsSection $section
    ): void {
        abort_unless(
            (int)
            $section
                ->cms_page_id
            ===
            (int) $page->id,
            404
        );
    }
}
