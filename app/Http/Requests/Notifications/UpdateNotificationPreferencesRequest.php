<?php

namespace App\Http\Requests\Notifications;

use App\Enums\NotificationEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preferences' => [
                'required',
                'array',
            ],

            'preferences.*.event_type' => [
                'required',
                'string',
                Rule::enum(NotificationEventType::class),
            ],

            'preferences.*.in_app_enabled' => [
                'required',
                'boolean',
            ],

            'preferences.*.email_enabled' => [
                'required',
                'boolean',
            ],

            'preferences.*.sms_enabled' => [
                'required',
                'boolean',
            ],
        ];
    }
}
