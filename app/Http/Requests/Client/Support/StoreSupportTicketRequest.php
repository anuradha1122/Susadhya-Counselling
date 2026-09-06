<?php

namespace App\Http\Requests\Client\Support;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
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
            'category' => [
                'required',
                Rule::in(
                    SupportTicket::categories()
                ),
            ],

            'subject' => [
                'required',
                'string',
                'max:200',
            ],

            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
        ];
    }
}
