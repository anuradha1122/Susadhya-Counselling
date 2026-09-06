<?php

namespace App\Http\Requests\Client\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketReplyRequest extends FormRequest
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
            'body' => [
                'required',
                'string',
                'min:2',
                'max:10000',
            ],
        ];
    }
}
