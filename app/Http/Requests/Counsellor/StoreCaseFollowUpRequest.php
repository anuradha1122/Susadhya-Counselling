<?php

namespace App\Http\Requests\Counsellor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'clinical.records.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
            'due_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
