<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreClientRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guest();
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
            'email' => mb_strtolower(
                $this->normalizeString($this->input('email'))
            ),
            'phone' => $this->normalizeString(
                $this->input('phone')
            ),
            'preferred_language' => $this->normalizeNullableString(
                $this->input('preferred_language')
            ),
            'communication_consent' => $this->boolean(
                'communication_consent'
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
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s().]{7,30}$/',
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
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
            ],
            'terms_accepted' => [
                'accepted',
            ],
            'privacy_policy_accepted' => [
                'accepted',
            ],
            'communication_consent' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using digits, spaces, +, -, brackets, or dots.',
            'preferred_language.required' => 'Select your preferred language.',
            'preferred_language.in' => 'Select a valid preferred language.',
            'terms_accepted.accepted' => 'You must accept the terms of service to register.',
            'privacy_policy_accepted.accepted' => 'You must accept the privacy policy to register.',
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
