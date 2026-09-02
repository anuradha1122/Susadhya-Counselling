<?php

namespace App\Http\Requests\Client;

use App\Models\ClientIntake;
use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitIntakeRequest extends FormRequest
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
            'presenting_concerns' => ['required', 'string', 'max:5000'],
            'current_symptoms' => ['nullable', 'string', 'max:5000'],
            'counselling_goals' => ['required', 'string', 'max:5000'],
            'preferred_session_mode' => [
                'required',
                Rule::in(ClientIntake::preferredSessionModes()),
            ],
            'previous_counselling' => ['nullable', 'boolean'],
            'previous_counselling_notes' => ['nullable', 'string', 'max:5000'],
            'medication_notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:50'],
            'emergency_contact_relationship' => ['required', 'string', 'max:255'],
            'consent_terms_accepted' => ['accepted'],
            'consent_privacy_accepted' => ['accepted'],
            'consent_telehealth_accepted' => ['accepted'],
            'consent_data_processing_accepted' => ['accepted'],
            'screening_answers' => ['required', 'array'],
            'screening_answers.*.answer_score' => ['nullable', 'integer', 'min:0', 'max:3'],
            'screening_answers.*.answer_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $answers = $this->input('screening_answers', []);

                foreach (ClientIntake::screeningQuestions() as $question) {
                    $key = $question['key'];

                    if (
                        ! isset($answers[$key])
                        || ! array_key_exists('answer_score', $answers[$key])
                        || $answers[$key]['answer_score'] === ''
                        || $answers[$key]['answer_score'] === null
                    ) {
                        $validator->errors()->add(
                            "screening_answers.{$key}.answer_score",
                            'Please answer all screening questions before submitting.'
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'presenting_concerns.required' => 'Please describe the main concern you want support with.',
            'counselling_goals.required' => 'Please describe what you hope to achieve through counselling.',
            'preferred_session_mode.required' => 'Please select your preferred session mode.',
            'emergency_contact_name.required' => 'Please provide an emergency contact name.',
            'emergency_contact_phone.required' => 'Please provide an emergency contact phone number.',
            'emergency_contact_relationship.required' => 'Please provide your relationship to the emergency contact.',
            'consent_terms_accepted.accepted' => 'You must accept the counselling terms before submitting.',
            'consent_privacy_accepted.accepted' => 'You must accept the privacy consent before submitting.',
            'consent_telehealth_accepted.accepted' => 'You must accept the telehealth consent before submitting.',
            'consent_data_processing_accepted.accepted' => 'You must accept the data processing consent before submitting.',
        ];
    }
}
