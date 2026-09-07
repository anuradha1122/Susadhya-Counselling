<?php

namespace App\Http\Controllers\Admin\AdvancedProduct;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvancedProduct\StoreGroupCounsellingProgramRequest;
use App\Http\Requests\Admin\AdvancedProduct\UpdateGroupCounsellingProgramRequest;
use App\Models\CounsellingService;
use App\Models\CounsellorProfile;
use App\Models\GroupCounsellingProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupCounsellingProgramController extends Controller
{
    public function index(Request $request): Response
    {
        $programs = GroupCounsellingProgram::query()
            ->with(['service', 'leadCounsellorProfile.user'])
            ->withCount('enrollments')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/AdvancedProduct/GroupCounselling/Index', [
            'programs' => $programs,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AdvancedProduct/GroupCounselling/Form', [
            'program' => null,
            'services' => $this->services(),
            'counsellors' => $this->counsellors(),
        ]);
    }

    public function store(StoreGroupCounsellingProgramRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        GroupCounsellingProgram::create($data);

        return redirect()
            ->route('admin.advanced-products.group-counselling.index')
            ->with('success', 'Group counselling program created.');
    }

    public function edit(GroupCounsellingProgram $program): Response
    {
        return Inertia::render('Admin/AdvancedProduct/GroupCounselling/Form', [
            'program' => $program,
            'services' => $this->services(),
            'counsellors' => $this->counsellors(),
        ]);
    }

    public function update(UpdateGroupCounsellingProgramRequest $request, GroupCounsellingProgram $program): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;

        $program->update($data);

        return redirect()
            ->route('admin.advanced-products.group-counselling.index')
            ->with('success', 'Group counselling program updated.');
    }

    public function destroy(GroupCounsellingProgram $program): RedirectResponse
    {
        abort_if($program->enrollments()->exists(), 422, 'Cannot delete a program with enrollments.');

        $program->delete();

        return redirect()
            ->route('admin.advanced-products.group-counselling.index')
            ->with('success', 'Group counselling program deleted.');
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

    private function counsellors(): array
    {
        return CounsellorProfile::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->map(fn (CounsellorProfile $profile): array => [
                'id' => $profile->id,
                'name' => $profile->user?->name ?? 'Counsellor #'.$profile->id,
            ])
            ->all();
    }
}
