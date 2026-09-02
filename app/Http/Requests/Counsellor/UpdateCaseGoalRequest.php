<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CaseGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseGoalRequest extends FormRequest
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
            'target_date' => [
                'nullable',
                'date',
            ],
            'status' => [
                'required',
                Rule::in(CaseGoal::statuses()),
            ],
            'outcome_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
