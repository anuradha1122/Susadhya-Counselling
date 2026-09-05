<?php

namespace App\Http\Requests\Reports;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'from' => [
                'nullable',
                'date',
            ],
            'to' => [
                'nullable',
                'date',
                'after_or_equal:from',
            ],
            'counsellor_profile_id' => [
                'nullable',
                'integer',
                'exists:counsellor_profiles,id',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_RESCHEDULED,
                    Appointment::STATUS_COMPLETED,
                    Appointment::STATUS_CANCELLED,
                    Appointment::STATUS_NO_SHOW,
                ]),
            ],
            'mode' => [
                'nullable',
                Rule::in([
                    Appointment::MODE_ONLINE,
                    Appointment::MODE_IN_PERSON,
                ]),
            ],
            'method' => [
                'nullable',
                Rule::in([
                    'gateway',
                    'manual',
                    'waived',
                ]),
            ],
            'currency' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Za-z]{3}$/',
            ],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        $from = $validated['from']
            ?? now()->startOfMonth()->toDateString();

        $to = $validated['to']
            ?? now()->toDateString();

        return [
            'from' => $from,
            'to' => $to,
            'counsellor_profile_id' => $validated['counsellor_profile_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'method' => $validated['method'] ?? null,
            'currency' => isset($validated['currency'])
                ? strtoupper($validated['currency'])
                : null,
        ];
    }
}
