<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideRefundRequest extends FormRequest
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
            'decision' => [
                'required',
                Rule::in([
                    'approve',
                    'reject',
                ]),
            ],
            'approved_amount' => [
                Rule::requiredIf(
                    $this->input(
                        'decision'
                    ) === 'approve'
                ),
                'nullable',
                'numeric',
                'gt:0',
            ],
            'decision_notes' => [
                Rule::requiredIf(
                    $this->input(
                        'decision'
                    ) === 'reject'
                ),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
