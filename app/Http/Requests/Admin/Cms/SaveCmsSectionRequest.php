<?php

namespace App\Http\Requests\Admin\Cms;

use App\Models\CmsSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCmsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.sections.manage'
        ) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => is_array(
                $this->input('content')
            )
                ? $this->input('content')
                : [],

            'is_active' => $this->boolean(
                'is_active'
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'key' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],

            'type' => [
                'required',
                Rule::in(
                    CmsSection::types()
                ),
            ],

            'heading' => [
                'nullable',
                'string',
                'max:220',
            ],

            'subheading' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'content' => [
                'nullable',
                'array',
            ],

            'content.eyebrow' => [
                'nullable',
                'string',
                'max:180',
            ],

            'content.body' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'content.image_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content.image_alt' => [
                'nullable',
                'string',
                'max:255',
            ],

            'content.image_position' => [
                'nullable',
                Rule::in([
                    'left',
                    'right',
                ]),
            ],

            'content.visual' => [
                'nullable',
                Rule::in([
                    'session',
                    'journey',
                    'privacy',
                ]),
            ],

            'content.primary_cta_label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'content.primary_cta_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'content.secondary_cta_label' => [
                'nullable',
                'string',
                'max:100',
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
                'max:10',
            ],

            'content.trust_items.*' => [
                'required',
                'string',
                'max:255',
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

            'content.items.*.value' => [
                'nullable',
                'string',
                'max:100',
            ],

            'content.items.*.label' => [
                'nullable',
                'string',
                'max:255',
            ],

            'content.items.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'content.items.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'content.items.*.icon' => [
                'nullable',
                Rule::in([
                    'shield',
                    'badge',
                    'heart',
                ]),
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
