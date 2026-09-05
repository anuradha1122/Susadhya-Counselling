<?php

namespace App\Http\Requests\Compliance;

use App\Models\RetentionPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRetentionPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'compliance.retention.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'retention_days' => [
                'nullable',
                'integer',
                'min:1',
                'max:36500',
                'required_if:enabled,true',
            ],

            'action' => [
                'required',
                'string',
                Rule::in(
                    RetentionPolicy::actions()
                ),
            ],

            'enabled' => [
                'required',
                'boolean',
            ],

            'automatic_execution' => [
                'required',
                'boolean',
            ],

            'legal_basis' => [
                'nullable',
                'string',
                'max:5000',
                'required_if:enabled,true',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $automatic =
                    $this->boolean(
                        'automatic_execution'
                    );

                if (! $automatic) {
                    return;
                }

                $policy =
                    $this->route(
                        'retentionPolicy'
                    )
                    ?? $this->route(
                        'retention_policy'
                    );

                if (
                    ! $policy instanceof RetentionPolicy
                ) {
                    return;
                }

                $allowedCategories =
                    config(
                        'compliance.retention.automatic_categories',
                        []
                    );

                if (
                    ! in_array(
                        $policy->category,
                        $allowedCategories,
                        true
                    )
                ) {
                    $validator->errors()->add(
                        'automatic_execution',
                        'Automatic retention execution is not allowed for this category.'
                    );
                }

                if (
                    $this->string('action')
                        ->toString()
                    !== RetentionPolicy::ACTION_DELETE
                ) {
                    $validator->errors()->add(
                        'action',
                        'Automatic retention execution currently supports deletion only.'
                    );
                }
            },
        ];
    }
}
