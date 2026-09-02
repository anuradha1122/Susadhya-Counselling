<?php

namespace App\Http\Requests\Admin;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([
            'admin',
            'super_admin',
        ]) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(self::managedStatuses()),
            ],
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
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select an appointment status.',
            'status.in' => 'Please select a valid appointment status.',
            'meeting_link.url' => 'Please enter a valid meeting link.',
            'meeting_link.max' => 'Meeting link cannot be longer than 500 characters.',
            'location.max' => 'Location cannot be longer than 255 characters.',
            'cancellation_reason.max' => 'Cancellation reason cannot be longer than 1000 characters.',
            'admin_notes.max' => 'Admin notes cannot be longer than 2000 characters.',
        ];
    }

    public static function managedStatuses(): array
    {
        return [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_NO_SHOW,
        ];
    }
}
