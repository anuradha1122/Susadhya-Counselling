<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.pages.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'body' => [
                'nullable',
                'string',
                'max:100000',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:320',
            ],

            'og_image_path' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'robots_index' => [
                'required',
                'boolean',
            ],

            'robots_follow' => [
                'required',
                'boolean',
            ],
        ];
    }
}
