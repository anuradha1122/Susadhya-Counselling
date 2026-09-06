<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;

class SaveCmsFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.faqs.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'question' => [
                'required',
                'string',
                'max:500',
            ],

            'answer' => [
                'required',
                'string',
                'max:20000',
            ],

            'display_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }
}
