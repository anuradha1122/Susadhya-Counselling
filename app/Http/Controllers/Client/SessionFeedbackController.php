<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Support\StoreSessionFeedbackRequest;
use App\Models\Appointment;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionFeedbackController extends Controller
{
    public function create(
        Request $request
    ): Response {
        $clientProfile =
            $request
                ->user()
                ->clientProfile;

        abort_unless(
            $clientProfile !== null,
            404
        );

        $appointments =
            Appointment::query()
                ->where(
                    'client_profile_id',
                    $clientProfile->id
                )
                ->where(
                    'status',
                    Appointment::STATUS_COMPLETED
                )
                ->whereDoesntHave(
                    'sessionFeedback'
                )
                ->with([
                    'counsellorProfile.user:id,name',
                    'counsellingService:id,name',
                ])
                ->orderByDesc(
                    'appointment_date'
                )
                ->orderByDesc(
                    'start_time'
                )
                ->limit(50)
                ->get()
                ->map(
                    fn (
                        Appointment $appointment
                    ): array => [
                        'uuid' => $appointment->uuid,

                        'appointment_date' => $appointment
                            ->appointment_date
                            ?->format('Y-m-d'),

                        'time' => $appointment
                            ->formattedTimeRange(),

                        'counsellor' => $appointment
                            ->counsellorProfile
                            ?->user
                            ?->name
                            ?? 'Counsellor',

                        'service' => $appointment
                            ->counsellingService
                            ?->name
                            ?? 'Counselling session',
                    ]
                )
                ->values();

        return Inertia::render(
            'Client/Support/Feedback',
            [
                'appointments' => $appointments,
            ]
        );
    }

    public function store(
        StoreSessionFeedbackRequest $request,
        Appointment $appointment,
        SupportTicketService $support
    ): RedirectResponse {
        $support->submitFeedback(
            user: $request->user(),
            appointment: $appointment,
            data: $request->validated()
        );

        return to_route(
            'client.support.feedback.create'
        )->with(
            'success',
            'Thank you. Your feedback has been recorded.'
        );
    }
}
