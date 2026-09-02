<?php

namespace App\Http\Requests\Client;

use App\Models\ClientIntake;
use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('client')
            && ClientProfile::query()
                ->where('user_id', $this->user()->id)
                ->exists();
    }

    public function rules(): array
    {
        return [
            'presenting_concerns' => ['nullable', 'string', 'max:5000'],
            'current_symptoms' => ['nullable', 'string', 'max:5000'],
            'counselling_goals' => ['nullable', 'string', 'max:5000'],
            'preferred_session_mode' => [
                'nullable',
                Rule::in(ClientIntake::preferredSessionModes()),
            ],
            'previous_counselling' => ['nullable', 'boolean'],
            'previous_counselling_notes' => ['nullable', 'string', 'max:5000'],
            'medication_notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'consent_terms_accepted' => ['nullable', 'boolean'],
            'consent_privacy_accepted' => ['nullable', 'boolean'],
            'consent_telehealth_accepted' => ['nullable', 'boolean'],
            'consent_data_processing_accepted' => ['nullable', 'boolean'],
            'screening_answers' => ['nullable', 'array'],
            'screening_answers.*.answer_score' => ['nullable', 'integer', 'min:0', 'max:3'],
            'screening_answers.*.answer_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
