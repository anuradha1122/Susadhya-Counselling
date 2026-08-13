<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCounsellorAvailabilityRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'counsellor_profile_id' => [
                'nullable',
                'integer',
                'exists:counsellor_profiles,id',
            ],
            'day_of_week' => [
                'required',
                'integer',
                'between:0,6',
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
            'mode' => [
                'required',
                Rule::in(CounsellorAvailabilityRule::modes()),
            ],
            'slot_duration_minutes' => [
                'required',
                'integer',
                Rule::in([15, 30, 45, 60, 90, 120]),
            ],
            'buffer_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:120',
            ],
            'capacity_per_slot' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],
            'timezone' => [
                'required',
                'timezone',
                'max:80',
            ],
            'effective_from' => [
                'nullable',
                'date',
            ],
            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.between' => 'Please select a valid day of the week.',
            'start_time.before' => 'The start time must be before the end time.',
            'end_time.after' => 'The end time must be after the start time.',
            'mode.in' => 'Please select a valid counselling mode.',
            'slot_duration_minutes.in' => 'Please select a valid slot duration.',
            'timezone.timezone' => 'Please select a valid timezone.',
            'effective_until.after_or_equal' => 'The effective until date must be on or after the effective from date.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->boolean('is_active', true)) {
                return;
            }

            $counsellorProfileId = $this->resolvedCounsellorProfileId();

            if (! $counsellorProfileId) {
                $validator->errors()->add(
                    'counsellor_profile_id',
                    'A counsellor profile is required for availability.'
                );

                return;
            }

            $overlapExists = CounsellorAvailabilityRule::query()
                ->where('counsellor_profile_id', $counsellorProfileId)
                ->where('day_of_week', (int) $this->input('day_of_week'))
                ->where('is_active', true)
                ->when(
                    $this->availabilityRuleIdToIgnore(),
                    fn (Builder $query, int $id) => $query->whereKeyNot($id)
                )
                ->where('start_time', '<', $this->input('end_time'))
                ->where('end_time', '>', $this->input('start_time'))
                ->where(function (Builder $query): void {
                    $effectiveUntil = $this->input('effective_until');

                    if ($effectiveUntil) {
                        $query
                            ->whereNull('effective_from')
                            ->orWhereDate('effective_from', '<=', $effectiveUntil);

                        return;
                    }

                    $query->whereRaw('1 = 1');
                })
                ->where(function (Builder $query): void {
                    $effectiveFrom = $this->input('effective_from');

                    if ($effectiveFrom) {
                        $query
                            ->whereNull('effective_until')
                            ->orWhereDate('effective_until', '>=', $effectiveFrom);

                        return;
                    }

                    $query->whereRaw('1 = 1');
                })
                ->exists();

            if ($overlapExists) {
                $validator->errors()->add(
                    'start_time',
                    'This availability overlaps with an existing active availability rule for the selected counsellor and day.'
                );
            }
        });
    }

    protected function resolvedCounsellorProfileId(): ?int
    {
        $routeProfile = $this->route('counsellor_profile')
            ?? $this->route('counsellorProfile')
            ?? $this->route('profile');

        if ($routeProfile instanceof CounsellorProfile) {
            return $routeProfile->id;
        }

        if (is_numeric($routeProfile)) {
            return (int) $routeProfile;
        }

        if ($this->filled('counsellor_profile_id')) {
            return (int) $this->input('counsellor_profile_id');
        }

        $userProfile = $this->user()?->counsellorProfile;

        if ($userProfile instanceof CounsellorProfile) {
            return $userProfile->id;
        }

        return null;
    }

    protected function availabilityRuleIdToIgnore(): ?int
    {
        return null;
    }
}
