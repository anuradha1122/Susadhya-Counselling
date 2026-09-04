<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;

class NotificationTemplateRenderer
{
    public function render(
        NotificationTemplate $template,
        array $payload
    ): array {
        return [
            'subject' => $this->replace(
                $template->subject ?? $template->name,
                $payload
            ),

            'in_app_body' => $this->replace(
                $template->in_app_body,
                $payload
            ),

            'email_body' => $this->replace(
                $template->email_body ?? $template->in_app_body,
                $payload
            ),

            'sms_body' => $this->replace(
                $template->sms_body ?? $template->in_app_body,
                $payload
            ),
        ];
    }

    private function replace(
        ?string $text,
        array $payload
    ): string {
        if (! $text) {
            return '';
        }

        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/',
            function (array $matches) use ($payload): string {
                $value = data_get(
                    $payload,
                    $matches[1],
                    ''
                );

                if (is_array($value) || is_object($value)) {
                    return '';
                }

                return (string) $value;
            },
            $text
        ) ?? $text;
    }
}
