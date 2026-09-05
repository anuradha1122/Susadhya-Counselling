<?php

namespace App\Http\Requests\Compliance;

use App\Models\PrivacyRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrivacyRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'privacy.requests.submit'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in(
                    PrivacyRequest::types()
                ),
            ],

            'request_details' => [
                'required',
                'string',
                'max:5000',
            ],

            'scope' => [
                'nullable',
                'array',
                'max:20',
            ],

            'scope.*' => [
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select a privacy request type.',

            'type.in' => 'The selected privacy request type is invalid.',

            'request_details.required' => 'Please provide details about your request.',
        ];
    }
}
