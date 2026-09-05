<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counsellor\ConfirmAppointmentRequest;
use App\Http\Requests\Counsellor\MarkAppointmentOutcomeRequest;
use App\Models\Appointment;
use App\Models\CounsellorProfile;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
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

        $counsellorProfile = CounsellorProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $appointments = Appointment::query()
            ->with([
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id,city,status',
                'counsellingService:id,name,slug,service_mode,duration_minutes,price,currency,status',
            ])
            ->where('counsellor_profile_id', $counsellorProfile->id)
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

        return Inertia::render('Counsellor/Appointments/Index', [
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

    public function confirm(
        ConfirmAppointmentRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $validated = $request->validated();

        $counsellorProfile = CounsellorProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($appointment->counsellor_profile_id === $counsellorProfile->id, 404);

        if ($appointment->status !== Appointment::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'appointment' => 'Only pending appointments can be confirmed.',
            ]);
        }

        if ($appointment->isOnline() && blank($validated['meeting_link'] ?? null)) {
            throw ValidationException::withMessages([
                'meeting_link' => 'Please provide a meeting link for online appointments.',
            ]);
        }

        if ($appointment->isInPerson() && blank($validated['location'] ?? null)) {
            throw ValidationException::withMessages([
                'location' => 'Please provide a location for in-person appointments.',
            ]);
        }

        DB::transaction(function () use ($appointment, $request, $validated): void {
            $fromStatus = $appointment->status;

            $appointment->forceFill([
                'status' => Appointment::STATUS_CONFIRMED,
                'meeting_link' => $validated['meeting_link'] ?? $appointment->meeting_link,
                'location' => $validated['location'] ?? $appointment->location,
                'counsellor_notes' => $validated['counsellor_notes'] ?? $appointment->counsellor_notes,
                'reminder_scheduled_at' => $this->calculateReminderTime($appointment),
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => Appointment::STATUS_CONFIRMED,
                'reason' => 'Appointment confirmed by counsellor.',
                'metadata' => [
                    'source' => 'counsellor_confirmation',
                    'meeting_link_added' => filled($validated['meeting_link'] ?? null),
                    'location_added' => filled($validated['location'] ?? null),
                    'appointment_date' => $appointment->appointment_date?->toDateString(),
                    'start_time' => $appointment->start_time?->format('H:i'),
                    'end_time' => $appointment->end_time?->format('H:i'),
                    'mode' => $appointment->mode,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('counsellor.appointments.index')
            ->with('success', 'Appointment confirmed successfully.');
    }

    public function complete(
        MarkAppointmentOutcomeRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $this->markOutcome(
            request: $request,
            appointment: $appointment,
            toStatus: Appointment::STATUS_COMPLETED,
            historyReason: 'Appointment marked as completed by counsellor.',
            successMessage: 'Appointment marked as completed successfully.',
            source: 'counsellor_completion'
        );

        return redirect()
            ->route('counsellor.appointments.index')
            ->with('success', 'Appointment marked as completed successfully.');
    }

    public function noShow(
        MarkAppointmentOutcomeRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $this->markOutcome(
            request: $request,
            appointment: $appointment,
            toStatus: Appointment::STATUS_NO_SHOW,
            historyReason: 'Appointment marked as no-show by counsellor.',
            successMessage: 'Appointment marked as no-show successfully.',
            source: 'counsellor_no_show'
        );

        return redirect()
            ->route('counsellor.appointments.index')
            ->with('success', 'Appointment marked as no-show successfully.');
    }

    private function markOutcome(
        MarkAppointmentOutcomeRequest $request,
        Appointment $appointment,
        string $toStatus,
        string $historyReason,
        string $successMessage,
        string $source
    ): void {
        $validated = $request->validated();

        $counsellorProfile = CounsellorProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($appointment->counsellor_profile_id === $counsellorProfile->id, 404);

        if ($appointment->status !== Appointment::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'appointment' => 'Only confirmed appointments can be closed.',
            ]);
        }

        DB::transaction(function () use (
            $appointment,
            $request,
            $validated,
            $toStatus,
            $historyReason,
            $source
        ): void {
            $fromStatus = $appointment->status;

            $appointment->forceFill([
                'status' => $toStatus,
                'counsellor_notes' => $validated['counsellor_notes'] ?? $appointment->counsellor_notes,
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $historyReason,
                'metadata' => [
                    'source' => $source,
                    'appointment_date' => $appointment->appointment_date?->toDateString(),
                    'start_time' => $appointment->start_time?->format('H:i'),
                    'end_time' => $appointment->end_time?->format('H:i'),
                    'mode' => $appointment->mode,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });
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
            'admin_notes' => $appointment->admin_notes,
            'cancellation_reason' => $appointment->cancellation_reason,
            'cancelled_at' => $appointment->cancelled_at?->toDateTimeString(),
            'reminder_scheduled_at' => $appointment->reminder_scheduled_at?->toDateTimeString(),
            'can_be_confirmed' => $appointment->status === Appointment::STATUS_PENDING,
            'can_be_completed' => $appointment->status === Appointment::STATUS_CONFIRMED,
            'can_be_marked_no_show' => $appointment->status === Appointment::STATUS_CONFIRMED,
            'client' => [
                'id' => $appointment->clientProfile?->id,
                'name' => $appointment->clientProfile?->user?->name,
                'email' => $appointment->clientProfile?->user?->email,
                'phone' => $appointment->clientProfile?->user?->phone,
                'city' => $appointment->clientProfile?->city,
            ],
            'service' => $appointment->counsellingService
                ? [
                    'id' => $appointment->counsellingService->id,
                    'name' => $appointment->counsellingService->name,
                    'slug' => $appointment->counsellingService->slug,
                    'service_mode' => $appointment->counsellingService->service_mode,
                    'duration_minutes' => $appointment->counsellingService->duration_minutes,
                    'price' => $appointment->counsellingService->price,
                    'currency' => $appointment->counsellingService->currency,
                    'status' => $appointment->counsellingService->status,
                ]
                : null,
        ];
    }

    private function calculateReminderTime(Appointment $appointment): CarbonImmutable
    {
        $appointmentDate = $appointment->appointment_date?->toDateString();
        $startTime = $this->formatTime($appointment->start_time);

        return CarbonImmutable::parse($appointmentDate.' '.$startTime)
            ->subDay();
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
