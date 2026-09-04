<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.finance.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => [
                'required',
                'integer',
                'exists:appointments,id',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
            ],

            'paid_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'Please select an appointment.',

            'appointment_id.exists' => 'The selected appointment could not be found.',

            'reference.required' => 'Please enter the payment reference.',

            'paid_at.date' => 'Please enter a valid payment date and time.',

            'paid_at.before_or_equal' => 'The payment date and time cannot be in the future.',
        ];
    }
}
