<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CancelAppointmentRequest;
use App\Http\Requests\Client\RescheduleAppointmentRequest;
use App\Http\Requests\Client\RescheduleAppointmentSlotRequest;
use App\Http\Requests\Client\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Services\Appointments\AppointmentSlotService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => [
                'nullable',
                Rule::in(Appointment::statuses()),
            ],
            'period' => [
                'nullable',
                Rule::in([
                    'upcoming',
                    'past',
                    'all',
                ]),
            ],
        ]);

        $period = $filters['period'] ?? 'upcoming';
        $status = $filters['status'] ?? null;

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $appointments = Appointment::query()
            ->with([
                'counsellorProfile.user:id,name,email,phone,is_active',
                'counsellorProfile:id,user_id,professional_title,city,status',
                'counsellingService:id,name,service_code,service_mode,duration_minutes,base_fee,status',
            ])
            ->where('client_profile_id', $clientProfile->id)
            ->when($status, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($period === 'upcoming', function (Builder $query): void {
                $query->whereDate('appointment_date', '>=', now()->toDateString());
            })
            ->when($period === 'past', function (Builder $query): void {
                $query->whereDate('appointment_date', '<', now()->toDateString());
            })
            ->when($period === 'upcoming', function (Builder $query): void {
                $query
                    ->orderBy('appointment_date')
                    ->orderBy('start_time');
            })
            ->when($period !== 'upcoming', function (Builder $query): void {
                $query
                    ->orderByDesc('appointment_date')
                    ->orderByDesc('start_time');
            })
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Appointment $appointment): array => $this->appointmentPayload($appointment));

        return Inertia::render('Client/Appointments/Index', [
            'appointments' => $appointments,
            'filters' => [
                'status' => $status ?? '',
                'period' => $period,
            ],
            'options' => [
                'statuses' => collect(Appointment::statuses())
                    ->map(fn (string $status): array => [
                        'value' => $status,
                        'label' => str($status)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
                'periods' => [
                    [
                        'value' => 'upcoming',
                        'label' => 'Upcoming',
                    ],
                    [
                        'value' => 'past',
                        'label' => 'Past',
                    ],
                    [
                        'value' => 'all',
                        'label' => 'All',
                    ],
                ],
            ],
        ]);
    }

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

        DB::transaction(function () use (
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
            ->route('client.appointments.index')
            ->with('success', 'Appointment request submitted successfully.');
    }

    public function rescheduleSlots(
        RescheduleAppointmentSlotRequest $request,
        Appointment $appointment,
        AppointmentSlotService $slotService
    ): JsonResponse {
        $validated = $request->validated();

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($appointment->client_profile_id === $clientProfile->id, 404);

        if (! $appointment->canBeRescheduled()) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment cannot be rescheduled.',
            ]);
        }

        $counsellorProfile = $this->activeCounsellorProfileForAppointment($appointment);

        $slots = $slotService->availableSlotsForDate(
            counsellorProfile: $counsellorProfile,
            date: $validated['appointment_date'],
            clientProfile: $clientProfile,
            mode: $validated['mode'],
            ignoreAppointmentId: $appointment->id
        );

        return response()->json([
            'appointment_id' => $appointment->id,
            'counsellor_profile_id' => $counsellorProfile->id,
            'appointment_date' => $validated['appointment_date'],
            'mode' => $validated['mode'],
            'slots' => $slots->values(),
        ]);
    }

    public function reschedule(
        RescheduleAppointmentRequest $request,
        Appointment $appointment,
        AppointmentSlotService $slotService
    ): RedirectResponse {
        $validated = $request->validated();

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($appointment->client_profile_id === $clientProfile->id, 404);

        if (! $appointment->canBeRescheduled()) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment cannot be rescheduled.',
            ]);
        }

        $counsellorProfile = $this->activeCounsellorProfileForAppointment($appointment);

        DB::transaction(function () use (
            $appointment,
            $request,
            $validated,
            $clientProfile,
            $counsellorProfile,
            $slotService
        ): void {
            $isAvailable = $slotService->isSlotAvailable(
                counsellorProfile: $counsellorProfile,
                date: $validated['appointment_date'],
                startTime: $validated['start_time'],
                endTime: $validated['end_time'],
                clientProfile: $clientProfile,
                mode: $validated['mode'],
                ignoreAppointmentId: $appointment->id
            );

            if (! $isAvailable) {
                throw ValidationException::withMessages([
                    'start_time' => 'The selected reschedule slot is no longer available.',
                ]);
            }

            $fromStatus = $appointment->status;

            $appointment->forceFill([
                'status' => Appointment::STATUS_RESCHEDULED,
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => Appointment::STATUS_RESCHEDULED,
                'reason' => 'Appointment rescheduled by client.',
                'metadata' => [
                    'source' => 'client_reschedule_original',
                    'new_appointment_date' => $validated['appointment_date'],
                    'new_start_time' => $validated['start_time'],
                    'new_end_time' => $validated['end_time'],
                    'new_mode' => $validated['mode'],
                ],
                'changed_by' => $request->user()->id,
            ]);

            $newAppointment = Appointment::query()->create([
                'client_profile_id' => $clientProfile->id,
                'counsellor_profile_id' => $counsellorProfile->id,
                'counselling_service_id' => $appointment->counselling_service_id,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'timezone' => $appointment->timezone,
                'mode' => $validated['mode'],
                'status' => Appointment::STATUS_PENDING,
                'client_notes' => $validated['client_notes'] ?? $appointment->client_notes,
                'rescheduled_from_appointment_id' => $appointment->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $newAppointment->statusHistories()->create([
                'from_status' => null,
                'to_status' => Appointment::STATUS_PENDING,
                'reason' => 'Rescheduled appointment requested by client.',
                'metadata' => [
                    'source' => 'client_reschedule_new',
                    'rescheduled_from_appointment_id' => $appointment->id,
                    'appointment_date' => $newAppointment->appointment_date?->toDateString(),
                    'start_time' => $newAppointment->start_time?->format('H:i'),
                    'end_time' => $newAppointment->end_time?->format('H:i'),
                    'mode' => $newAppointment->mode,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('client.appointments.index')
            ->with('success', 'Appointment reschedule request submitted successfully.');
    }

    public function cancel(
        CancelAppointmentRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $validated = $request->validated();

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($appointment->client_profile_id === $clientProfile->id, 404);

        if (! $appointment->canBeCancelled()) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment cannot be cancelled.',
            ]);
        }

        DB::transaction(function () use ($appointment, $request, $validated): void {
            $fromStatus = $appointment->status;
            $reason = filled($validated['cancellation_reason'] ?? null)
                ? $validated['cancellation_reason']
                : 'Cancelled by client.';

            $appointment->forceFill([
                'status' => Appointment::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_by' => $request->user()->id,
                'cancelled_at' => now(),
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => Appointment::STATUS_CANCELLED,
                'reason' => 'Appointment cancelled by client.',
                'metadata' => [
                    'source' => 'client_cancellation',
                    'cancellation_reason' => $reason,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('client.appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }

    private function activeCounsellorProfileForAppointment(Appointment $appointment): CounsellorProfile
    {
        return CounsellorProfile::query()
            ->whereKey($appointment->counsellor_profile_id)
            ->where('status', 'active')
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->firstOrFail();
    }

    private function appointmentPayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'uuid' => $appointment->uuid,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'start_time' => $this->formatTime($appointment->start_time),
            'end_time' => $this->formatTime($appointment->end_time),
            'timezone' => $appointment->timezone,
            'mode' => $appointment->mode,
            'status' => $appointment->status,
            'meeting_link' => $appointment->meeting_link,
            'location' => $appointment->location,
            'client_notes' => $appointment->client_notes,
            'counsellor_notes' => $appointment->counsellor_notes,
            'cancellation_reason' => $appointment->cancellation_reason,
            'cancelled_at' => $appointment->cancelled_at?->toDateTimeString(),
            'can_be_cancelled' => $appointment->canBeCancelled(),
            'can_be_rescheduled' => $appointment->canBeRescheduled(),
            'rescheduled_from_appointment_id' => $appointment->rescheduled_from_appointment_id,
            'counsellor' => [
                'id' => $appointment->counsellorProfile?->id,
                'name' => $appointment->counsellorProfile?->user?->name,
                'email' => $appointment->counsellorProfile?->user?->email,
                'phone' => $appointment->counsellorProfile?->user?->phone,
                'professional_title' => $appointment->counsellorProfile?->professional_title,
                'city' => $appointment->counsellorProfile?->city,
            ],
            'service' => $appointment->counsellingService
                ? [
                    'id' => $appointment->counsellingService->id,
                    'name' => $appointment->counsellingService->name,
                    'service_code' => $appointment->counsellingService->service_code,
                    'service_mode' => $appointment->counsellingService->service_mode,
                    'duration_minutes' => $appointment->counsellingService->duration_minutes,
                    'base_fee' => $appointment->counsellingService->base_fee,
                    'status' => $appointment->counsellingService->status,
                ]
                : null,
        ];
    }

    private function formatTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }
}
