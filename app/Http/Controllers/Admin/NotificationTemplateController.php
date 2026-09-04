<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\UpdateNotificationTemplateRequest;
use App\Models\NotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render(
            'Admin/Notifications/Templates',
            [
                'templates' => NotificationTemplate::query()
                    ->orderBy('key')
                    ->get()
                    ->map(fn (NotificationTemplate $template) => [
                        'id' => $template->id,
                        'key' => $template->key,
                        'name' => $template->name,
                        'subject' => $template->subject,
                        'in_app_body' => $template->in_app_body,
                        'email_body' => $template->email_body,
                        'sms_body' => $template->sms_body,
                        'variables' => $template->variables ?? [],
                        'is_active' => $template->is_active,
                    ]),
            ]
        );
    }

    public function update(
        UpdateNotificationTemplateRequest $request,
        NotificationTemplate $template
    ): RedirectResponse {
        $template->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'Notification template updated.'
        );
    }
}
