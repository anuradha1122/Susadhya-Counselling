<?php

namespace App\Http\Requests\Admin\AdvancedProduct;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('advanced-products.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:service_packages,slug'],
            'description' => ['nullable', 'string'],
            'sessions_count' => ['required', 'integer', 'min:1', 'max:100'],
            'validity_days' => ['required', 'integer', 'min:1', 'max:730'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_subscription' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:counselling_services,id'],
        ];
    }
}
