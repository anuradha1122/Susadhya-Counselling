<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\UpdateSystemSettingsRequest;
use App\Services\AdminOperations\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function index(
        SystemSettingService $settings
    ): Response {
        return Inertia::render(
            'Admin/Settings/Index',
            [
                'settings' => $settings->all(),
            ]
        );
    }

    public function update(
        UpdateSystemSettingsRequest $request,
        SystemSettingService $settings
    ): RedirectResponse {
        $settings->updateMany(
            $request->validated('settings'),
            $request->user()
        );

        return back()->with(
            'success',
            'System settings updated.'
        );
    }
}
