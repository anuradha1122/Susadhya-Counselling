<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\UpdatePublicSiteSettingRequest;
use App\Models\CmsMedia;
use App\Models\PublicSiteSetting;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicSiteSettingController extends Controller
{
    public function edit(): Response
    {
        $settings =
            PublicSiteSetting::query()
                ->firstOrCreate([
                    'site_name' => 'Susadhya Counselling',
                ]);

        return Inertia::render(
            'Admin/Cms/Settings/Edit',
            [
                'settings' => $settings,

                'media' => CmsMedia::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->latest()
                    ->limit(250)
                    ->get()
                    ->map(
                        fn (
                            CmsMedia $item
                        ): array => [
                            'uuid' => $item->uuid,

                            'url' => $item
                                ->publicUrl(),

                            'original_name' => $item
                                ->original_name,

                            'alt_text' => $item
                                ->alt_text,
                        ]
                    )
                    ->values(),
            ]
        );
    }

    public function update(
        UpdatePublicSiteSettingRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $settings =
            PublicSiteSetting::query()
                ->firstOrCreate([
                    'site_name' => 'Susadhya Counselling',
                ]);

        $settings->update([
            ...$request->validated(),

            'updated_by' => $request->user()->id,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.site_settings_updated',
            action: 'update',
            subject: $settings,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Public website settings updated.'
        );
    }
}
