<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCounsellingServiceRequest;
use App\Http\Requests\Admin\UpdateCounsellingServiceRequest;
use App\Models\CounsellingService;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CounsellingServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CounsellingService::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                'in:active,inactive,archived',
            ],
            'service_category_id' => [
                'nullable',
                'integer',
                'exists:service_categories,id',
            ],
            'service_mode' => [
                'nullable',
                'in:online,in_person,both',
            ],
            'target_age_group' => [
                'nullable',
                'in:children,adolescents,adults,seniors,all_ages,custom',
            ],
        ]);

        $services = CounsellingService::query()
            ->with('category:id,name,status')
            ->when(
                $filters['search'] ?? null,
                function ($query, string $search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'short_description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'category',
                                    fn ($query) => $query->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );
                }
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                $filters['service_category_id'] ?? null,
                fn ($query, int|string $categoryId) => $query->where(
                    'service_category_id',
                    $categoryId
                )
            )
            ->when(
                $filters['service_mode'] ?? null,
                fn ($query, string $mode) => $query->where(
                    'service_mode',
                    $mode
                )
            )
            ->when(
                $filters['target_age_group'] ?? null,
                fn ($query, string $ageGroup) => $query->where(
                    'target_age_group',
                    $ageGroup
                )
            )
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'Admin/CounsellingServices/Index',
            [
                'services' => $services,
                'filters' => [
                    'search' => $filters['search'] ?? '',
                    'status' => $filters['status'] ?? '',
                    'service_category_id' => isset(
                        $filters['service_category_id']
                    )
                        ? (string) $filters['service_category_id']
                        : '',
                    'service_mode' => $filters['service_mode'] ?? '',
                    'target_age_group' => $filters[
                        'target_age_group'
                    ] ?? '',
                ],
                'categories' => ServiceCategory::query()
                    ->where('status', '!=', 'archived')
                    ->orderBy('display_order')
                    ->orderBy('name')
                    ->get(['id', 'name', 'status']),
                'permissions' => [
                    'create' => $request->user()
                        ->can('services.create'),
                    'update' => $request->user()
                        ->can('services.update'),
                    'archive' => $request->user()
                        ->can('services.archive'),
                ],
            ]
        );
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', CounsellingService::class);

        $categoryId = $request->integer('service_category_id');

        return Inertia::render(
            'Admin/CounsellingServices/Create',
            [
                ...$this->formOptions(),
                'selectedCategoryId' => $categoryId ?: null,
            ]
        );
    }

    public function store(
        StoreCounsellingServiceRequest $request
    ): RedirectResponse {
        $service = CounsellingService::create(
            $request->validated()
        );

        return to_route(
            'admin.counselling-services.show',
            $service
        )->with(
            'success',
            'Counselling service created successfully.'
        );
    }

    public function show(
        CounsellingService $counsellingService
    ): Response {
        $this->authorize('view', $counsellingService);

        $counsellingService->load([
            'category:id,name,slug,status',
            'archivedBy:id,name',
        ]);

        return Inertia::render(
            'Admin/CounsellingServices/Show',
            [
                'service' => $counsellingService,
                'permissions' => [
                    'update' => request()->user()
                        ->can('update', $counsellingService),
                    'archive' => request()->user()
                        ->can('delete', $counsellingService),
                    'restore' => request()->user()
                        ->can('restore', $counsellingService),
                ],
            ]
        );
    }

    public function edit(
        CounsellingService $counsellingService
    ): Response {
        $this->authorize('update', $counsellingService);

        $counsellingService->load(
            'category:id,name,status'
        );

        return Inertia::render(
            'Admin/CounsellingServices/Edit',
            [
                'service' => $counsellingService,
                ...$this->formOptions($counsellingService),
            ]
        );
    }

    public function update(
        UpdateCounsellingServiceRequest $request,
        CounsellingService $counsellingService
    ): RedirectResponse {
        $counsellingService->update(
            $request->validated()
        );

        return to_route(
            'admin.counselling-services.show',
            $counsellingService
        )->with(
            'success',
            'Counselling service updated successfully.'
        );
    }

    public function destroy(
        CounsellingService $counsellingService
    ): RedirectResponse {
        $this->authorize('delete', $counsellingService);

        $counsellingService->update([
            'status' => 'archived',
            'archived_at' => now(),
            'archived_by' => request()->user()->id,
        ]);

        return to_route(
            'admin.counselling-services.index'
        )->with(
            'success',
            'Counselling service archived successfully.'
        );
    }

    public function restore(
        CounsellingService $counsellingService
    ): RedirectResponse {
        $this->authorize('restore', $counsellingService);

        $restoreStatus = $counsellingService->category->status
            === 'active'
                ? 'active'
                : 'inactive';

        $counsellingService->update([
            'status' => $restoreStatus,
            'archived_at' => null,
            'archived_by' => null,
        ]);

        return to_route(
            'admin.counselling-services.show',
            $counsellingService
        )->with(
            'success',
            'Counselling service restored successfully.'
        );
    }

    private function formOptions(
        ?CounsellingService $currentService = null
    ): array {
        return [
            'categories' => ServiceCategory::query()
                ->where(function ($query) use ($currentService): void {
                    $query->where('status', 'active');

                    if ($currentService) {
                        $query->orWhere(
                            'id',
                            $currentService->service_category_id
                        );
                    }
                })
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(['id', 'name', 'status']),
            'serviceModes' => [
                [
                    'value' => 'online',
                    'label' => 'Online',
                ],
                [
                    'value' => 'in_person',
                    'label' => 'In person',
                ],
                [
                    'value' => 'both',
                    'label' => 'Online and in person',
                ],
            ],
            'targetAgeGroups' => [
                [
                    'value' => 'children',
                    'label' => 'Children',
                ],
                [
                    'value' => 'adolescents',
                    'label' => 'Adolescents',
                ],
                [
                    'value' => 'adults',
                    'label' => 'Adults',
                ],
                [
                    'value' => 'seniors',
                    'label' => 'Seniors',
                ],
                [
                    'value' => 'all_ages',
                    'label' => 'All ages',
                ],
                [
                    'value' => 'custom',
                    'label' => 'Custom age range',
                ],
            ],
            'durations' => [
                30,
                45,
                60,
                90,
                120,
            ],
        ];
    }
}
