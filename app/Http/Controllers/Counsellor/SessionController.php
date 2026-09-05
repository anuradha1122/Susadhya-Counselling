<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counsellor\CompleteSessionRequest;
use App\Http\Requests\Counsellor\StartSessionRequest;
use App\Http\Requests\Counsellor\StoreSessionNoteRequest;
use App\Models\Appointment;
use App\Models\CounsellingSession;
use App\Models\CounsellorProfile;
use App\Models\SessionNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => [
                'nullable',
                Rule::in(CounsellingSession::statuses()),
            ],
            'risk_level' => [
                'nullable',
                Rule::in(CounsellingSession::riskLevels()),
            ],
        ]);

        $status = $filters['status'] ?? null;
        $riskLevel = $filters['risk_level'] ?? null;

        $counsellorProfile = $this->counsellorProfile($request);

        $readyAppointments = Appointment::query()
            ->with([
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id',
                'counsellingService:id,name,slug,duration_minutes,service_mode,price,currency,status',
            ])
            ->where('counsellor_profile_id', $counsellorProfile->id)
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->whereDoesntHave('counsellingSession')
            ->whereDate('appointment_date', '<=', now()->toDateString())
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->limit(10)
            ->get()
            ->map(fn (Appointment $appointment): array => $this->readyAppointmentPayload($appointment))
            ->values();

        $sessions = CounsellingSession::query()
            ->with([
                'appointment:id,appointment_date,start_time,end_time,timezone,mode,status,meeting_link,location',
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id',
                'notes.author:id,name,email',
            ])
            ->forCounsellorProfile($counsellorProfile)
            ->when($status, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($riskLevel, function (Builder $query, string $riskLevel): void {
                $query->where('clinical_risk_level', $riskLevel);
            })
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (CounsellingSession $session): array => $this->sessionPayload($session));

        return Inertia::render('Counsellor/Sessions/Index', [
            'readyAppointments' => $readyAppointments,
            'sessions' => $sessions,
            'filters' => [
                'status' => $status ?? '',
                'risk_level' => $riskLevel ?? '',
            ],
            'options' => [
                'statuses' => collect(CounsellingSession::statuses())
                    ->map(fn (string $status): array => [
                        'value' => $status,
                        'label' => str($status)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
                'riskLevels' => collect(CounsellingSession::riskLevels())
                    ->map(fn (string $riskLevel): array => [
                        'value' => $riskLevel,
                        'label' => str($riskLevel)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
                'noteTypes' => collect(SessionNote::noteTypes())
                    ->map(fn (string $type): array => [
                        'value' => $type,
                        'label' => str($type)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
                'visibilities' => collect(SessionNote::visibilities())
                    ->map(fn (string $visibility): array => [
                        'value' => $visibility,
                        'label' => str($visibility)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function start(
        StartSessionRequest $request,
        Appointment $appointment
    ): RedirectResponse {
        $counsellorProfile = $this->counsellorProfile($request);

        abort_unless($appointment->counsellor_profile_id === $counsellorProfile->id, 404);

        if ($appointment->status !== Appointment::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'appointment' => 'Only confirmed appointments can be started as sessions.',
            ]);
        }

        $session = CounsellingSession::query()->firstOrCreate(
            [
                'appointment_id' => $appointment->id,
            ],
            [
                'client_profile_id' => $appointment->client_profile_id,
                'counsellor_profile_id' => $appointment->counsellor_profile_id,
                'status' => CounsellingSession::STATUS_IN_PROGRESS,
                'mode' => $appointment->mode,
                'started_at' => now(),
                'clinical_risk_level' => CounsellingSession::RISK_LOW,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]
        );

        if ($session->status === CounsellingSession::STATUS_DRAFT) {
            $session->forceFill([
                'status' => CounsellingSession::STATUS_IN_PROGRESS,
                'started_at' => $session->started_at ?: now(),
                'updated_by' => $request->user()->id,
            ])->save();
        }

        return redirect()
            ->route('counsellor.sessions.index')
            ->with('success', 'Counselling session started successfully.');
    }

    public function storeNote(
        StoreSessionNoteRequest $request,
        CounsellingSession $session
    ): RedirectResponse {
        $counsellorProfile = $this->counsellorProfile($request);

        abort_unless($session->counsellor_profile_id === $counsellorProfile->id, 404);

        if (! $session->canBeEditedByCounsellor()) {
            throw ValidationException::withMessages([
                'session' => 'Completed sessions cannot receive new counsellor notes.',
            ]);
        }

        $validated = $request->validated();

        $session->notes()->create([
            'author_id' => $request->user()->id,
            'note_type' => $validated['note_type'],
            'visibility' => $validated['visibility'],
            'content' => $validated['content'],
        ]);

        $session->forceFill([
            'updated_by' => $request->user()->id,
        ])->save();

        return redirect()
            ->route('counsellor.sessions.index')
            ->with('success', 'Session note added successfully.');
    }

    public function complete(
        CompleteSessionRequest $request,
        CounsellingSession $session
    ): RedirectResponse {
        $counsellorProfile = $this->counsellorProfile($request);

        abort_unless($session->counsellor_profile_id === $counsellorProfile->id, 404);

        if (! $session->canBeCompleted()) {
            throw ValidationException::withMessages([
                'session' => 'This session cannot be completed.',
            ]);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $session, $validated): void {
            $session->forceFill([
                'status' => CounsellingSession::STATUS_COMPLETED,
                'started_at' => $session->started_at ?: now(),
                'ended_at' => now(),
                'completed_at' => now(),
                'presenting_summary' => $validated['presenting_summary'] ?? null,
                'intervention_summary' => $validated['intervention_summary'],
                'outcome_summary' => $validated['outcome_summary'],
                'client_visible_summary' => $validated['client_visible_summary'] ?? null,
                'homework' => $validated['homework'] ?? null,
                'private_notes' => $validated['private_notes'] ?? null,
                'clinical_risk_level' => $validated['clinical_risk_level'],
                'follow_up_recommended' => (bool) ($validated['follow_up_recommended'] ?? false),
                'follow_up_notes' => $validated['follow_up_notes'] ?? null,
                'next_session_recommended_at' => $validated['next_session_recommended_at'] ?? null,
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment = $session->appointment()->firstOrFail();
            $fromStatus = $appointment->status;

            $appointment->forceFill([
                'status' => Appointment::STATUS_COMPLETED,
                'counsellor_notes' => $validated['outcome_summary'],
                'updated_by' => $request->user()->id,
            ])->save();

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => Appointment::STATUS_COMPLETED,
                'reason' => 'Appointment completed through counselling session delivery.',
                'metadata' => [
                    'source' => 'session_delivery',
                    'counselling_session_id' => $session->id,
                    'clinical_risk_level' => $session->clinical_risk_level,
                    'follow_up_recommended' => $session->follow_up_recommended,
                ],
                'changed_by' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('counsellor.sessions.index')
            ->with('success', 'Counselling session completed successfully.');
    }

    private function counsellorProfile(Request $request): CounsellorProfile
    {
        return CounsellorProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function readyAppointmentPayload(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'start_time' => $this->formatTime($appointment->start_time),
            'end_time' => $this->formatTime($appointment->end_time),
            'timezone' => $appointment->timezone,
            'mode' => $appointment->mode,
            'meeting_link' => $appointment->meeting_link,
            'location' => $appointment->location,
            'client' => [
                'id' => $appointment->clientProfile?->id,
                'name' => $appointment->clientProfile?->user?->name,
                'email' => $appointment->clientProfile?->user?->email,
                'phone' => $appointment->clientProfile?->user?->phone,
            ],
            'service' => $appointment->counsellingService
                ? [
                    'id' => $appointment->counsellingService->id,
                    'name' => $appointment->counsellingService->name,
                    'duration_minutes' => $appointment->counsellingService->duration_minutes,
                ]
                : null,
        ];
    }

    private function sessionPayload(CounsellingSession $session): array
    {
        return [
            'id' => $session->id,
            'status' => $session->status,
            'mode' => $session->mode,
            'started_at' => $session->started_at?->toDateTimeString(),
            'ended_at' => $session->ended_at?->toDateTimeString(),
            'completed_at' => $session->completed_at?->toDateTimeString(),
            'presenting_summary' => $session->presenting_summary,
            'intervention_summary' => $session->intervention_summary,
            'outcome_summary' => $session->outcome_summary,
            'client_visible_summary' => $session->client_visible_summary,
            'homework' => $session->homework,
            'private_notes' => $session->private_notes,
            'clinical_risk_level' => $session->clinical_risk_level,
            'follow_up_recommended' => $session->follow_up_recommended,
            'follow_up_notes' => $session->follow_up_notes,
            'next_session_recommended_at' => $session->next_session_recommended_at?->toDateString(),
            'can_be_completed' => $session->canBeCompleted(),
            'can_be_edited' => $session->canBeEditedByCounsellor(),
            'appointment' => [
                'id' => $session->appointment?->id,
                'appointment_date' => $session->appointment?->appointment_date?->toDateString(),
                'start_time' => $this->formatTime($session->appointment?->start_time),
                'end_time' => $this->formatTime($session->appointment?->end_time),
                'timezone' => $session->appointment?->timezone,
                'mode' => $session->appointment?->mode,
                'status' => $session->appointment?->status,
                'meeting_link' => $session->appointment?->meeting_link,
                'location' => $session->appointment?->location,
            ],
            'client' => [
                'id' => $session->clientProfile?->id,
                'name' => $session->clientProfile?->user?->name,
                'email' => $session->clientProfile?->user?->email,
                'phone' => $session->clientProfile?->user?->phone,
            ],
            'notes' => $session->notes
                ->map(fn (SessionNote $note): array => [
                    'id' => $note->id,
                    'note_type' => $note->note_type,
                    'visibility' => $note->visibility,
                    'content' => $note->content,
                    'created_at' => $note->created_at?->toDateTimeString(),
                    'author' => [
                        'id' => $note->author?->id,
                        'name' => $note->author?->name,
                    ],
                ])
                ->values(),
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
