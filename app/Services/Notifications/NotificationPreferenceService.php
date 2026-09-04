<?php

namespace App\Services\Notifications;

use App\Enums\NotificationEventType;
use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    public function channels(
        User $user,
        NotificationEventType $event
    ): array {
        $preference = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('event_type', $event->value)
            ->first();

        $inAppEnabled = $event->isCritical()
            ? true
            : ($preference?->in_app_enabled ?? true);

        $emailEnabled = $preference?->email_enabled ?? true;

        $smsEnabled = $preference?->sms_enabled ?? false;

        $channels = [];

        if ($inAppEnabled) {
            $channels[] = 'database';
        }

        if (
            config('notifications.email.enabled', true)
            && $emailEnabled
            && filled($user->email)
        ) {
            $channels[] = 'mail';
        }

        if (
            config('notifications.sms.enabled', false)
            && $smsEnabled
            && filled($this->smsRecipient($user))
        ) {
            $channels[] = 'sms';
        }

        return array_values(array_unique($channels));
    }

    public function smsRecipient(User $user): ?string
    {
        return $user->phone
            ?? data_get($user, 'clientProfile.phone')
            ?? data_get($user, 'counsellorProfile.phone');
    }
}
