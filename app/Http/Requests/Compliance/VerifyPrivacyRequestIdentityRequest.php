<?php

namespace App\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPrivacyRequestIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'compliance.privacy.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
