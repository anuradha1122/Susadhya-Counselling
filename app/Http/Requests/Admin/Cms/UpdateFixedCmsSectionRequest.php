<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedCmsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.sections.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'heading' => [
                'nullable',
                'string',
                'max:300',
            ],

            'subheading' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'content' => [
                'nullable',
                'array',
            ],

            'content.eyebrow' => [
                'nullable',
                'string',
                'max:200',
            ],

            'content.body' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'content.image_path' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'content.image_alt' => [
                'nullable',
                'string',
                'max:300',
            ],

            'content.primary_cta_label' => [
                'nullable',
                'string',
                'max:120',
            ],

            'content.primary_cta_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content.secondary_cta_label' => [
                'nullable',
                'string',
                'max:120',
            ],

            'content.secondary_cta_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content.limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:24',
            ],

            'content.trust_items' => [
                'nullable',
                'array',
                'max:12',
            ],

            'content.trust_items.*' => [
                'required',
                'string',
                'max:300',
            ],

            'content.points' => [
                'nullable',
                'array',
                'max:20',
            ],

            'content.points.*' => [
                'required',
                'string',
                'max:500',
            ],

            'content.items' => [
                'nullable',
                'array',
                'max:20',
            ],

            'content.items.*.number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'content.items.*.title' => [
                'nullable',
                'string',
                'max:300',
            ],

            'content.items.*.description' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}
