<?php

namespace App\Http\Controllers\Admin\AdvancedProduct;

use App\Http\Controllers\Controller;
use App\Models\ContentTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentTranslationController extends Controller
{
    public function index(Request $request): Response
    {
        $translations = ContentTranslation::query()
            ->with('reviewer:id,name')
            ->when($request->string('locale')->toString(), function ($query, string $locale): void {
                $query->where('locale', $locale);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/AdvancedProduct/Translations/Index', [
            'translations' => $translations,
            'filters' => $request->only('locale'),
            'locales' => ['en', 'si', 'ta'],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AdvancedProduct/Translations/Form', [
            'translation' => null,
            'locales' => ['en', 'si', 'ta'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['reviewed_by'] = $request->user()->id;
        $data['reviewed_at'] = now();

        ContentTranslation::create($data);

        return redirect()
            ->route('admin.advanced-products.translations.index')
            ->with('success', 'Translation created.');
    }

    public function edit(ContentTranslation $translation): Response
    {
        return Inertia::render('Admin/AdvancedProduct/Translations/Form', [
            'translation' => $translation,
            'locales' => ['en', 'si', 'ta'],
        ]);
    }

    public function update(Request $request, ContentTranslation $translation): RedirectResponse
    {
        $data = $this->validated($request, $translation);

        $data['reviewed_by'] = $request->user()->id;
        $data['reviewed_at'] = now();

        $translation->update($data);

        return redirect()
            ->route('admin.advanced-products.translations.index')
            ->with('success', 'Translation updated.');
    }

    public function destroy(ContentTranslation $translation): RedirectResponse
    {
        $translation->delete();

        return redirect()
            ->route('admin.advanced-products.translations.index')
            ->with('success', 'Translation deleted.');
    }

    private function validated(Request $request, ?ContentTranslation $translation = null): array
    {
        $data = $request->validate([
            'translatable_type' => ['required', 'string', 'max:255'],
            'translatable_id' => ['required', 'integer', 'min:1'],
            'locale' => ['required', 'string', 'max:10'],
            'field' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string'],
        ]);

        $exists = ContentTranslation::query()
            ->where('translatable_type', $data['translatable_type'])
            ->where('translatable_id', $data['translatable_id'])
            ->where('locale', $data['locale'])
            ->where('field', $data['field'])
            ->when($translation, fn ($query) => $query->whereKeyNot($translation->id))
            ->exists();

        abort_if($exists, 422, 'This translation already exists.');

        return $data;
    }
}
