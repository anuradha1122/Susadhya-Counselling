<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counsellor\StoreCaseFollowUpRequest;
use App\Http\Requests\Counsellor\StoreCaseGoalRequest;
use App\Http\Requests\Counsellor\StoreCaseRequest;
use App\Http\Requests\Counsellor\StoreClinicalNoteRequest;
use App\Http\Requests\Counsellor\UpdateCaseFollowUpRequest;
use App\Http\Requests\Counsellor\UpdateCaseGoalRequest;
use App\Http\Requests\Counsellor\UpdateCaseRequest;
use App\Http\Requests\Counsellor\UpdateClinicalNoteRequest;
use App\Models\CaseFollowUp;
use App\Models\CaseGoal;
use App\Models\ClientCase;
use App\Models\ClinicalNote;
use App\Models\CounsellingSession;
use App\Services\Clinical\ClinicalNoteVersionService;
use App\Services\Clinical\ClinicalRecordAccessLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    public function __construct(
        private readonly ClinicalNoteVersionService $versions,
        private readonly ClinicalRecordAccessLogger $accessLogger,
    ) {}

    public function index(Request $request): Response
    {
        $profile = $this->counsellorProfile($request);

        $search = trim(
            $request->string('search')->toString()
        );

        $status = $request
            ->string('status')
            ->toString();

        $risk = $request
            ->string('risk')
            ->toString();

        $cases = ClientCase::query()
            ->where(
                'counsellor_profile_id',
                $profile->id,
            )
            ->with([
                'clientProfile.user:id,name,email,phone',
            ])
            ->withCount([
                'goals',
                'followUps',
                'clinicalNotes',
            ])
            ->when(
                $search,
                function (
                    Builder $query,
                    string $search
                ): void {
                    $query->where(
                        function (
                            Builder $query
                        ) use ($search): void {
                            $query
                                ->where(
                                    'summary',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'clientProfile.user',
                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query
                                            ->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'email',
                                                'like',
                                                "%{$search}%"
                                            )
                                            ->orWhere(
                                                'phone',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $status,
                    ClientCase::statuses(),
                    true,
                ),
                fn (Builder $query) => $query->where(
                    'status',
                    $status,
                ),
            )
            ->when(
                in_array(
                    $risk,
                    ClientCase::riskLevels(),
                    true,
                ),
                fn (Builder $query) => $query->where(
                    'risk_level',
                    $risk,
                ),
            )
            ->latest('opened_at')
            ->paginate(12)
            ->withQueryString();

        $eligibleClients = CounsellingSession::query()
            ->where(
                'counsellor_profile_id',
                $profile->id,
            )
            ->whereIn(
                'status',
                [
                    'in_progress',
                    'completed',
                ],
            )
            ->with(
                'clientProfile.user:id,name,email'
            )
            ->get()
            ->pluck('clientProfile')
            ->filter()
            ->unique('id')
            ->values()
            ->map(
                fn ($client) => [
                    'id' => $client->id,
                    'name' => $client->user?->name
                        ?? 'Client',
                    'email' => $client->user?->email,
                ]
            );

        return Inertia::render(
            'Counsellor/Cases/Index',
            [
                'cases' => $cases,
                'eligibleClients' => $eligibleClients,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'risk' => $risk,
                ],
                'statuses' => ClientCase::statuses(),
                'riskLevels' => ClientCase::riskLevels(),
            ]
        );
    }

    public function store(
        StoreCaseRequest $request
    ): RedirectResponse {
        $profile =
            $this->counsellorProfile($request);

        $data = $request->validated();

        $hasRelationship =
            CounsellingSession::query()
                ->where(
                    'client_profile_id',
                    $data['client_profile_id'],
                )
                ->where(
                    'counsellor_profile_id',
                    $profile->id,
                )
                ->whereIn(
                    'status',
                    [
                        'in_progress',
                        'completed',
                    ],
                )
                ->exists();

        if (! $hasRelationship) {
            throw ValidationException::withMessages([
                'client_profile_id' => 'A counselling relationship must exist before a clinical case can be opened.',
            ]);
        }

        $existing = ClientCase::query()
            ->where(
                'client_profile_id',
                $data['client_profile_id'],
            )
            ->where(
                'counsellor_profile_id',
                $profile->id,
            )
            ->whereIn(
                'status',
                [
                    ClientCase::STATUS_OPEN,
                    ClientCase::STATUS_ON_HOLD,
                ],
            )
            ->first();

        if ($existing) {
            return redirect()
                ->route(
                    'counsellor.cases.show',
                    $existing
                )
                ->with(
                    'info',
                    'An active case already exists for this client.'
                );
        }

        $case = ClientCase::create([
            ...$data,
            'counsellor_profile_id' => $profile->id,
            'opened_by' => $request->user()->id,
            'status' => ClientCase::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        return redirect()
            ->route(
                'counsellor.cases.show',
                $case
            )
            ->with(
                'success',
                'Clinical case opened.'
            );
    }

    public function show(
        Request $request,
        ClientCase $case,
    ): Response {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $case->load([
            'clientProfile.user:id,name,email,phone',
            'counsellorProfile.user:id,name,email',
            'goals.creator:id,name',
            'followUps.creator:id,name',
            'followUps.completedBy:id,name',
            'clinicalNotes.author:id,name',
            'clinicalNotes.signer:id,name',
        ]);

        $this->accessLogger->log(
            $request->user(),
            $case,
            'case',
            $case->id,
            'read',
            $request,
        );

        $sessions =
            CounsellingSession::query()
                ->where(
                    'client_profile_id',
                    $case->client_profile_id,
                )
                ->where(
                    'counsellor_profile_id',
                    $case->counsellor_profile_id,
                )
                ->whereIn(
                    'status',
                    [
                        'in_progress',
                        'completed',
                    ],
                )
                ->latest('started_at')
                ->get([
                    'id',
                    'status',
                    'started_at',
                    'ended_at',
                ]);

        return Inertia::render(
            'Counsellor/Cases/Show',
            [
                'caseRecord' => $case,
                'sessions' => $sessions,
                'caseStatuses' => [
                    ClientCase::STATUS_OPEN,
                    ClientCase::STATUS_ON_HOLD,
                ],
                'goalStatuses' => CaseGoal::statuses(),
                'followUpStatuses' => CaseFollowUp::statuses(),
                'riskLevels' => ClientCase::riskLevels(),
            ]
        );
    }

    public function update(
        UpdateCaseRequest $request,
        ClientCase $case,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        $case->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'Case summary updated.'
        );
    }

    public function close(
        Request $request,
        ClientCase $case,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        if (! $case->isClosed()) {
            $case->update([
                'status' => ClientCase::STATUS_CLOSED,
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
            ]);
        }

        return redirect()
            ->route('counsellor.cases.index')
            ->with(
                'success',
                'Clinical case closed.'
            );
    }

    public function storeGoal(
        StoreCaseGoalRequest $request,
        ClientCase $case,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        $case->goals()->create([
            ...$request->validated(),
            'status' => CaseGoal::STATUS_ACTIVE,
            'created_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Case goal added.'
        );
    }

    public function updateGoal(
        UpdateCaseGoalRequest $request,
        ClientCase $case,
        CaseGoal $goal,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        abort_unless(
            $goal->client_case_id === $case->id,
            404,
        );

        $data = $request->validated();

        if (
            $data['status']
            === CaseGoal::STATUS_ACHIEVED
        ) {
            $data['completed_at'] =
                $goal->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }

        $goal->update($data);

        return back()->with(
            'success',
            'Case goal updated.'
        );
    }

    public function storeFollowUp(
        StoreCaseFollowUpRequest $request,
        ClientCase $case,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        $case->followUps()->create([
            ...$request->validated(),
            'status' => CaseFollowUp::STATUS_PENDING,
            'created_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Follow-up action added.'
        );
    }

    public function updateFollowUp(
        UpdateCaseFollowUpRequest $request,
        ClientCase $case,
        CaseFollowUp $followUp,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        abort_unless(
            $followUp->client_case_id
                === $case->id,
            404,
        );

        $data = $request->validated();

        if (
            $data['status']
            === CaseFollowUp::STATUS_COMPLETED
        ) {
            $data['completed_at'] =
                $followUp->completed_at
                ?? now();

            $data['completed_by'] =
                $request->user()->id;
        } else {
            $data['completed_at'] = null;
            $data['completed_by'] = null;
        }

        $followUp->update($data);

        return back()->with(
            'success',
            'Follow-up action updated.'
        );
    }

    public function storeNote(
        StoreClinicalNoteRequest $request,
        ClientCase $case,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        $data = $request->validated();

        if (
            ! empty(
                $data['counselling_session_id']
            )
        ) {
            $valid =
                CounsellingSession::query()
                    ->whereKey(
                        $data[
                            'counselling_session_id'
                        ]
                    )
                    ->where(
                        'client_profile_id',
                        $case->client_profile_id,
                    )
                    ->where(
                        'counsellor_profile_id',
                        $case
                            ->counsellor_profile_id,
                    )
                    ->exists();

            if (! $valid) {
                throw ValidationException::withMessages([
                    'counselling_session_id' => 'The selected session does not belong to this case.',
                ]);
            }
        }

        DB::transaction(
            function () use (
                $request,
                $case,
                $data,
            ): void {
                $note =
                    $case->clinicalNotes()
                        ->create([
                            ...$data,
                            'author_id' => $request
                                ->user()
                                ->id,
                            'status' => ClinicalNote::STATUS_DRAFT,
                            'version' => 1,
                        ]);

                $this->versions->snapshot(
                    $note,
                    $request->user(),
                    'Initial clinical note draft.',
                );
            }
        );

        return back()->with(
            'success',
            'Clinical note saved as draft.'
        );
    }

    public function updateNote(
        UpdateClinicalNoteRequest $request,
        ClientCase $case,
        ClinicalNote $note,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        abort_unless(
            $note->client_case_id === $case->id,
            404,
        );

        abort_if(
            $note->isLocked(),
            422,
            'Signed or locked clinical notes cannot be edited.',
        );

        $data = $request->validated();

        $reason =
            $data['change_reason'] ?? null;

        unset($data['change_reason']);

        DB::transaction(
            function () use (
                $request,
                $note,
                $data,
                $reason,
            ): void {
                $note->update([
                    ...$data,
                    'version' => $note->version + 1,
                ]);

                $this->versions->snapshot(
                    $note->fresh(),
                    $request->user(),
                    $reason
                        ?: 'Clinical note draft updated.',
                );
            }
        );

        return back()->with(
            'success',
            'Clinical note updated and versioned.'
        );
    }

    public function signNote(
        Request $request,
        ClientCase $case,
        ClinicalNote $note,
    ): RedirectResponse {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        $this->ensureWritable($case);

        abort_unless(
            $note->client_case_id === $case->id,
            404,
        );

        abort_if(
            $note->isLocked(),
            422,
            'Clinical note is already signed or locked.',
        );

        abort_if(
            blank($note->note),
            422,
            'Clinical documentation is required before signing.',
        );

        DB::transaction(
            function () use (
                $request,
                $note,
            ): void {
                $now = now();

                $note->update([
                    'status' => ClinicalNote::STATUS_SIGNED,
                    'version' => $note->version + 1,
                    'signed_by' => $request->user()->id,
                    'signed_at' => $now,
                    'locked_at' => $now,
                ]);

                $this->versions->snapshot(
                    $note->fresh(),
                    $request->user(),
                    'Clinical note signed and locked.',
                );
            }
        );

        return back()->with(
            'success',
            'Clinical note signed and locked.'
        );
    }

    public function noteVersions(
        Request $request,
        ClientCase $case,
        ClinicalNote $note,
    ): Response {
        $case = $this->ownedCase(
            $request,
            $case,
        );

        abort_unless(
            $note->client_case_id === $case->id,
            404,
        );

        $note->load([
            'author:id,name',
            'signer:id,name',
            'versions.changedBy:id,name',
        ]);

        $this->accessLogger->log(
            $request->user(),
            $case,
            'clinical_note',
            $note->id,
            'version_history_read',
            $request,
        );

        return Inertia::render(
            'Counsellor/Cases/NoteVersions',
            [
                'caseRecord' => $case->load(
                    'clientProfile.user:id,name'
                ),
                'clinicalNote' => $note,
            ]
        );
    }

    private function counsellorProfile(
        Request $request
    ) {
        $profile =
            $request->user()
                ?->counsellorProfile;

        abort_unless(
            $profile,
            403,
            'A counsellor profile is required.'
        );

        return $profile;
    }

    private function ownedCase(
        Request $request,
        ClientCase $case,
    ): ClientCase {
        $profile =
            $this->counsellorProfile(
                $request
            );

        abort_unless(
            $case->counsellor_profile_id
                === $profile->id,
            403,
            'You do not have access to this clinical case.'
        );

        return $case;
    }

    private function ensureWritable(
        ClientCase $case
    ): void {
        abort_if(
            $case->isClosed(),
            422,
            'Closed cases are read-only.'
        );
    }
}
