<?php

namespace App\Http\Requests\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $clientProfile = $this->user()?->clientProfile;

        return $clientProfile instanceof ClientProfile
            && $this->user()->can('update', $clientProfile);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preferred_language' => $this->normalizeNullableString(
                $this->input('preferred_language')
            ),
            'general_availability_notes' => $this->normalizeNullableString(
                $this->input('general_availability_notes')
            ),
            'accessibility_requirements' => $this->normalizeNullableString(
                $this->input('accessibility_requirements')
            ),
            'additional_preferences' => $this->normalizeNullableString(
                $this->input('additional_preferences')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'preferred_counselling_mode' => [
                'required',
                Rule::in([
                    'online',
                    'in_person',
                    'no_preference',
                ]),
            ],
            'preferred_counsellor_gender' => [
                'required',
                Rule::in([
                    'male',
                    'female',
                    'no_preference',
                ]),
            ],
            'preferred_language' => [
                'required',
                Rule::in([
                    'sinhala',
                    'tamil',
                    'english',
                    'no_preference',
                    'other',
                ]),
            ],
            'general_availability_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'accessibility_requirements' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'additional_preferences' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_counselling_mode.required' => 'Select your preferred counselling mode.',
            'preferred_counselling_mode.in' => 'Select a valid counselling mode.',
            'preferred_counsellor_gender.required' => 'Select your preferred counsellor gender.',
            'preferred_counsellor_gender.in' => 'Select a valid counsellor gender preference.',
            'preferred_language.required' => 'Select your preferred language.',
            'preferred_language.in' => 'Select a valid preferred language.',
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
