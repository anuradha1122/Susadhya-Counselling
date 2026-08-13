<?php

namespace App\Http\Requests\Availability;

use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCounsellorBlockedSlotRequest extends FormRequest
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
            'blocked_date' => [
                'required',
                'date',
            ],
            'is_full_day' => [
                'sometimes',
                'boolean',
            ],
            'start_time' => [
                'nullable',
                'required_unless:is_full_day,1,true,on',
                'date_format:H:i',
                'before:end_time',
            ],
            'end_time' => [
                'nullable',
                'required_unless:is_full_day,1,true,on',
                'date_format:H:i',
                'after:start_time',
            ],
            'reason' => [
                'nullable',
                'string',
                'max:120',
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
            'start_time.required_unless' => 'Start time is required when the blocked slot is not full day.',
            'end_time.required_unless' => 'End time is required when the blocked slot is not full day.',
            'start_time.before' => 'The blocked slot start time must be before the end time.',
            'end_time.after' => 'The blocked slot end time must be after the start time.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $counsellorProfileId = $this->resolvedCounsellorProfileId();

            if (! $counsellorProfileId) {
                $validator->errors()->add(
                    'counsellor_profile_id',
                    'A counsellor profile is required for this blocked slot.'
                );

                return;
            }

            $overlapExists = CounsellorBlockedSlot::query()
                ->where('counsellor_profile_id', $counsellorProfileId)
                ->whereDate('blocked_date', $this->input('blocked_date'))
                ->when(
                    $this->blockedSlotIdToIgnore(),
                    fn (Builder $query, int $id) => $query->whereKeyNot($id)
                )
                ->where(function (Builder $query): void {
                    if ($this->boolean('is_full_day')) {
                        $query->whereRaw('1 = 1');

                        return;
                    }

                    $query
                        ->where('is_full_day', true)
                        ->orWhere(function (Builder $query): void {
                            $query
                                ->where('start_time', '<', $this->input('end_time'))
                                ->where('end_time', '>', $this->input('start_time'));
                        });
                })
                ->exists();

            if ($overlapExists) {
                $validator->errors()->add(
                    'blocked_date',
                    'This blocked slot overlaps with an existing blocked slot for the selected counsellor.'
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

    protected function blockedSlotIdToIgnore(): ?int
    {
        return null;
    }
}
