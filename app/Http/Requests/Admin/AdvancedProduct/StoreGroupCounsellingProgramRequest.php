<?php

namespace App\Http\Requests\Admin\AdvancedProduct;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupCounsellingProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('advanced-products.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'counselling_service_id' => ['nullable', 'exists:counselling_services,id'],
            'lead_counsellor_profile_id' => ['nullable', 'exists:counsellor_profiles,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:group_counselling_programs,slug'],
            'description' => ['nullable', 'string'],
            'mode' => ['required', Rule::in(['online', 'in_person', 'hybrid'])],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:2', 'max:100'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'price' => ['required', 'numeric', 'min:0'],
            'requires_approval' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
