<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(
            'create',
            ServiceCategory::class
        );
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('service_categories', 'name'),
            ],
            'slug' => [
                'required',
                'string',
                'max:140',
                'alpha_dash',
                Rule::unique('service_categories', 'slug'),
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
