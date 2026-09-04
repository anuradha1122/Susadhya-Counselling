<?php

namespace App\Http\Requests\Client;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Services\Appointments\AppointmentSlotService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('client')
            && ClientProfile::query()
                ->where('user_id', $this->user()->id)
                ->exists();
    }

    public function rules(): array
    {
        return [
            'counsellor_profile_id' => [
                'required',
                'integer',
                'exists:counsellor_profiles,id',
            ],
            'counselling_service_id' => [
                'nullable',
                'integer',
                'exists:counselling_services,id',
            ],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'mode' => [
                'required',
                Rule::in(Appointment::modes()),
            ],
            'client_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $clientProfile = $this->clientProfile();
                $counsellorProfile = $this->activeCounsellorProfile();

                if (! $clientProfile) {
                    $validator->errors()->add(
                        'client_profile_id',
                        'Your client profile could not be found.'
                    );

                    return;
                }

                if (! $counsellorProfile) {
                    $validator->errors()->add(
                        'counsellor_profile_id',
                        'The selected counsellor is not available for booking.'
                    );

                    return;
                }

                $slotService = app(AppointmentSlotService::class);

                $isAvailable = $slotService->isSlotAvailable(
                    counsellorProfile: $counsellorProfile,
                    date: $this->string('appointment_date')->toString(),
                    startTime: $this->string('start_time')->toString(),
                    endTime: $this->string('end_time')->toString(),
                    clientProfile: $clientProfile,
                    mode: $this->string('mode')->toString()
                );

                if (! $isAvailable) {
                    $validator->errors()->add(
                        'start_time',
                        'The selected appointment slot is no longer available.'
                    );
                }
            },
        ];
    }

    public function clientProfile(): ?ClientProfile
    {
        if (! $this->user()) {
            return null;
        }

        return ClientProfile::query()
            ->where('user_id', $this->user()->id)
            ->first();
    }

    public function activeCounsellorProfile(): ?CounsellorProfile
    {
        $counsellorProfileId = $this->integer('counsellor_profile_id');

        if (! $counsellorProfileId) {
            return null;
        }

        return CounsellorProfile::query()
            ->whereKey($counsellorProfileId)
            ->where('status', 'active')
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->first();
    }

    public function messages(): array
    {
        return [
            'counsellor_profile_id.required' => 'Please select a counsellor.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past.',
            'start_time.required' => 'Please select a start time.',
            'end_time.required' => 'Please select an end time.',
            'end_time.after' => 'Appointment end time must be after the start time.',
            'mode.required' => 'Please select an appointment mode.',
        ];
    }
}
