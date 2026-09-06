<?php

namespace App\Http\Requests\Client\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'support.client.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'overall_rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'technical_rating' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'comment' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'would_recommend' => [
                'nullable',
                'boolean',
            ],

            'consent_to_follow_up' => [
                'required',
                'boolean',
            ],
        ];
    }
}
