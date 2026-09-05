<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalException;
use App\Models\User;
use App\Services\AdminOperations\AdminOperationsDashboardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OperationsDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        AdminOperationsDashboardService $dashboard
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                Rule::in(OperationalException::statuses()),
            ],
            'priority' => [
                'nullable',
                Rule::in(OperationalException::priorities()),
            ],
        ]);

        $exceptions = OperationalException::query()
            ->with([
                'assignee:id,name',
                'creator:id,name',
            ])
            ->when(
                $filters['search'] ?? null,
                function (
                    Builder $query,
                    string $search
                ): void {
                    $query->where(
                        function (Builder $inner) use ($search): void {
                            $inner
                                ->where(
                                    'title',
                                    'like',
                                    '%'.trim($search).'%'
                                )
                                ->orWhere(
                                    'source_reference',
                                    'like',
                                    '%'.trim($search).'%'
                                );
                        }
                    );
                }
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
                    ]
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return Inertia::render(
            'Admin/Operations/Index',
            [
                'metrics' => $dashboard->metrics(),
                'exceptions' => $exceptions,
                'filters' => [
                    'search' => $filters['search'] ?? '',
                    'status' => $filters['status'] ?? '',
                    'priority' => $filters['priority'] ?? '',
                ],
                'assignees' => $assignees,
                'options' => [
                    'types' => OperationalException::types(),
                    'statuses' => OperationalException::statuses(),
                    'priorities' => OperationalException::priorities(),
                ],
            ]
        );
    }
}
