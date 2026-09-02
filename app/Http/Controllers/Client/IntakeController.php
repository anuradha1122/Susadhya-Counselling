<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitIntakeRequest;
use App\Http\Requests\Client\UpdateIntakeRequest;
use App\Models\ClientIntake;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IntakeController extends Controller
{
    public function edit(Request $request): Response
    {
        $clientProfile = $this->clientProfile($request);

        $intake = ClientIntake::query()
            ->firstOrCreate(
                ['client_profile_id' => $clientProfile->id],
                [
                    'status' => ClientIntake::STATUS_DRAFT,
                    'risk_level' => ClientIntake::RISK_LOW,
                ]
            );

        $intake->load(['screeningAnswers']);

        return Inertia::render('Client/Intake/Edit', [
            'intake' => $this->intakePayload($intake),
            'screeningQuestions' => ClientIntake::screeningQuestions(),
            'screeningScoreOptions' => ClientIntake::screeningScoreOptions(),
            'preferredSessionModes' => collect(ClientIntake::preferredSessionModes())
                ->map(fn (string $mode): array => [
                    'value' => $mode,
                    'label' => str($mode)->replace('_', ' ')->title()->toString(),
                ])
                ->values(),
        ]);
    }

    public function update(UpdateIntakeRequest $request): RedirectResponse
    {
        $clientProfile = $this->clientProfile($request);

        $intake = ClientIntake::query()
            ->firstOrCreate(
                ['client_profile_id' => $clientProfile->id],
                [
                    'status' => ClientIntake::STATUS_DRAFT,
                    'risk_level' => ClientIntake::RISK_LOW,
                ]
            );

        if (! $intake->canBeEditedByClient()) {
            throw ValidationException::withMessages([
                'intake' => 'Submitted intake cannot be edited unless follow-up is requested.',
            ]);
        }

        DB::transaction(function () use ($request, $intake): void {
            $this->saveIntakeDraft($intake, $request->validated());
        });

        return redirect()
            ->route('client.intake.edit')
            ->with('success', 'Intake saved successfully.');
    }

    public function submit(SubmitIntakeRequest $request): RedirectResponse
    {
        $clientProfile = $this->clientProfile($request);

        $intake = ClientIntake::query()
            ->firstOrCreate(
                ['client_profile_id' => $clientProfile->id],
                [
                    'status' => ClientIntake::STATUS_DRAFT,
                    'risk_level' => ClientIntake::RISK_LOW,
                ]
            );

        if (! $intake->canBeEditedByClient()) {
            throw ValidationException::withMessages([
                'intake' => 'This intake has already been submitted.',
            ]);
        }

        DB::transaction(function () use ($request, $intake): void {
            $validated = $request->validated();

            $this->saveIntakeDraft($intake, $validated);

            $risk = $this->calculateRiskLevel($validated['screening_answers'] ?? []);

            $intake->forceFill([
                'status' => ClientIntake::STATUS_SUBMITTED,
                'risk_level' => $risk['level'],
                'risk_notes' => $risk['notes'],
                'consent_given_at' => now(),
                'submitted_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'reviewer_notes' => null,
            ])->save();
        });

        return redirect()
            ->route('client.intake.edit')
            ->with('success', 'Intake submitted successfully.');
    }

    private function saveIntakeDraft(ClientIntake $intake, array $validated): void
    {
        $intake->forceFill([
            'presenting_concerns' => $validated['presenting_concerns'] ?? null,
            'current_symptoms' => $validated['current_symptoms'] ?? null,
            'counselling_goals' => $validated['counselling_goals'] ?? null,
            'preferred_session_mode' => $validated['preferred_session_mode'] ?? null,
            'previous_counselling' => $validated['previous_counselling'] ?? false,
            'previous_counselling_notes' => $validated['previous_counselling_notes'] ?? null,
            'medication_notes' => $validated['medication_notes'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'consent_terms_accepted' => (bool) ($validated['consent_terms_accepted'] ?? false),
            'consent_privacy_accepted' => (bool) ($validated['consent_privacy_accepted'] ?? false),
            'consent_telehealth_accepted' => (bool) ($validated['consent_telehealth_accepted'] ?? false),
            'consent_data_processing_accepted' => (bool) ($validated['consent_data_processing_accepted'] ?? false),
        ])->save();

        $this->syncScreeningAnswers(
            intake: $intake,
            answers: $validated['screening_answers'] ?? []
        );
    }

    private function syncScreeningAnswers(ClientIntake $intake, array $answers): void
    {
        $questions = collect(ClientIntake::screeningQuestions())
            ->keyBy('key');

        foreach ($answers as $key => $answer) {
            if (! $questions->has($key)) {
                continue;
            }

            $question = $questions->get($key);
            $score = $answer['answer_score'] ?? null;

            $intake->screeningAnswers()->updateOrCreate(
                [
                    'question_key' => $key,
                ],
                [
                    'question_text' => $question['label'],
                    'answer_score' => $score === '' ? null : $score,
                    'answer_value' => $this->screeningScoreLabel($score),
                    'answer_notes' => $answer['answer_notes'] ?? null,
                ]
            );
        }
    }

    private function calculateRiskLevel(array $answers): array
    {
        $scores = collect($answers)
            ->map(fn (array $answer): ?int => isset($answer['answer_score'])
                && $answer['answer_score'] !== ''
                ? (int) $answer['answer_score']
                : null)
            ->filter(fn (?int $score): bool => $score !== null)
            ->values();

        $selfHarmScore = isset($answers['self_harm_thoughts']['answer_score'])
            && $answers['self_harm_thoughts']['answer_score'] !== ''
            ? (int) $answers['self_harm_thoughts']['answer_score']
            : 0;

        $highestScore = $scores->max() ?? 0;
        $averageScore = $scores->isNotEmpty() ? $scores->avg() : 0;

        if ($selfHarmScore >= 2) {
            return [
                'level' => ClientIntake::RISK_URGENT,
                'notes' => 'Urgent risk flag triggered by self-harm screening response.',
            ];
        }

        if ($selfHarmScore === 1) {
            return [
                'level' => ClientIntake::RISK_HIGH,
                'notes' => 'High risk flag triggered by self-harm screening response.',
            ];
        }

        if ($highestScore >= 3 || $averageScore >= 2.25) {
            return [
                'level' => ClientIntake::RISK_HIGH,
                'notes' => 'High risk level calculated from screening score pattern.',
            ];
        }

        if ($highestScore >= 2 || $averageScore >= 1.25) {
            return [
                'level' => ClientIntake::RISK_MODERATE,
                'notes' => 'Moderate risk level calculated from screening score pattern.',
            ];
        }

        return [
            'level' => ClientIntake::RISK_LOW,
            'notes' => 'Low risk level calculated from screening score pattern.',
        ];
    }

    private function screeningScoreLabel(mixed $score): ?string
    {
        if ($score === null || $score === '') {
            return null;
        }

        return collect(ClientIntake::screeningScoreOptions())
            ->firstWhere('value', (int) $score)['label'] ?? null;
    }

    private function clientProfile(Request $request): ClientProfile
    {
        return ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function intakePayload(ClientIntake $intake): array
    {
        return [
            'id' => $intake->id,
            'status' => $intake->status,
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
            'consent_version' => $intake->consent_version,
            'risk_level' => $intake->risk_level,
            'risk_notes' => $intake->risk_notes,
            'submitted_at' => $intake->submitted_at?->toDateTimeString(),
            'reviewed_at' => $intake->reviewed_at?->toDateTimeString(),
            'reviewer_notes' => $intake->reviewer_notes,
            'can_edit' => $intake->canBeEditedByClient(),
            'screening_answers' => $intake->screeningAnswers
                ->mapWithKeys(fn ($answer): array => [
                    $answer->question_key => [
                        'answer_score' => $answer->answer_score,
                        'answer_notes' => $answer->answer_notes,
                    ],
                ])
                ->all(),
        ];
    }
}
