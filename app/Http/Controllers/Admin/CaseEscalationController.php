<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\StoreCaseEscalationRequest;
use App\Http\Requests\Admin\Operations\UpdateCaseEscalationRequest;
use App\Models\CaseEscalation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CaseEscalationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                Rule::in(CaseEscalation::statuses()),
            ],
            'priority' => [
                'nullable',
                Rule::in(CaseEscalation::priorities()),
            ],
        ]);

        $escalations = CaseEscalation::query()
            ->with([
                'assignee:id,name',
                'creator:id,name',
            ])
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(
                    'case_reference',
                    'like',
                    '%'.trim($search).'%'
                )
            )
            ->when(
                $filters['status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status)
            )
            ->when(
                $filters['priority'] ?? null,
                fn (Builder $query, string $priority) => $query->where('priority', $priority)
            )
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('due_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $assignees = User::query()
            ->where('is_active', true)
            ->whereHas(
                'roles',
                fn (Builder $query) => $query->whereIn(
                    'name',
                    [
                        'admin',
                        'super_admin',
                        'clinical_supervisor',
                    ]
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return Inertia::render(
            'Admin/CaseEscalations/Index',
            [
                'escalations' => $escalations,
                'filters' => [
                    'search' => $filters['search'] ?? '',
                    'status' => $filters['status'] ?? '',
                    'priority' => $filters['priority'] ?? '',
                ],
                'assignees' => $assignees,
                'options' => [
                    'reasons' => CaseEscalation::reasons(),
                    'statuses' => CaseEscalation::statuses(),
                    'priorities' => CaseEscalation::priorities(),
                ],
            ]
        );
    }

    public function store(
        StoreCaseEscalationRequest $request
    ): RedirectResponse {
        CaseEscalation::query()->create([
            ...$request->validated(),
            'status' => CaseEscalation::STATUS_OPEN,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Case escalation metadata created.'
        );
    }

    public function update(
        UpdateCaseEscalationRequest $request,
        CaseEscalation $caseEscalation
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $validated,
                $caseEscalation
            ): void {
                $resolved = in_array(
                    $validated['status'],
                    [
                        CaseEscalation::STATUS_RESOLVED,
                        CaseEscalation::STATUS_DISMISSED,
                    ],
                    true
                );

                $caseEscalation->forceFill([
                    ...$validated,
                    'resolved_by' => $resolved
                        ? $request->user()->id
                        : null,
                    'resolved_at' => $resolved
                        ? now()
                        : null,
                    'updated_by' => $request->user()->id,
                ])->save();
            }
        );

        return back()->with(
            'success',
            'Case escalation metadata updated.'
        );
    }
}
