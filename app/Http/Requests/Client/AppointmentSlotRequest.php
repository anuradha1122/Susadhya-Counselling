<?php

namespace App\Http\Requests\Client;

use App\Models\Appointment;
use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentSlotRequest extends FormRequest
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
            'mode' => [
                'required',
                Rule::in(Appointment::modes()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past.',
            'mode.required' => 'Please select an appointment mode.',
        ];
    }
}
