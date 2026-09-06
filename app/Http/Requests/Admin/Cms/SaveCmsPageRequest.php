<?php

namespace App\Http\Requests\Admin\Cms;

use App\Models\CmsPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.pages.manage'
        ) === true;
    }

    protected function prepareForValidation(): void
    {
        $title = $this->input('title');

        if (
            blank($this->input('slug'))
            && filled($title)
        ) {
            $this->merge([
                'slug' => Str::slug(
                    (string) $title
                ),
            ]);
        }
    }

    public function rules(): array
    {
        $page =
            $this->route('cmsPage');

        return [
            'title' => [
                'required',
                'string',
                'max:180',
            ],

            'slug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(
                    'cms_pages',
                    'slug'
                )->ignore(
                    $page?->id
                ),
            ],

            'menu_label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'body' => [
                'nullable',
                'string',
                'max:100000',
            ],

            'template' => [
                'required',
                Rule::in(
                    CmsPage::templates()
                ),
            ],

            'show_in_header' => [
                'required',
                'boolean',
            ],

            'show_in_footer' => [
                'required',
                'boolean',
            ],

            'menu_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
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

            'canonical_url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'og_title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'og_description' => [
                'nullable',
                'string',
                'max:320',
            ],

            'og_image_path' => [
                'nullable',
                'string',
                'max:500',
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
