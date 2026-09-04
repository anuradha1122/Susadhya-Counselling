<?php

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'notifications.templates.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'in_app_body' => [
                'required',
                'string',
                'max:3000',
            ],

            'email_body' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'sms_body' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}
