<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCounsellorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('counsellors.create');
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
                Rule::unique('counsellor_profiles', 'user_id'),
            ],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('counsellor_profiles', 'registration_number'),
            ],
            'professional_title' => [
                'nullable',
                'string',
                'max:150',
            ],
            'nic' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('counsellor_profiles', 'nic'),
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
                    'other',
                    'prefer_not_to_say',
                ]),
            ],
            'years_of_experience' => [
                'required',
                'integer',
                'min:0',
                'max:80',
            ],
            'biography' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'city' => [
                'nullable',
                'string',
                'max:150',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
            'specialization_ids' => [
                'nullable',
                'array',
            ],
            'specialization_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('specializations', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'languages' => [
                'nullable',
                'array',
            ],
            'languages.*.language_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('languages', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'languages.*.proficiency' => [
                'required',
                Rule::in([
                    'basic',
                    'conversational',
                    'fluent',
                    'native',
                ]),
            ],
            'qualifications' => [
                'nullable',
                'array',
            ],
            'qualifications.*.qualification' => [
                'required',
                'string',
                'max:255',
            ],
            'qualifications.*.institution' => [
                'required',
                'string',
                'max:255',
            ],
            'qualifications.*.field_of_study' => [
                'nullable',
                'string',
                'max:255',
            ],
            'qualifications.*.year_completed' => [
                'nullable',
                'integer',
                'min:1900',
                'max:'.now()->year,
            ],
            'qualifications.*.certificate_number' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'The selected user already has a counsellor profile.',
            'specialization_ids.*.distinct' => 'A specialization cannot be selected more than once.',
            'languages.*.language_id.distinct' => 'A language cannot be selected more than once.',
        ];
    }
}
