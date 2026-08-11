<?php

namespace App\Http\Requests\Admin;

use App\Models\CounsellingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCounsellingServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(
            'create',
            CounsellingService::class
        );
    }

    public function rules(): array
    {
        return $this->serviceRules();
    }

    protected function serviceRules(): array
    {
        return [
            'service_category_id' => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')
                    ->where(fn ($query) => $query->where(
                        'status',
                        'active'
                    )),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('counselling_services', 'name'),
            ],
            'slug' => [
                'required',
                'string',
                'max:170',
                'alpha_dash',
                Rule::unique('counselling_services', 'slug'),
            ],
            'short_description' => [
                'required',
                'string',
                'max:500',
            ],
            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'duration_minutes' => [
                'required',
                'integer',
                'min:15',
                'max:480',
            ],
            'service_mode' => [
                'required',
                Rule::in([
                    'online',
                    'in_person',
                    'both',
                ]),
            ],
            'target_age_group' => [
                'required',
                Rule::in([
                    'children',
                    'adolescents',
                    'adults',
                    'seniors',
                    'all_ages',
                    'custom',
                ]),
            ],
            'minimum_age' => [
                Rule::requiredIf(
                    fn (): bool => $this->input(
                        'target_age_group'
                    ) === 'custom'
                ),
                'nullable',
                'integer',
                'min:0',
                'max:120',
            ],
            'maximum_age' => [
                Rule::requiredIf(
                    fn (): bool => $this->input(
                        'target_age_group'
                    ) === 'custom'
                ),
                'nullable',
                'integer',
                'min:0',
                'max:120',
                'gte:minimum_age',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                Rule::in(['LKR']),
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

    protected function prepareForValidation(): void
    {
        if ($this->input('target_age_group') !== 'custom') {
            $this->merge([
                'minimum_age' => null,
                'maximum_age' => null,
            ]);
        }

        if ($this->filled('currency')) {
            $this->merge([
                'currency' => strtoupper(
                    (string) $this->input('currency')
                ),
            ]);
        }
    }
}
