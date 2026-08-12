<?php

namespace App\Http\Requests\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientPrivacySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $clientProfile = $this->user()?->clientProfile;

        return $clientProfile instanceof ClientProfile
            && $this->user()->can('update', $clientProfile);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'communication_consent' => $this->boolean('communication_consent'),
            'emergency_contact_permission' => $this->boolean('emergency_contact_permission'),
            'allow_email_updates' => $this->boolean('allow_email_updates'),
            'allow_sms_updates' => $this->boolean('allow_sms_updates'),
            'allow_whatsapp_updates' => $this->boolean('allow_whatsapp_updates'),
            'share_profile_with_assigned_counsellor' => $this->boolean(
                'share_profile_with_assigned_counsellor'
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'communication_consent' => [
                'required',
                'boolean',
            ],
            'emergency_contact_permission' => [
                'required',
                'boolean',
            ],
            'allow_email_updates' => [
                'required',
                'boolean',
            ],
            'allow_sms_updates' => [
                'required',
                'boolean',
            ],
            'allow_whatsapp_updates' => [
                'required',
                'boolean',
            ],
            'share_profile_with_assigned_counsellor' => [
                'required',
                'boolean',
            ],
        ];
    }
}
