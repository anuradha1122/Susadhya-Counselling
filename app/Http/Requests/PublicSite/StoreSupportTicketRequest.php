<?php

namespace App\Http\Requests\PublicSite;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],

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

            'message' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],

            'privacy_acknowledged' => [
                'accepted',
            ],

            /*
             * Honeypot.
             *
             * Real users never see/fill this field.
             */
            'website' => [
                'nullable',
                'string',
                'max:0',
            ],
        ];
    }
}
