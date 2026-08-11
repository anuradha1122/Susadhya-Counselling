<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(
            'update',
            $this->route('service_category')
        );
    }

    public function rules(): array
    {
        $category = $this->route('service_category');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique(
                    'service_categories',
                    'name'
                )->ignore($category),
            ],
            'slug' => [
                'required',
                'string',
                'max:140',
                'alpha_dash',
                Rule::unique(
                    'service_categories',
                    'slug'
                )->ignore($category),
            ],
            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],
            'display_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }
}
