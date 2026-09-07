<?php

namespace App\Http\Controllers\Admin\AdvancedProduct;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvancedProduct\StoreServicePackageRequest;
use App\Http\Requests\Admin\AdvancedProduct\UpdateServicePackageRequest;
use App\Models\CounsellingService;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServicePackageController extends Controller
{
    public function index(Request $request): Response
    {
        $packages = ServicePackage::query()
            ->withCount('subscriptions')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/AdvancedProduct/Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AdvancedProduct/Packages/Form', [
            'package' => null,
            'services' => $this->services(),
            'selectedServices' => [],
        ]);
    }

    public function store(StoreServicePackageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];

        unset($data['service_ids']);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_subscription'] = $request->boolean('is_subscription');
        $data['is_active'] = $request->boolean('is_active');

        $package = ServicePackage::create($data);

        $this->syncItems($package, $serviceIds);

        return redirect()
            ->route('admin.advanced-products.packages.index')
            ->with('success', 'Package created.');
    }

    public function edit(ServicePackage $package): Response
    {
        $package->load('items');

        return Inertia::render('Admin/AdvancedProduct/Packages/Form', [
            'package' => $package,
            'services' => $this->services(),
            'selectedServices' => $package->items->pluck('counselling_service_id')->values(),
        ]);
    }

    public function update(UpdateServicePackageRequest $request, ServicePackage $package): RedirectResponse
    {
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];

        unset($data['service_ids']);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_subscription'] = $request->boolean('is_subscription');
        $data['is_active'] = $request->boolean('is_active');

        $package->update($data);

        $this->syncItems($package, $serviceIds);

        return redirect()
            ->route('admin.advanced-products.packages.index')
            ->with('success', 'Package updated.');
    }

    public function destroy(ServicePackage $package): RedirectResponse
    {
        abort_if($package->subscriptions()->exists(), 422, 'Cannot delete a package with subscriptions.');

        $package->delete();

        return redirect()
            ->route('admin.advanced-products.packages.index')
            ->with('success', 'Package deleted.');
    }

    private function syncItems(ServicePackage $package, array $serviceIds): void
    {
        $package->items()->delete();

        foreach (array_values(array_unique($serviceIds)) as $index => $serviceId) {
            $package->items()->create([
                'counselling_service_id' => $serviceId,
                'quantity' => 1,
                'sort_order' => $index,
            ]);
        }
    }

    private function services(): array
    {
        return CounsellingService::query()
            ->orderBy('id')
            ->get()
            ->map(fn (CounsellingService $service): array => [
                'id' => $service->id,
                'name' => $service->name ?? $service->title ?? 'Service #'.$service->id,
            ])
            ->all();
    }
}
