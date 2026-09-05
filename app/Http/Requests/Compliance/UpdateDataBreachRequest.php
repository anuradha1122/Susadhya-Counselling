<?php

namespace App\Http\Requests\Compliance;

use App\Models\DataBreach;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataBreachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'compliance.breaches.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:180',
            ],

            'severity' => [
                'required',
                'string',
                Rule::in(
                    DataBreach::severities()
                ),
            ],

            'status' => [
                'required',
                'string',
                Rule::in(
                    DataBreach::statuses()
                ),
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'detected_at' => [
                'required',
                'date',
            ],

            'occurred_at' => [
                'nullable',
                'date',
                'before_or_equal:detected_at',
            ],

            'reported_to_authority_at' => [
                'nullable',
                'date',
            ],

            'affected_subject_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000000',
            ],

            'data_categories' => [
                'nullable',
                'array',
                'max:30',
            ],

            'data_categories.*' => [
                'string',
                'max:100',
            ],

            'systems_affected' => [
                'nullable',
                'array',
                'max:30',
            ],

            'systems_affected.*' => [
                'string',
                'max:100',
            ],

            'summary' => [
                'required',
                'string',
                'max:10000',
            ],

            'containment_actions' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'notification_decision' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'authority_reference' => [
                'nullable',
                'string',
                'max:160',
            ],
        ];
    }
}
