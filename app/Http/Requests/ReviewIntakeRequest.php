<?php

namespace App\Http\Requests;

use App\Models\ClientIntake;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'counsellor',
        ]) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(ClientIntake::reviewStatuses()),
            ],
            'risk_level' => [
                'required',
                Rule::in(ClientIntake::riskLevels()),
            ],
            'reviewer_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select review status.',
            'status.in' => 'Please select a valid review status.',
            'risk_level.required' => 'Please select risk level.',
            'risk_level.in' => 'Please select a valid risk level.',
            'reviewer_notes.max' => 'Reviewer notes cannot be longer than 5000 characters.',
        ];
    }
}
