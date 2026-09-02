<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
use App\Models\SessionNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $sessions = CounsellingSession::query()
            ->with([
                'appointment:id,appointment_date,start_time,end_time,timezone,mode,status',
                'counsellorProfile.user:id,name,email,phone,is_active',
                'counsellorProfile:id,user_id,professional_title,city,status',
                'clientVisibleNotes.author:id,name,email',
            ])
            ->forClientProfile($clientProfile)
            ->completed()
            ->latest('completed_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (CounsellingSession $session): array => $this->sessionPayload($session));

        return Inertia::render('Client/Sessions/Index', [
            'sessions' => $sessions,
        ]);
    }

    private function sessionPayload(CounsellingSession $session): array
    {
        return [
            'id' => $session->id,
            'status' => $session->status,
            'mode' => $session->mode,
            'completed_at' => $session->completed_at?->toDateTimeString(),
            'client_visible_summary' => $session->client_visible_summary,
            'homework' => $session->homework,
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
            ],
            'counsellor' => [
                'id' => $session->counsellorProfile?->id,
                'name' => $session->counsellorProfile?->user?->name,
                'email' => $session->counsellorProfile?->user?->email,
                'phone' => $session->counsellorProfile?->user?->phone,
                'professional_title' => $session->counsellorProfile?->professional_title,
                'city' => $session->counsellorProfile?->city,
            ],
            'notes' => $session->clientVisibleNotes
                ->map(fn (SessionNote $note): array => [
                    'id' => $note->id,
                    'note_type' => $note->note_type,
                    'content' => $note->content,
                    'created_at' => $note->created_at?->toDateTimeString(),
                    'author' => [
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
