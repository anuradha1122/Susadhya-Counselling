<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCounsellorAvailabilityBreakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'counsellor_availability_rule_id' => [
                'nullable',
                'integer',
                'exists:counsellor_availability_rules,id',
            ],
            'title' => [
                'required',
                'string',
                'max:120',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                'before:end_time',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.before' => 'The break start time must be before the break end time.',
            'end_time.after' => 'The break end time must be after the break start time.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $rule = $this->resolvedAvailabilityRule();

            if (! $rule) {
                $validator->errors()->add(
                    'counsellor_availability_rule_id',
                    'An availability rule is required for this break.'
                );

                return;
            }

            if ($this->input('start_time') < $rule->start_time->format('H:i')) {
                $validator->errors()->add(
                    'start_time',
                    'The break start time must be within the availability time range.'
                );
            }

            if ($this->input('end_time') > $rule->end_time->format('H:i')) {
                $validator->errors()->add(
                    'end_time',
                    'The break end time must be within the availability time range.'
                );
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->boolean('is_active', true)) {
                return;
            }

            $overlapExists = CounsellorAvailabilityBreak::query()
                ->where('counsellor_availability_rule_id', $rule->id)
                ->where('is_active', true)
                ->when(
                    $this->availabilityBreakIdToIgnore(),
                    fn (Builder $query, int $id) => $query->whereKeyNot($id)
                )
                ->where('start_time', '<', $this->input('end_time'))
                ->where('end_time', '>', $this->input('start_time'))
                ->exists();

            if ($overlapExists) {
                $validator->errors()->add(
                    'start_time',
                    'This break overlaps with an existing active break for the selected availability rule.'
                );
            }
        });
    }

    protected function resolvedAvailabilityRule(): ?CounsellorAvailabilityRule
    {
        $rule = $this->route('availability_rule')
            ?? $this->route('availabilityRule')
            ?? $this->route('counsellorAvailabilityRule')
            ?? $this->route('rule');

        if ($rule instanceof CounsellorAvailabilityRule) {
            return $rule;
        }

        if (is_numeric($rule)) {
            return CounsellorAvailabilityRule::find((int) $rule);
        }

        if ($this->filled('counsellor_availability_rule_id')) {
            return CounsellorAvailabilityRule::find(
                (int) $this->input('counsellor_availability_rule_id')
            );
        }

        return null;
    }

    protected function availabilityBreakIdToIgnore(): ?int
    {
        return null;
    }
}
