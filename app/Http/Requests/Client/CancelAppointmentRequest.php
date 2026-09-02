<?php

namespace App\Http\Requests\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
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
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.max' => 'Cancellation reason cannot be longer than 1000 characters.',
        ];
    }
}
