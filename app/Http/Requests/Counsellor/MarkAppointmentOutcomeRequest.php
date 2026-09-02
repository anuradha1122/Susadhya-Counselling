<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CounsellorProfile;
use Illuminate\Foundation\Http\FormRequest;

class MarkAppointmentOutcomeRequest extends FormRequest
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
            'counsellor_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'counsellor_notes.max' => 'Counsellor notes cannot be longer than 2000 characters.',
        ];
    }
}
