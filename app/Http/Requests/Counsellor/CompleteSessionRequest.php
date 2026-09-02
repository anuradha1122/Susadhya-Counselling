<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CounsellingSession;
use App\Models\CounsellorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('counsellor')
            && CounsellorProfile::query()
                ->where('user_id', $this->user()->id)
                ->exists();
    }

    public function rules(): array
    {
        return [
            'presenting_summary' => ['nullable', 'string', 'max:5000'],
            'intervention_summary' => ['required', 'string', 'max:5000'],
            'outcome_summary' => ['required', 'string', 'max:5000'],
            'client_visible_summary' => ['nullable', 'string', 'max:5000'],
            'homework' => ['nullable', 'string', 'max:5000'],
            'private_notes' => ['nullable', 'string', 'max:8000'],
            'clinical_risk_level' => [
                'required',
                Rule::in(CounsellingSession::riskLevels()),
            ],
            'follow_up_recommended' => ['nullable', 'boolean'],
            'follow_up_notes' => ['nullable', 'string', 'max:5000'],
            'next_session_recommended_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'intervention_summary.required' => 'Please enter the intervention summary.',
            'outcome_summary.required' => 'Please enter the session outcome summary.',
            'clinical_risk_level.required' => 'Please select the clinical risk level.',
            'clinical_risk_level.in' => 'Please select a valid clinical risk level.',
        ];
    }
}
