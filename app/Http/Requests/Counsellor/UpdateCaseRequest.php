<?php

namespace App\Http\Requests\Counsellor;

use App\Models\ClientCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in([
                    ClientCase::STATUS_OPEN,
                    ClientCase::STATUS_ON_HOLD,
                ]),
            ],
            'summary' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'formulation' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'risk_level' => [
                'required',
                Rule::in(ClientCase::riskLevels()),
            ],
            'risk_flag' => [
                'required',
                'boolean',
            ],
            'risk_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
