<?php

namespace App\Http\Requests\Admin;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCounsellingServiceRequest extends StoreCounsellingServiceRequest
{
    public function authorize(): bool
    {
        return Gate::allows(
            'update',
            $this->route('counselling_service')
        );
    }

    protected function serviceRules(): array
    {
        $service = $this->route('counselling_service');
        $rules = parent::serviceRules();

        $rules['service_category_id'] = [
            'required',
            'integer',
            Rule::exists('service_categories', 'id')
                ->where(function ($query) use ($service) {
                    $query->where('status', 'active');

                    if ($service?->service_category_id) {
                        $query->orWhere(
                            'id',
                            $service->service_category_id
                        );
                    }
                }),
        ];

        $rules['name'] = [
            'required',
            'string',
            'max:150',
            Rule::unique(
                'counselling_services',
                'name'
            )->ignore($service),
        ];

        $rules['slug'] = [
            'required',
            'string',
            'max:170',
            'alpha_dash',
            Rule::unique(
                'counselling_services',
                'slug'
            )->ignore($service),
        ];

        return $rules;
    }
}
