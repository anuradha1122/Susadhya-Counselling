<?php

namespace App\Http\Requests\Admin\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'support.admin.manage'
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

            'is_internal' => [
                'required',
                'boolean',
            ],
        ];
    }
}
