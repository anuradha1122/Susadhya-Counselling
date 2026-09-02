<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CaseFollowUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseFollowUpRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in(
                    CaseFollowUp::statuses()
                ),
            ],
        ];
    }
}
