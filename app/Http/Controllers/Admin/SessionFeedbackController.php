<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionFeedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionFeedbackController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'rating' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'follow_up' => [
                'nullable',
                'in:yes,no',
            ],
        ]);

        $query =
            SessionFeedback::query()
                ->with([
                    'clientProfile.user:id,name,email',
                    'appointment.counsellorProfile.user:id,name',
                    'appointment.counsellingService:id,name',
                ]);

        if (
            filled(
                $filters['rating']
                ?? null
            )
        ) {
            $query->where(
                'overall_rating',
                $filters['rating']
            );
        }

        if (
            ($filters['follow_up']
                ?? '')
            === 'yes'
        ) {
            $query->where(
                'consent_to_follow_up',
                true
            );
        }

        if (
            ($filters['follow_up']
                ?? '')
            === 'no'
        ) {
            $query->where(
                'consent_to_follow_up',
                false
            );
        }

        $feedback =
            $query
                ->orderByDesc(
                    'submitted_at'
                )
                ->paginate(20)
                ->withQueryString()
                ->through(
                    fn (
                        SessionFeedback $item
                    ): array => [
                        'uuid' => $item->uuid,

                        'overall_rating' => $item->overall_rating,

                        'technical_rating' => $item->technical_rating,

                        'comment' => $item->comment,

                        'would_recommend' => $item->would_recommend,

                        'consent_to_follow_up' => $item->consent_to_follow_up,

                        'submitted_at' => $item
                            ->submitted_at
                            ?->toISOString(),

                        'client' => [
                            'name' => $item
                                ->clientProfile
                                ?->user
                                ?->name
                                ?? 'Client',

                            'email' => $item
                                ->clientProfile
                                ?->user
                                ?->email,
                        ],

                        'appointment' => [
                            'uuid' => $item
                                ->appointment
                                ?->uuid,

                            'date' => $item
                                ->appointment
                                ?->appointment_date
                                ?->format(
                                    'Y-m-d'
                                ),

                            'counsellor' => $item
                                ->appointment
                                ?->counsellorProfile
                                ?->user
                                ?->name
                                ?? 'Counsellor',

                            'service' => $item
                                ->appointment
                                ?->counsellingService
                                ?->name
                                ?? 'Counselling session',
                        ],
                    ]
                );

        return Inertia::render(
            'Admin/Support/Feedback',
            [
                'feedback' => $feedback,

                'filters' => [
                    'rating' => $filters['rating']
                        ?? '',

                    'follow_up' => $filters['follow_up']
                        ?? '',
                ],
            ]
        );
    }
}
