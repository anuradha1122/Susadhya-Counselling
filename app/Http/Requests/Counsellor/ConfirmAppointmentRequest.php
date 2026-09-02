<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CounsellorProfile;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmAppointmentRequest extends FormRequest
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
            'meeting_link' => [
                'nullable',
                'url',
                'max:500',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
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
            'meeting_link.url' => 'Please enter a valid meeting link.',
            'meeting_link.max' => 'Meeting link cannot be longer than 500 characters.',
            'location.max' => 'Location cannot be longer than 255 characters.',
            'counsellor_notes.max' => 'Counsellor notes cannot be longer than 2000 characters.',
        ];
    }
}
