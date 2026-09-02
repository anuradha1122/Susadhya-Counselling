<?php

namespace App\Http\Requests\Client;

use App\Models\Appointment;
use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
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
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'mode' => [
                'required',
                Rule::in(Appointment::modes()),
            ],
            'client_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.required' => 'Please select a new appointment date.',
            'appointment_date.after_or_equal' => 'New appointment date cannot be in the past.',
            'start_time.required' => 'Please select a new appointment slot.',
            'end_time.required' => 'Please select a new appointment slot.',
            'mode.required' => 'Please select an appointment mode.',
            'client_notes.max' => 'Client notes cannot be longer than 2000 characters.',
        ];
    }
}
