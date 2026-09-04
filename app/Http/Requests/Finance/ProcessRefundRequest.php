<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payments.refunds.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'manual_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
