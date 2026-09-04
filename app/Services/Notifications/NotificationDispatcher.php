<?php

namespace App\Services\Notifications;

use App\Enums\NotificationEventType;
use App\Models\NotificationDispatch;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Notifications\TemplatedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationDispatcher
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences
    ) {}

    public function dispatch(
        User $user,
        NotificationEventType $event,
        string $templateKey,
        array $payload,
        ?Model $source = null,
        ?string $url = null,
        ?string $deduplicationKey = null
    ): ?NotificationDispatch {
        $template = NotificationTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        /*
         * This intentionally becomes a no-op if templates have not yet
         * been seeded. It protects older module tests/factories while
         * M15 is being installed.
         */
        if (! $template) {
            return null;
        }

        $channels = $this->preferences->channels(
            $user,
            $event
        );

        $deduplicationKey ??= implode(':', [
            $event->value,
            $source ? class_basename($source) : 'system',
            $source?->getKey() ?? 'none',
            $user->getKey(),
            Str::uuid(),
        ]);

        $existing = NotificationDispatch::query()
            ->where(
                'deduplication_key',
                $deduplicationKey
            )
            ->first();

        if ($existing) {
            return $existing;
        }

        $dispatch = DB::transaction(
            function () use (
                $user,
                $event,
                $templateKey,
                $payload,
                $source,
                $url,
                $deduplicationKey,
                $channels
            ): NotificationDispatch {
                $dispatch = NotificationDispatch::create([
                    'user_id' => $user->id,
                    'event_type' => $event->value,
                    'template_key' => $templateKey,
                    'channels' => $channels,
                    'source_type' => $source
                        ? class_basename($source)
                        : null,
                    'source_id' => $source
                        ? (string) $source->getKey()
                        : null,
                    'url' => $url,
                    'payload' => $payload,
                    'deduplication_key' => $deduplicationKey,
                ]);

                foreach ($channels as $channel) {
                    $dispatch->deliveries()->create([
                        'channel' => $channel,
                        'status' => 'pending',
                        'attempt' => 1,
                    ]);
                }

                return $dispatch;
            }
        );

        $user->notify(
            new TemplatedNotification($dispatch->id)
        );

        $dispatch->update([
            'dispatched_at' => now(),
        ]);

        return $dispatch->fresh('deliveries');
    }
}
