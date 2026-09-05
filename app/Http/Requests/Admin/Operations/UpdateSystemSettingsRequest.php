<?php

namespace App\Http\Requests\Admin\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'settings.update'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'settings' => [
                'required',
                'array',
            ],
            'settings.*' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
