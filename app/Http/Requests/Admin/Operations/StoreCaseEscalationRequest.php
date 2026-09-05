<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\CaseEscalation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseEscalationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'admin.case-escalations.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'case_reference' => [
                'required',
                'string',
                'max:100',
            ],
            'reason_code' => [
                'required',
                Rule::in(CaseEscalation::reasons()),
            ],
            'priority' => [
                'required',
                Rule::in(CaseEscalation::priorities()),
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'due_at' => [
                'nullable',
                'date',
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
