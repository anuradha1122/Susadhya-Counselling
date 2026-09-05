<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\CaseEscalation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseEscalationRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in(CaseEscalation::statuses()),
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
            'resolution_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}
