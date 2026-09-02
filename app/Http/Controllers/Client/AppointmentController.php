<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Services\Appointments\AppointmentSlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function store(
        StoreAppointmentRequest $request,
        AppointmentSlotService $slotService
    ): RedirectResponse {
        $validated = $request->validated();

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $counsellorProfile = CounsellorProfile::query()
            ->whereKey($validated['counsellor_profile_id'])
            ->where('status', 'active')
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->firstOrFail();

        $appointment = DB::transaction(function () use (
            $request,
            $validated,
            $clientProfile,
            $counsellorProfile,
            $slotService
        ): Appointment {
            $isAvailable = $slotService->isSlotAvailable(
                counsellorProfile: $counsellorProfile,
                date: $validated['appointment_date'],
                startTime: $validated['start_time'],
                endTime: $validated['end_time'],
                clientProfile: $clientProfile,
                mode: $validated['mode']
            );

            if (! $isAvailable) {
                throw ValidationException::withMessages([
                    'start_time' => 'The selected appointment slot is no longer available.',
                ]);
            }

            $appointment = Appointment::query()->create([
                'client_profile_id' => $clientProfile->id,
                'counsellor_profile_id' => $counsellorProfile->id,
                'counselling_service_id' => $validated['counselling_service_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'timezone' => 'Asia/Colombo',
                'mode' => $validated['mode'],
                'status' => Appointment::STATUS_PENDING,
                'client_notes' => $validated['client_notes'] ?? null,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $appointment->statusHistories()->create([
                'from_status' => null,
                'to_status' => Appointment::STATUS_PENDING,
                'reason' => 'Appointment requested by client.',
                'metadata' => [
                    'source' => 'client_booking',
                    'appointment_date' => $appointment->appointment_date?->toDateString(),
                    'start_time' => $appointment->start_time?->format('H:i'),
                    'end_time' => $appointment->end_time?->format('H:i'),
                    'mode' => $appointment->mode,
                ],
                'changed_by' => $request->user()->id,
            ]);

            return $appointment;
        });

        return redirect()
            ->route('client.counsellors.show', $appointment->counsellor_profile_id)
            ->with('success', 'Appointment request submitted successfully.');
    }
}
