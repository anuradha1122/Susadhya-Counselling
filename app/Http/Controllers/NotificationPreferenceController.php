<?php

namespace App\Http\Controllers;

use App\Enums\NotificationEventType;
use App\Http\Requests\Notifications\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    public function edit(Request $request): Response
    {
        $stored = NotificationPreference::query()
            ->where('user_id', $request->user()->id)
            ->get()
            ->keyBy('event_type');

        $preferences = collect(
            NotificationEventType::cases()
        )->map(
            function (
                NotificationEventType $event
            ) use ($stored): array {
                $preference = $stored->get(
                    $event->value
                );

                return [
                    'event_type' => $event->value,
                    'label' => $event->label(),
                    'is_critical' => $event->isCritical(),

                    'in_app_enabled' => $event->isCritical()
                        ? true
                        : ($preference?->in_app_enabled ?? true),

                    'email_enabled' => $preference
                        ?->email_enabled ?? true,

                    'sms_enabled' => $preference
                        ?->sms_enabled ?? false,
                ];
            }
        )->values();

        return Inertia::render(
            'Notifications/Preferences',
            [
                'preferences' => $preferences,

                'emailAvailable' => (bool) config(
                    'notifications.email.enabled',
                    true
                ),

                'smsAvailable' => (bool) config(
                    'notifications.sms.enabled',
                    false
                ),
            ]
        );
    }

    public function update(
        UpdateNotificationPreferencesRequest $request
    ): RedirectResponse {
        foreach (
            $request->validated('preferences') as $preference
        ) {
            $event = NotificationEventType::from(
                $preference['event_type']
            );

            NotificationPreference::query()
                ->updateOrCreate(
                    [
                        'user_id' => $request
                            ->user()
                            ->id,

                        'event_type' => $event->value,
                    ],
                    [
                        /*
                         * Critical events always retain in-app delivery.
                         */
                        'in_app_enabled' => $event->isCritical()
                            ? true
                            : $preference['in_app_enabled'],

                        'email_enabled' => $preference[
                            'email_enabled'
                        ],

                        'sms_enabled' => $preference[
                            'sms_enabled'
                        ],
                    ]
                );
        }

        return back()->with(
            'success',
            'Notification preferences updated.'
        );
    }
}
