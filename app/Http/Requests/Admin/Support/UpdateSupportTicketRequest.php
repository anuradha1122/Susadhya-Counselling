<?php

namespace App\Http\Requests\Admin\Support;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupportTicketRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in(
                    SupportTicket::statuses()
                ),
            ],

            'priority' => [
                'required',
                Rule::in(
                    SupportTicket::priorities()
                ),
            ],

            'owner_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'resolution_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
