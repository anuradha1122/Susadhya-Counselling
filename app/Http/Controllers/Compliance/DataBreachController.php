<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\StoreDataBreachRequest;
use App\Http\Requests\Compliance\UpdateDataBreachRequest;
use App\Models\DataBreach;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DataBreachController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                'string',
                'max:32',
            ],

            'severity' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $query = DataBreach::query()
            ->with([
                'reporter:id,name',
                'assignee:id,name',
            ]);

        if (
            isset($filters['search'])
            && $filters['search'] !== ''
        ) {
            $search =
                $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'title',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            isset($filters['status'])
            && $filters['status'] !== ''
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (
            isset($filters['severity'])
            && $filters['severity'] !== ''
        ) {
            $query->where(
                'severity',
                $filters['severity']
            );
        }

        $breaches =
            $query
                ->latest('detected_at')
                ->paginate(20)
                ->withQueryString()
                ->through(
                    fn (DataBreach $breach): array => [
                        'uuid' => $breach->uuid,

                        'reference' => $breach->reference,

                        'title' => $breach->title,

                        'severity' => $breach->severity,

                        'status' => $breach->status,

                        'detected_at' => $breach
                            ->detected_at
                            ?->toIso8601String(),

                        'affected_subject_count' => $breach
                            ->affected_subject_count,

                        'reported_by' => $breach
                            ->reporter
                            ?->name,

                        'assigned_to' => $breach
                            ->assignee
                            ?->name,
                    ]
                );

        return Inertia::render(
            'Compliance/Breaches/Index',
            [
                'breaches' => $breaches,

                'filters' => [
                    'search' => $filters['search'] ?? '',

                    'status' => $filters['status'] ?? '',

                    'severity' => $filters['severity'] ?? '',
                ],

                'statuses' => DataBreach::statuses(),

                'severities' => DataBreach::severities(),

                'assignees' => $this->assignees(),
            ]
        );
    }

    public function store(
        StoreDataBreachRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data = $request->validated();

        $breach =
            DataBreach::query()->create([
                ...$data,

                'status' => DataBreach::STATUS_OPEN,

                'reported_by' => $request->user()->id,
            ]);

        $auditLogger->record(
            category: 'breach',
            event: 'breach.created',
            action: 'create',
            subject: $breach,
            actor: $request->user(),
            metadata: [
                'reference' => $breach->reference,

                'severity' => $breach->severity,
            ],
        );

        return redirect()
            ->route(
                'compliance.breaches.show',
                $breach
            )
            ->with(
                'success',
                'Breach record created.'
            );
    }

    public function show(
        DataBreach $breach
    ): Response {
        $breach->load([
            'reporter:id,name,email',
            'assignee:id,name,email',
        ]);

        return Inertia::render(
            'Compliance/Breaches/Show',
            [
                'breach' => [
                    'uuid' => $breach->uuid,

                    'reference' => $breach->reference,

                    'title' => $breach->title,

                    'severity' => $breach->severity,

                    'status' => $breach->status,

                    'reported_by' => $breach->reporter
                            ? [
                                'name' => $breach
                                    ->reporter
                                    ->name,

                                'email' => $breach
                                    ->reporter
                                    ->email,
                            ]
                            : null,

                    'assigned_to' => $breach->assignee
                            ? [
                                'id' => $breach
                                    ->assignee
                                    ->id,

                                'name' => $breach
                                    ->assignee
                                    ->name,

                                'email' => $breach
                                    ->assignee
                                    ->email,
                            ]
                            : null,

                    'detected_at' => $this->dateTime(
                        $breach->detected_at
                    ),

                    'occurred_at' => $this->dateTime(
                        $breach->occurred_at
                    ),

                    'contained_at' => $this->dateTime(
                        $breach->contained_at
                    ),

                    'reported_to_authority_at' => $this->dateTime(
                        $breach
                            ->reported_to_authority_at
                    ),

                    'closed_at' => $this->dateTime(
                        $breach->closed_at
                    ),

                    'affected_subject_count' => $breach
                        ->affected_subject_count,

                    'data_categories' => $breach
                        ->data_categories
                        ?? [],

                    'systems_affected' => $breach
                        ->systems_affected
                        ?? [],

                    'summary' => $breach->summary,

                    'containment_actions' => $breach
                        ->containment_actions,

                    'notification_decision' => $breach
                        ->notification_decision,

                    'authority_reference' => $breach
                        ->authority_reference,
                ],

                'statuses' => DataBreach::statuses(),

                'severities' => DataBreach::severities(),

                'assignees' => $this->assignees(),
            ]
        );
    }

    public function update(
        UpdateDataBreachRequest $request,
        DataBreach $breach,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $data = $request->validated();

        $previous = [
            'status' => $breach->status,

            'severity' => $breach->severity,

            'assigned_to' => $breach->assigned_to,
        ];

        if (
            in_array(
                $data['status'],
                [
                    DataBreach::STATUS_CONTAINED,
                    DataBreach::STATUS_CLOSED,
                ],
                true
            )
            && $breach->contained_at === null
        ) {
            $data['contained_at'] =
                now();
        }

        if (
            $data['status']
            === DataBreach::STATUS_CLOSED
        ) {
            $data['closed_at'] =
                $breach->closed_at
                ?? now();
        } else {
            $data['closed_at'] =
                null;
        }

        $breach->update($data);

        $auditLogger->record(
            category: 'breach',
            event: 'breach.updated',
            action: 'update',
            subject: $breach,
            actor: $request->user(),
            metadata: [
                'reference' => $breach->reference,

                'before' => $previous,

                'after' => [
                    'status' => $breach->status,

                    'severity' => $breach->severity,

                    'assigned_to' => $breach
                        ->assigned_to,
                ],
            ],
        );

        return back()->with(
            'success',
            'Breach record updated.'
        );
    }

    private function assignees(): array
    {
        return User::query()
            ->whereHas(
                'roles',
                fn ($query) => $query->whereIn(
                    'name',
                    [
                        'privacy_officer',
                        'super_admin',
                    ]
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ])
            ->toArray();
    }

    private function dateTime(
        mixed $value
    ): ?string {
        return $value
            ?->format(
                'Y-m-d\TH:i'
            );
    }
}
