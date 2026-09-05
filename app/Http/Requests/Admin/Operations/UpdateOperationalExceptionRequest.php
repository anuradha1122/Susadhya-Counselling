<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\OperationalException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperationalExceptionRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in(OperationalException::statuses()),
            ],
            'priority' => [
                'required',
                Rule::in(OperationalException::priorities()),
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
            'resolution_notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}
