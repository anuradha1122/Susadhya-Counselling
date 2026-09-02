<?php

namespace App\Http\Requests\Counsellor;

use App\Models\ClientCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicalNoteRequest extends FormRequest
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
            'counselling_session_id' => [
                'nullable',
                'integer',
                'exists:counselling_sessions,id',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'note' => [
                'required',
                'string',
                'max:20000',
            ],
            'formulation' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'intervention' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'risk_assessment' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'plan' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'risk_level' => [
                'required',
                Rule::in(ClientCase::riskLevels()),
            ],
        ];
    }
}
