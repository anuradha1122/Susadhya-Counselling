<?php

namespace App\Http\Requests\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientEmergencyContactRequest extends FormRequest
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
            'name' => $this->normalizeString($this->input('name')),
            'relationship' => $this->normalizeString(
                $this->input('relationship')
            ),
            'phone' => $this->normalizeString($this->input('phone')),
            'alternate_phone' => $this->normalizeNullableString(
                $this->input('alternate_phone')
            ),
            'email' => mb_strtolower(
                $this->normalizeNullableString($this->input('email')) ?? ''
            ) ?: null,
            'may_contact_in_emergency' => $this->boolean(
                'may_contact_in_emergency'
            ),
            'is_primary' => $this->boolean('is_primary'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'relationship' => [
                'required',
                'string',
                'max:120',
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
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'may_contact_in_emergency' => [
                'required',
                'boolean',
            ],
            'is_primary' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number using digits, spaces, +, -, brackets, or dots.',
            'alternate_phone.regex' => 'Enter a valid alternate phone number using digits, spaces, +, -, brackets, or dots.',
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
