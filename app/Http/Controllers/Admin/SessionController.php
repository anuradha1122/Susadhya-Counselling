<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSessionReviewRequest;
use App\Models\CounsellingSession;
use App\Models\SessionNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(CounsellingSession::statuses()),
            ],
            'risk_level' => [
                'nullable',
                Rule::in(CounsellingSession::riskLevels()),
            ],
            'follow_up' => [
                'nullable',
                Rule::in(['yes', 'no']),
            ],
        ]);

        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $riskLevel = $filters['risk_level'] ?? null;
        $followUp = $filters['follow_up'] ?? null;

        $sessions = CounsellingSession::query()
            ->with([
                'appointment:id,appointment_date,start_time,end_time,timezone,mode,status',
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id',
                'counsellorProfile.user:id,name,email,phone,is_active',
                'counsellorProfile:id,user_id,professional_title,city,status',
                'notes.author:id,name,email',
            ])
            ->when($search, function (Builder $query, string $search): void {
                $query
                    ->whereHas('clientProfile.user', function (Builder $query) use ($search): void {
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
            })
            ->when($status, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($riskLevel, function (Builder $query, string $riskLevel): void {
                $query->where('clinical_risk_level', $riskLevel);
            })
            ->when($followUp === 'yes', function (Builder $query): void {
                $query->where('follow_up_recommended', true);
            })
            ->when($followUp === 'no', function (Builder $query): void {
                $query->where('follow_up_recommended', false);
            })
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (CounsellingSession $session): array => $this->sessionPayload($session));

        return Inertia::render('Admin/Sessions/Index', [
            'sessions' => $sessions,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'risk_level' => $riskLevel ?? '',
                'follow_up' => $followUp ?? '',
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
                'followUpOptions' => [
                    [
                        'value' => 'yes',
                        'label' => 'Follow-up recommended',
                    ],
                    [
                        'value' => 'no',
                        'label' => 'No follow-up',
                    ],
                ],
            ],
        ]);
    }

    public function updateReview(
        UpdateSessionReviewRequest $request,
        CounsellingSession $session
    ): RedirectResponse {
        $validated = $request->validated();

        $session->forceFill([
            'clinical_risk_level' => $validated['clinical_risk_level'],
            'follow_up_recommended' => (bool) ($validated['follow_up_recommended'] ?? false),
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'updated_by' => $request->user()->id,
        ])->save();

        return redirect()
            ->route('admin.sessions.index')
            ->with('success', 'Session review updated successfully.');
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
            'admin_notes' => $session->admin_notes,
            'clinical_risk_level' => $session->clinical_risk_level,
            'follow_up_recommended' => $session->follow_up_recommended,
            'follow_up_notes' => $session->follow_up_notes,
            'next_session_recommended_at' => $session->next_session_recommended_at?->toDateString(),
            'appointment' => [
                'id' => $session->appointment?->id,
                'appointment_date' => $session->appointment?->appointment_date?->toDateString(),
                'start_time' => $this->formatTime($session->appointment?->start_time),
                'end_time' => $this->formatTime($session->appointment?->end_time),
                'timezone' => $session->appointment?->timezone,
                'mode' => $session->appointment?->mode,
                'status' => $session->appointment?->status,
            ],
            'client' => [
                'id' => $session->clientProfile?->id,
                'name' => $session->clientProfile?->user?->name,
                'email' => $session->clientProfile?->user?->email,
                'phone' => $session->clientProfile?->user?->phone,
            ],
            'counsellor' => [
                'id' => $session->counsellorProfile?->id,
                'name' => $session->counsellorProfile?->user?->name,
                'email' => $session->counsellorProfile?->user?->email,
                'phone' => $session->counsellorProfile?->user?->phone,
                'professional_title' => $session->counsellorProfile?->professional_title,
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
