<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;

class StoreCmsMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.media.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=5000,max_height=5000',
            ],

            'alt_text' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'CMS images may not exceed 5 MB.',

            'alt_text.required' => 'Alternative text is required for accessibility.',
        ];
    }
}
