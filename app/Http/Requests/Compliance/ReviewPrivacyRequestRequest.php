<?php

namespace App\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewPrivacyRequestRequest extends FormRequest
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
            'decision' => [
                'required',
                'string',
                Rule::in([
                    'approved',
                    'rejected',
                ]),
            ],

            'review_notes' => [
                'required',
                'string',
                'max:10000',
            ],

            'legal_basis' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'review_notes.required' => 'Review notes are required so the compliance decision remains traceable.',
        ];
    }
}
