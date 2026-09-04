<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
            'client'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'requested_amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }
}
