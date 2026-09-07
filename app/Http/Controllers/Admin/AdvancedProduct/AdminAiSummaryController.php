<?php

namespace App\Http\Controllers\Admin\AdvancedProduct;

use App\Http\Controllers\Controller;
use App\Models\AdminAiSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiSummaryController extends Controller
{
    public function index(Request $request): Response
    {
        $summaries = AdminAiSummary::query()
            ->with(['creator:id,name', 'reviewer:id,name'])
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/AdvancedProduct/AiSummaries/Index', [
            'summaries' => $summaries,
            'filters' => $request->only('status'),
            'statuses' => ['draft', 'reviewed', 'archived'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_type' => ['required', 'string', 'max:100'],
            'source_label' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'source_text' => ['nullable', 'string'],
            'summary' => ['required', 'string'],
            'risk_flags' => ['nullable', 'array'],
        ]);

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'draft';

        AdminAiSummary::create($data);

        return back()->with('success', 'Admin summary saved as draft.');
    }

    public function review(Request $request, AdminAiSummary $summary): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['reviewed', 'archived'])],
        ]);

        $summary->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Summary review status updated.');
    }
}
