<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceCategory::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                'in:active,inactive,archived',
            ],
        ]);

        $categories = ServiceCategory::query()
            ->withCount([
                'services',
                'services as active_services_count' => fn ($query) => $query
                    ->where('status', 'active'),
            ])
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
                                    'description',
                                    'like',
                                    "%{$search}%"
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
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'Admin/ServiceCategories/Index',
            [
                'categories' => $categories,
                'filters' => [
                    'search' => $filters['search'] ?? '',
                    'status' => $filters['status'] ?? '',
                ],
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

    public function create(): Response
    {
        $this->authorize('create', ServiceCategory::class);

        return Inertia::render(
            'Admin/ServiceCategories/Create'
        );
    }

    public function store(
        StoreServiceCategoryRequest $request
    ): RedirectResponse {
        $category = ServiceCategory::create(
            $request->validated()
        );

        return to_route(
            'admin.service-categories.show',
            $category
        )->with(
            'success',
            'Service category created successfully.'
        );
    }

    public function show(
        ServiceCategory $serviceCategory
    ): Response {
        $this->authorize('view', $serviceCategory);

        $serviceCategory->load([
            'archivedBy:id,name',
        ])->loadCount([
            'services',
            'services as active_services_count' => fn ($query) => $query
                ->where('status', 'active'),
        ]);

        $services = $serviceCategory->services()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'duration_minutes',
                'service_mode',
                'target_age_group',
                'price',
                'currency',
                'status',
            ]);

        return Inertia::render(
            'Admin/ServiceCategories/Show',
            [
                'category' => $serviceCategory,
                'services' => $services,
                'permissions' => [
                    'update' => request()->user()
                        ->can('update', $serviceCategory),
                    'archive' => request()->user()
                        ->can('delete', $serviceCategory),
                    'restore' => request()->user()
                        ->can('restore', $serviceCategory),
                    'createService' => request()->user()
                        ->can('services.create'),
                ],
            ]
        );
    }

    public function edit(
        ServiceCategory $serviceCategory
    ): Response {
        $this->authorize('update', $serviceCategory);

        return Inertia::render(
            'Admin/ServiceCategories/Edit',
            [
                'category' => $serviceCategory,
            ]
        );
    }

    public function update(
        UpdateServiceCategoryRequest $request,
        ServiceCategory $serviceCategory
    ): RedirectResponse {
        $serviceCategory->update(
            $request->validated()
        );

        return to_route(
            'admin.service-categories.show',
            $serviceCategory
        )->with(
            'success',
            'Service category updated successfully.'
        );
    }

    public function destroy(
        ServiceCategory $serviceCategory
    ): RedirectResponse {
        $this->authorize('delete', $serviceCategory);

        $serviceCategory->update([
            'status' => 'archived',
            'archived_at' => now(),
            'archived_by' => request()->user()->id,
        ]);

        return to_route(
            'admin.service-categories.index'
        )->with(
            'success',
            'Service category archived successfully.'
        );
    }

    public function restore(
        ServiceCategory $serviceCategory
    ): RedirectResponse {
        $this->authorize('restore', $serviceCategory);

        $serviceCategory->update([
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ]);

        return to_route(
            'admin.service-categories.show',
            $serviceCategory
        )->with(
            'success',
            'Service category restored successfully.'
        );
    }

    public function generateSlug(Request $request): array
    {
        $this->authorize('create', ServiceCategory::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        return [
            'slug' => Str::slug($validated['name']),
        ];
    }
}
