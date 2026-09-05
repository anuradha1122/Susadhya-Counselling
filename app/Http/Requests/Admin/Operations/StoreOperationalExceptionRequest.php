<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\OperationalException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOperationalExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'admin.operations.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(OperationalException::types()),
            ],
            'priority' => [
                'required',
                Rule::in(OperationalException::priorities()),
            ],
            'source_type' => [
                'nullable',
                'string',
                'max:50',
                Rule::in([
                    'appointment',
                    'availability',
                    'notification',
                    'account',
                    'content',
                    'other',
                ]),
            ],
            'source_reference' => [
                'nullable',
                'string',
                'max:100',
            ],
            'title' => [
                'required',
                'string',
                'max:180',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'due_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
