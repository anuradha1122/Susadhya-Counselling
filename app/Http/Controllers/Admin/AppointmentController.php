<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
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
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                Rule::in(UpdateAppointmentStatusRequest::managedStatuses()),
            ],
            'period' => [
                'nullable',
                Rule::in([
                    'upcoming',
                    'past',
                    'all',
                ]),
            ],
            'mode' => [
                'nullable',
                Rule::in(Appointment::modes()),
            ],
        ]);

        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $period = $filters['period'] ?? 'upcoming';
        $mode = $filters['mode'] ?? null;

        $appointments = Appointment::query()
            ->with([
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id,city,status',
                'counsellorProfile.user:id,name,email,phone,is_active',
                'counsellorProfile:id,user_id,professional_title,city,status',
                'counsellingService:id,name,service_code,service_mode,duration_minutes,base_fee,status',
                'cancelledBy:id,name,email',
                'createdBy:id,name,email',
                'updatedBy:id,name,email',
            ])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('uuid', 'like', "%{$search}%")
                        ->orWhereHas('clientProfile.user', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('counsellorProfile.user', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($mode, function (Builder $query, string $mode): void {
                $query->where('mode', $mode);
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
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Appointment $appointment): array => $this->appointmentPayload($appointment));

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'period' => $period,
                'mode' => $mode ?? '',
            ],
            'options' => [
                'statuses' => collect(UpdateAppointmentStatusRequest::managedStatuses())
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
                'modes' => collect(Appointment::modes())
                    ->map(fn (string $mode): array => [
                        'value' => $mode,
                        'label' => str($mode)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function updateStatus(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $validated = $request->validated();
        $toStatus = $validated['status'];

        $meetingLink = $validated['meeting_link'] ?? $appointment->meeting_link;
        $location = $validated['location'] ?? $appointment->location;

        if ($toStatus === Appointment::STATUS_CONFIRMED && $appointment->isOnline() && blank($meetingLink)) {
            throw ValidationException::withMessages([
                'meeting_link' => 'Please provide a meeting link before confirming this online appointment.',
            ]);
        }

        if ($toStatus === Appointment::STATUS_CONFIRMED && $appointment->isInPerson() && blank($location)) {
            throw ValidationException::withMessages([
                'location' => 'Please provide a location before confirming this in-person appointment.',
            ]);
        }

        DB::transaction(function () use ($appointment, $request, $validated, $toStatus, $meetingLink, $location): void {
            $fromStatus = $appointment->status;

            $updateData = [
                'status' => $toStatus,
                'meeting_link' => $meetingLink,
                'location' => $location,
                'admin_notes' => $validated['admin_notes'] ?? $appointment->admin_notes,
                'updated_by' => $request->user()->id,
            ];

            if ($toStatus === Appointment::STATUS_CONFIRMED) {
                $updateData['reminder_scheduled_at'] = $this->calculateReminderTime($appointment);
            }

            if ($toStatus === Appointment::STATUS_CANCELLED) {
                $updateData['cancellation_reason'] = filled($validated['cancellation_reason'] ?? null)
                    ? $validated['cancellation_reason']
                    : 'Cancelled by admin.';
                $updateData['cancelled_by'] = $request->user()->id;
                $updateData['cancelled_at'] = now();
            }

            if ($fromStatus === Appointment::STATUS_CANCELLED && $toStatus !== Appointment::STATUS_CANCELLED) {
                $updateData['cancellation_reason'] = null;
                $updateData['cancelled_by'] = null;
                $updateData['cancelled_at'] = null;
            }

            $appointment->forceFill($updateData)->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => 'Appointment status updated by admin.',
                'metadata' => [
                    'source' => 'admin_status_management',
                    'admin_notes' => $validated['admin_notes'] ?? null,
                    'cancellation_reason' => $updateData['cancellation_reason'] ?? null,
                    'meeting_link_added' => filled($meetingLink),
                    'location_added' => filled($location),
                    'appointment_date' => $appointment->appointment_date?->toDateString(),
                    'start_time' => $appointment->start_time?->format('H:i'),
                    'end_time' => $appointment->end_time?->format('H:i'),
                    'mode' => $appointment->mode,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', 'Appointment status updated successfully.');
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
            'client' => [
                'id' => $appointment->clientProfile?->id,
                'name' => $appointment->clientProfile?->user?->name,
                'email' => $appointment->clientProfile?->user?->email,
                'phone' => $appointment->clientProfile?->user?->phone,
                'city' => $appointment->clientProfile?->city,
                'status' => $appointment->clientProfile?->status,
            ],
            'counsellor' => [
                'id' => $appointment->counsellorProfile?->id,
                'name' => $appointment->counsellorProfile?->user?->name,
                'email' => $appointment->counsellorProfile?->user?->email,
                'phone' => $appointment->counsellorProfile?->user?->phone,
                'professional_title' => $appointment->counsellorProfile?->professional_title,
                'city' => $appointment->counsellorProfile?->city,
                'status' => $appointment->counsellorProfile?->status,
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
            'audit' => [
                'created_by' => $appointment->createdBy?->name,
                'updated_by' => $appointment->updatedBy?->name,
                'cancelled_by' => $appointment->cancelledBy?->name,
            ],
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
