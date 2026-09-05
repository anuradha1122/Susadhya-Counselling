<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\StoreContentSnippetRequest;
use App\Http\Requests\Admin\Operations\UpdateContentSnippetRequest;
use App\Models\ContentSnippet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentSnippetController extends Controller
{
    public function index(Request $request): Response
    {
        $snippets = ContentSnippet::query()
            ->with([
                'updater:id,name',
            ])
            ->orderBy('placement')
            ->orderBy('key')
            ->paginate(20);

        return Inertia::render(
            'Admin/ContentSnippets/Index',
            [
                'snippets' => $snippets,
                'statuses' => ContentSnippet::statuses(),
            ]
        );
    }

    public function store(
        StoreContentSnippetRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        ContentSnippet::query()->create([
            ...$validated,
            'published_at' => $validated['status']
                    === ContentSnippet::STATUS_PUBLISHED
                        ? now()
                        : null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Content snippet created.'
        );
    }

    public function update(
        UpdateContentSnippetRequest $request,
        ContentSnippet $contentSnippet
    ): RedirectResponse {
        $validated = $request->validated();

        $contentSnippet->forceFill([
            ...$validated,
            'published_at' => $validated['status']
                    === ContentSnippet::STATUS_PUBLISHED
                        ? ($contentSnippet->published_at ?? now())
                        : null,
            'updated_by' => $request->user()->id,
        ])->save();

        return back()->with(
            'success',
            'Content snippet updated.'
        );
    }
}
