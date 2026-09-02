<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewIntakeRequest;
use App\Models\ClientIntake;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IntakeReviewController extends Controller
{
    public function adminIndex(Request $request): Response
    {
        return $this->index(
            request: $request,
            routePrefix: 'admin',
            pageTitle: 'Client Intake Reviews',
            description: 'Review submitted client intakes, screening flags, consent records, and follow-up requirements.'
        );
    }

    public function counsellorIndex(Request $request): Response
    {
        return $this->index(
            request: $request,
            routePrefix: 'counsellor',
            pageTitle: 'Client Intake Reviews',
            description: 'Review client intake and screening summaries before counselling sessions.'
        );
    }

    public function adminReview(ReviewIntakeRequest $request, ClientIntake $intake): RedirectResponse
    {
        return $this->review($request, $intake, 'admin.intakes.index');
    }

    public function counsellorReview(ReviewIntakeRequest $request, ClientIntake $intake): RedirectResponse
    {
        return $this->review($request, $intake, 'counsellor.intakes.index');
    }

    private function index(
        Request $request,
        string $routePrefix,
        string $pageTitle,
        string $description
    ): Response {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(ClientIntake::reviewStatuses()),
            ],
            'risk_level' => [
                'nullable',
                Rule::in(ClientIntake::riskLevels()),
            ],
        ]);

        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $riskLevel = $filters['risk_level'] ?? null;

        $intakes = ClientIntake::query()
            ->with([
                'clientProfile.user:id,name,email,phone,is_active',
                'clientProfile:id,user_id,city,status',
                'screeningAnswers',
                'reviewer:id,name,email',
            ])
            ->submittedForReview()
            ->when($search, function (Builder $query, string $search): void {
                $query->whereHas('clientProfile.user', function (Builder $query) use ($search): void {
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
                $query->where('risk_level', $riskLevel);
            })
            ->orderByRaw("
                CASE risk_level
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'moderate' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (ClientIntake $intake): array => $this->intakePayload($intake));

        return Inertia::render('Intakes/ReviewIndex', [
            'intakes' => $intakes,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'risk_level' => $riskLevel ?? '',
            ],
            'options' => [
                'statuses' => collect(ClientIntake::reviewStatuses())
                    ->map(fn (string $status): array => [
                        'value' => $status,
                        'label' => str($status)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
                'riskLevels' => collect(ClientIntake::riskLevels())
                    ->map(fn (string $riskLevel): array => [
                        'value' => $riskLevel,
                        'label' => str($riskLevel)->replace('_', ' ')->title()->toString(),
                    ])
                    ->values(),
            ],
            'routePrefix' => $routePrefix,
            'pageTitle' => $pageTitle,
            'description' => $description,
        ]);
    }

    private function review(
        ReviewIntakeRequest $request,
        ClientIntake $intake,
        string $redirectRoute
    ): RedirectResponse {
        abort_unless($intake->canBeReviewed(), 404);

        $validated = $request->validated();

        $intake->forceFill([
            'status' => $validated['status'],
            'risk_level' => $validated['risk_level'],
            'reviewer_notes' => $validated['reviewer_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Client intake review updated successfully.');
    }

    private function intakePayload(ClientIntake $intake): array
    {
        return [
            'id' => $intake->id,
            'status' => $intake->status,
            'risk_level' => $intake->risk_level,
            'risk_notes' => $intake->risk_notes,
            'presenting_concerns' => $intake->presenting_concerns,
            'current_symptoms' => $intake->current_symptoms,
            'counselling_goals' => $intake->counselling_goals,
            'preferred_session_mode' => $intake->preferred_session_mode,
            'previous_counselling' => $intake->previous_counselling,
            'previous_counselling_notes' => $intake->previous_counselling_notes,
            'medication_notes' => $intake->medication_notes,
            'emergency_contact_name' => $intake->emergency_contact_name,
            'emergency_contact_phone' => $intake->emergency_contact_phone,
            'emergency_contact_relationship' => $intake->emergency_contact_relationship,
            'consent_terms_accepted' => $intake->consent_terms_accepted,
            'consent_privacy_accepted' => $intake->consent_privacy_accepted,
            'consent_telehealth_accepted' => $intake->consent_telehealth_accepted,
            'consent_data_processing_accepted' => $intake->consent_data_processing_accepted,
            'consent_given_at' => $intake->consent_given_at?->toDateTimeString(),
            'submitted_at' => $intake->submitted_at?->toDateTimeString(),
            'reviewed_at' => $intake->reviewed_at?->toDateTimeString(),
            'reviewer_notes' => $intake->reviewer_notes,
            'client' => [
                'id' => $intake->clientProfile?->id,
                'name' => $intake->clientProfile?->user?->name,
                'email' => $intake->clientProfile?->user?->email,
                'phone' => $intake->clientProfile?->user?->phone,
                'city' => $intake->clientProfile?->city,
                'status' => $intake->clientProfile?->status,
            ],
            'reviewer' => [
                'id' => $intake->reviewer?->id,
                'name' => $intake->reviewer?->name,
                'email' => $intake->reviewer?->email,
            ],
            'screening_answers' => $intake->screeningAnswers
                ->map(fn ($answer): array => [
                    'id' => $answer->id,
                    'question_key' => $answer->question_key,
                    'question_text' => $answer->question_text,
                    'answer_score' => $answer->answer_score,
                    'answer_value' => $answer->answer_value,
                    'answer_notes' => $answer->answer_notes,
                ])
                ->values(),
        ];
    }
}
