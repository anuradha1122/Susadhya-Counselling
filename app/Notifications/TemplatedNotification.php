<?php

namespace App\Notifications;

use App\Models\NotificationDispatch;
use App\Notifications\Channels\TrackedDatabaseChannel;
use App\Notifications\Channels\TrackedMailChannel;
use App\Notifications\Channels\TrackedSmsChannel;
use App\Services\Notifications\NotificationTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemplatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $dispatchId
    ) {}

    public function via(object $notifiable): array
    {
        $dispatch = $this->dispatch();

        if (! $dispatch) {
            return [];
        }

        return collect($dispatch->channels ?? [])
            ->map(fn (string $channel) => match ($channel) {
                'database' => TrackedDatabaseChannel::class,
                'mail' => TrackedMailChannel::class,
                'sms' => TrackedSmsChannel::class,
                default => null,
            })
            ->filter()
            ->values()
            ->all();
    }

    public function toDatabase(object $notifiable): array
    {
        $dispatch = $this->requiredDispatch();

        $template = $dispatch->template();

        abort_unless($template, 500);

        $rendered = app(
            NotificationTemplateRenderer::class
        )->render(
            $template,
            $dispatch->payload ?? []
        );

        return [
            'dispatch_id' => $dispatch->id,
            'event_type' => $dispatch->event_type,
            'title' => $rendered['subject'],
            'body' => $rendered['in_app_body'],
            'url' => $dispatch->url,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dispatch = $this->requiredDispatch();

        $template = $dispatch->template();

        abort_unless($template, 500);

        $rendered = app(
            NotificationTemplateRenderer::class
        )->render(
            $template,
            $dispatch->payload ?? []
        );

        $message = (new MailMessage)
            ->subject($rendered['subject'])
            ->greeting('Hello '.$notifiable->name.',')
            ->line($rendered['email_body'])
            ->line(
                'For privacy and security, sensitive counselling information is available only after signing in to Susadhya.'
            );

        if ($dispatch->url) {
            $message->action(
                'Open Susadhya',
                url($dispatch->url)
            );
        }

        return $message;
    }

    public function toSms(object $notifiable): string
    {
        $dispatch = $this->requiredDispatch();

        $template = $dispatch->template();

        abort_unless($template, 500);

        $rendered = app(
            NotificationTemplateRenderer::class
        )->render(
            $template,
            $dispatch->payload ?? []
        );

        return $rendered['sms_body'];
    }

    public function dispatch(): ?NotificationDispatch
    {
        return NotificationDispatch::query()
            ->with('deliveries')
            ->find($this->dispatchId);
    }

    private function requiredDispatch(): NotificationDispatch
    {
        return NotificationDispatch::query()
            ->findOrFail($this->dispatchId);
    }
}
