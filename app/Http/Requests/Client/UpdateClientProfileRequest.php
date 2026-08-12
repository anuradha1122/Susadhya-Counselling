<?php

namespace App\Http\Requests\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientProfileRequest extends FormRequest
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
            'first_name' => $this->normalizeString(
                $this->input('first_name')
            ),
            'last_name' => $this->normalizeString(
                $this->input('last_name')
            ),
            'preferred_name' => $this->normalizeNullableString(
                $this->input('preferred_name')
            ),
            'phone' => $this->normalizeString(
                $this->input('phone')
            ),
            'alternate_phone' => $this->normalizeNullableString(
                $this->input('alternate_phone')
            ),
            'pronouns' => $this->normalizeNullableString(
                $this->input('pronouns')
            ),
            'address_line_1' => $this->normalizeNullableString(
                $this->input('address_line_1')
            ),
            'address_line_2' => $this->normalizeNullableString(
                $this->input('address_line_2')
            ),
            'city' => $this->normalizeNullableString(
                $this->input('city')
            ),
            'district' => $this->normalizeNullableString(
                $this->input('district')
            ),
            'province' => $this->normalizeNullableString(
                $this->input('province')
            ),
            'postal_code' => $this->normalizeNullableString(
                $this->input('postal_code')
            ),
            'preferred_language' => $this->normalizeNullableString(
                $this->input('preferred_language')
            ),
            'occupation' => $this->normalizeNullableString(
                $this->input('occupation')
            ),
            'gender' => $this->normalizeNullableString(
                $this->input('gender')
            ),
            'marital_status' => $this->normalizeNullableString(
                $this->input('marital_status')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:120',
            ],
            'last_name' => [
                'required',
                'string',
                'max:120',
            ],
            'preferred_name' => [
                'nullable',
                'string',
                'max:120',
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],
            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'non_binary',
                    'other',
                    'prefer_not_to_say',
                ]),
            ],
            'pronouns' => [
                'nullable',
                'string',
                'max:80',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s().]{7,30}$/',
            ],
            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s().]{7,30}$/',
            ],
            'address_line_1' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'address_line_2' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'city' => [
                'nullable',
                'string',
                'max:120',
            ],
            'district' => [
                'nullable',
                'string',
                'max:120',
            ],
            'province' => [
                'nullable',
                'string',
                'max:120',
            ],
            'postal_code' => [
                'nullable',
                'string',
                'max:20',
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
            'preferred_contact_method' => [
                'required',
                Rule::in([
                    'email',
                    'phone',
                    'sms',
                    'whatsapp',
                    'no_preference',
                ]),
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:150',
            ],
            'marital_status' => [
                'nullable',
                Rule::in([
                    'single',
                    'married',
                    'separated',
                    'divorced',
                    'widowed',
                    'other',
                    'prefer_not_to_say',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using digits, spaces, +, -, brackets, or dots.',
            'alternate_phone.regex' => 'Enter a valid alternate phone number using digits, spaces, +, -, brackets, or dots.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'preferred_language.required' => 'Select your preferred language.',
            'preferred_language.in' => 'Select a valid preferred language.',
        ];
    }

    private function normalizeString(mixed $value): string
    {
        return trim((string) $value);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}
