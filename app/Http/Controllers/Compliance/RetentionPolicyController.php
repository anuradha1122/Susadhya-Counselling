<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\UpdateRetentionPolicyRequest;
use App\Models\RetentionPolicy;
use App\Models\RetentionRun;
use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\RetentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RetentionPolicyController extends Controller
{
    public function index(): Response
    {
        $policies =
            RetentionPolicy::query()
                ->with('updatedBy:id,name')
                ->orderBy('category')
                ->get()
                ->map(
                    fn (RetentionPolicy $policy): array => [
                        'uuid' => $policy->uuid,

                        'category' => $policy->category,

                        'name' => $policy->name,

                        'description' => $policy->description,

                        'retention_days' => $policy->retention_days,

                        'action' => $policy->action,

                        'enabled' => $policy->enabled,

                        'automatic_execution' => $policy
                            ->automatic_execution,

                        'legal_basis' => $policy->legal_basis,

                        'reviewed_at' => $policy->reviewed_at
                            ?->toIso8601String(),

                        'updated_by' => $policy->updatedBy?->name,

                        'automatic_allowed' => in_array(
                            $policy->category,
                            config(
                                'compliance.retention.automatic_categories',
                                []
                            ),
                            true
                        ),
                    ]
                )
                ->values();

        $runs =
            RetentionRun::query()
                ->with([
                    'policy:id,uuid,name,category',
                    'initiator:id,name',
                ])
                ->latest('started_at')
                ->limit(50)
                ->get()
                ->map(
                    fn (RetentionRun $run): array => [
                        'uuid' => $run->uuid,

                        'policy' => $run->policy
                                ? [
                                    'name' => $run
                                        ->policy
                                        ->name,

                                    'category' => $run
                                        ->policy
                                        ->category,
                                ]
                                : null,

                        'mode' => $run->mode,

                        'status' => $run->status,

                        'candidate_count' => $run->candidate_count,

                        'processed_count' => $run->processed_count,

                        'skipped_count' => $run->skipped_count,

                        'failed_count' => $run->failed_count,

                        'initiated_by' => $run
                            ->initiator
                            ?->name,

                        'started_at' => $run->started_at
                            ?->toIso8601String(),

                        'completed_at' => $run->completed_at
                            ?->toIso8601String(),
                    ]
                )
                ->values();

        return Inertia::render(
            'Compliance/Retention/Index',
            [
                'policies' => $policies,
                'runs' => $runs,
                'actions' => RetentionPolicy::actions(),
            ]
        );
    }

    public function update(
        UpdateRetentionPolicyRequest $request,
        RetentionPolicy $retentionPolicy,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $before = [
            'retention_days' => $retentionPolicy->retention_days,

            'action' => $retentionPolicy->action,

            'enabled' => $retentionPolicy->enabled,

            'automatic_execution' => $retentionPolicy
                ->automatic_execution,
        ];

        $data = $request->validated();

        $retentionPolicy->update([
            'retention_days' => $data['retention_days'],

            'action' => $data['action'],

            'enabled' => $data['enabled'],

            'automatic_execution' => $data[
                    'automatic_execution'
                ],

            'legal_basis' => $data['legal_basis']
                ?? null,

            'updated_by' => $request->user()->id,

            'reviewed_at' => now(),
        ]);

        $auditLogger->record(
            category: 'retention',
            event: 'retention.policy_updated',
            action: 'update',
            subject: $retentionPolicy,
            actor: $request->user(),
            metadata: [
                'category' => $retentionPolicy->category,

                'before' => $before,

                'after' => [
                    'retention_days' => $retentionPolicy
                        ->retention_days,

                    'action' => $retentionPolicy
                        ->action,

                    'enabled' => $retentionPolicy
                        ->enabled,

                    'automatic_execution' => $retentionPolicy
                        ->automatic_execution,
                ],
            ],
        );

        return back()->with(
            'success',
            'Retention policy updated.'
        );
    }

    public function dryRun(
        Request $request,
        RetentionPolicy $retentionPolicy,
        RetentionService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'compliance.retention.manage'
            ),
            403
        );

        $run = $service->run(
            policy: $retentionPolicy,
            actor: $request->user(),
            execute: false,
        );

        return back()->with(
            'success',
            sprintf(
                'Dry run completed. %d candidate record(s) identified.',
                $run->candidate_count
            )
        );
    }

    public function execute(
        Request $request,
        RetentionPolicy $retentionPolicy,
        RetentionService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'compliance.retention.manage'
            ),
            403
        );

        $run = $service->run(
            policy: $retentionPolicy,
            actor: $request->user(),
            execute: true,
        );

        if ($run->status === 'blocked') {
            return back()->with(
                'error',
                'Execution was blocked by the retention safety policy.'
            );
        }

        return back()->with(
            'success',
            sprintf(
                'Retention execution completed. %d record(s) processed.',
                $run->processed_count
            )
        );
    }
}
