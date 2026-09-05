<?php

namespace App\Http\Requests\Compliance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompletePrivacyRequestRequest extends FormRequest
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
            'execution_notes' => [
                'required',
                'string',
                'max:10000',
            ],

            'deletion_strategy' => [
                'nullable',
                'string',
                Rule::in([
                    'not_applicable',
                    'retained_due_to_legal_obligation',
                    'manual_anonymisation',
                    'manual_deletion',
                    'partial_deletion',
                    'correction_completed',
                ]),
            ],
        ];
    }
}
