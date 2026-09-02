<?php

namespace App\Http\Controllers\ClinicalSupervisor;

use App\Http\Controllers\Controller;
use App\Models\ClientCase;
use App\Models\ClinicalNote;
use App\Services\Clinical\ClinicalRecordAccessLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    public function __construct(
        private readonly ClinicalRecordAccessLogger $accessLogger,
    ) {}

    public function index(
        Request $request
    ): Response {
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
            ->with([
                'clientProfile.user:id,name,email,phone',
                'counsellorProfile.user:id,name,email',
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
                                            );
                                    }
                                )
                                ->orWhereHas(
                                    'counsellorProfile.user',
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
                    $status
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
                    $risk
                ),
            )
            ->latest('opened_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'ClinicalSupervisor/Cases/Index',
            [
                'cases' => $cases,
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

    public function show(
        Request $request,
        ClientCase $case,
    ): Response {
        $case->load([
            'clientProfile.user:id,name,email,phone',
            'counsellorProfile.user:id,name,email',
            'openedBy:id,name',
            'closedBy:id,name',
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
            'supervisor_read',
            $request,
        );

        return Inertia::render(
            'ClinicalSupervisor/Cases/Show',
            [
                'caseRecord' => $case,
            ]
        );
    }

    public function noteVersions(
        Request $request,
        ClientCase $case,
        ClinicalNote $note,
    ): Response {
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
            'supervisor_version_history_read',
            $request,
        );

        return Inertia::render(
            'ClinicalSupervisor/Cases/NoteVersions',
            [
                'caseRecord' => $case->load([
                    'clientProfile.user:id,name',
                    'counsellorProfile.user:id,name',
                ]),
                'clinicalNote' => $note,
            ]
        );
    }
}
