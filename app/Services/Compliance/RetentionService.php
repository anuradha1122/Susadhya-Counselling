<?php

namespace App\Services\Compliance;

use App\Models\RetentionPolicy;
use App\Models\RetentionRun;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class RetentionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {}

    public function run(
        RetentionPolicy $policy,
        ?User $actor = null,
        bool $execute = false
    ): RetentionRun {
        if (! $policy->enabled) {
            throw ValidationException::withMessages([
                'policy' => 'The retention policy is disabled.',
            ]);
        }

        if (
            $policy->retention_days === null
            || $policy->retention_days < 1
        ) {
            throw ValidationException::withMessages([
                'retention_days' => 'The retention policy requires an approved retention period.',
            ]);
        }

        $cutoff =
            now()->subDays(
                $policy->retention_days
            );

        $candidateCount =
            $this->candidateCount(
                $policy,
                $cutoff
            );

        $run =
            RetentionRun::query()
                ->create([
                    'retention_policy_id' => $policy->id,

                    'initiated_by' => $actor?->id,

                    'mode' => $execute
                            ? 'execute'
                            : 'dry_run',

                    'status' => 'running',

                    'candidate_count' => $candidateCount,

                    'processed_count' => 0,
                    'skipped_count' => 0,
                    'failed_count' => 0,

                    'summary' => [
                        'category' => $policy->category,

                        'action' => $policy->action,

                        'cutoff' => $cutoff
                            ->toIso8601String(),
                    ],

                    'started_at' => now(),
                ]);

        try {
            if (! $execute) {
                return $this->completeDryRun(
                    $run,
                    $policy,
                    $cutoff,
                    $candidateCount
                );
            }

            if (
                ! $this->canExecuteAutomatically(
                    $policy
                )
            ) {
                $run->forceFill([
                    'status' => 'blocked',

                    'skipped_count' => $candidateCount,

                    'summary' => [
                        ...(
                            $run->summary
                            ?? []
                        ),

                        'reason' => 'Automatic execution is not permitted for this retention policy.',
                    ],

                    'completed_at' => now(),
                ])->save();

                $this->auditLogger->record(
                    category: 'retention',
                    event: 'retention.execution_blocked',
                    action: 'execute',
                    subject: $policy,
                    result: 'blocked',
                    actor: $actor,
                    metadata: [
                        'category' => $policy->category,

                        'candidate_count' => $candidateCount,
                    ],
                );

                return $run->refresh();
            }

            $result =
                $this->executePolicy(
                    $policy,
                    $cutoff
                );

            $run->forceFill([
                'status' => 'completed',

                'processed_count' => $result[
                        'processed'
                    ],

                'skipped_count' => $result[
                        'skipped'
                    ],

                'failed_count' => $result[
                        'failed'
                    ],

                'summary' => [
                    ...(
                        $run->summary
                        ?? []
                    ),

                    'execution' => $result,
                ],

                'completed_at' => now(),
            ])->save();

            $this->auditLogger->record(
                category: 'retention',
                event: 'retention.executed',
                action: 'execute',
                subject: $policy,
                actor: $actor,
                metadata: [
                    'category' => $policy->category,

                    'candidate_count' => $candidateCount,

                    'processed_count' => $result[
                            'processed'
                        ],

                    'skipped_count' => $result[
                            'skipped'
                        ],

                    'failed_count' => $result[
                            'failed'
                        ],
                ],
            );

            return $run->refresh();
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',

                'failed_count' => $candidateCount,

                'summary' => [
                    ...(
                        $run->summary
                        ?? []
                    ),

                    'exception_class' => $exception::class,
                ],

                'completed_at' => now(),
            ])->save();

            $this->auditLogger->record(
                category: 'retention',
                event: 'retention.failed',
                action: $execute
                        ? 'execute'
                        : 'dry_run',
                subject: $policy,
                result: 'failure',
                actor: $actor,
                metadata: [
                    'category' => $policy->category,

                    'exception_class' => $exception::class,
                ],
            );

            throw $exception;
        }
    }

    public function candidateCount(
        RetentionPolicy $policy,
        Carbon $cutoff
    ): int {
        $source =
            $this->sourceFor(
                $policy->category
            );

        if ($source === null) {
            return 0;
        }

        [
            'table' => $table,
            'date_column' => $dateColumn,
        ] = $source;

        if (
            ! Schema::hasTable(
                $table
            )
            || ! Schema::hasColumn(
                $table,
                $dateColumn
            )
        ) {
            return 0;
        }

        return DB::table($table)
            ->where(
                $dateColumn,
                '<',
                $cutoff
            )
            ->count();
    }

    private function completeDryRun(
        RetentionRun $run,
        RetentionPolicy $policy,
        Carbon $cutoff,
        int $candidateCount
    ): RetentionRun {
        $run->forceFill([
            'status' => 'completed',

            'summary' => [
                ...(
                    $run->summary
                    ?? []
                ),

                'dry_run' => true,

                'candidate_count' => $candidateCount,

                'cutoff' => $cutoff
                    ->toIso8601String(),

                'message' => 'No records were modified.',
            ],

            'completed_at' => now(),
        ])->save();

        $this->auditLogger->record(
            category: 'retention',
            event: 'retention.dry_run_completed',
            action: 'dry_run',
            subject: $policy,
            actor: $run->initiator,
            metadata: [
                'category' => $policy->category,

                'candidate_count' => $candidateCount,

                'cutoff' => $cutoff
                    ->toIso8601String(),
            ],
        );

        return $run->refresh();
    }

    private function canExecuteAutomatically(
        RetentionPolicy $policy
    ): bool {
        if (
            ! $policy
                ->automatic_execution
        ) {
            return false;
        }

        if (
            $policy->action
            !== RetentionPolicy::ACTION_DELETE
        ) {
            return false;
        }

        $allowed =
            config(
                'compliance.retention.automatic_categories',
                []
            );

        return in_array(
            $policy->category,
            $allowed,
            true
        );
    }

    private function executePolicy(
        RetentionPolicy $policy,
        Carbon $cutoff
    ): array {
        return match (
            $policy->category
        ) {
            RetentionPolicy::CATEGORY_NOTIFICATIONS => $this->deleteNotifications(
                $cutoff
            ),

            default => [
                'processed' => 0,
                'skipped' => $this->candidateCount(
                    $policy,
                    $cutoff
                ),
                'failed' => 0,
                'message' => 'No automatic executor is registered for this category.',
            ],
        };
    }

    private function deleteNotifications(
        Carbon $cutoff
    ): array {
        if (
            ! Schema::hasTable(
                'notifications'
            )
            || ! Schema::hasColumn(
                'notifications',
                'created_at'
            )
        ) {
            return [
                'processed' => 0,
                'skipped' => 0,
                'failed' => 0,
                'message' => 'Notifications table is unavailable.',
            ];
        }

        $ids =
            DB::table(
                'notifications'
            )
                ->where(
                    'created_at',
                    '<',
                    $cutoff
                )
                ->pluck('id')
                ->all();

        if ($ids === []) {
            return [
                'processed' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];
        }

        try {
            DB::transaction(
                function () use (
                    $ids
                ): void {
                    $this
                        ->deleteNotificationChildren(
                            $ids
                        );

                    DB::table(
                        'notifications'
                    )
                        ->whereIn(
                            'id',
                            $ids
                        )
                        ->delete();
                }
            );

            return [
                'processed' => count($ids),

                'skipped' => 0,
                'failed' => 0,
            ];
        } catch (Throwable) {
            return [
                'processed' => 0,
                'skipped' => 0,

                'failed' => count($ids),

                'message' => 'Notification deletion failed. Existing foreign-key relationships were left untouched.',
            ];
        }
    }

    private function deleteNotificationChildren(
        array $notificationIds
    ): void {
        if (
            Schema::hasTable(
                'notification_dispatches'
            )
            && Schema::hasColumn(
                'notification_dispatches',
                'notification_id'
            )
        ) {
            $dispatchIds =
                DB::table(
                    'notification_dispatches'
                )
                    ->whereIn(
                        'notification_id',
                        $notificationIds
                    )
                    ->pluck('id')
                    ->all();

            if (
                $dispatchIds !== []
                && Schema::hasTable(
                    'notification_deliveries'
                )
            ) {
                foreach (
                    [
                        'notification_dispatch_id',
                        'dispatch_id',
                    ] as $column
                ) {
                    if (
                        Schema::hasColumn(
                            'notification_deliveries',
                            $column
                        )
                    ) {
                        DB::table(
                            'notification_deliveries'
                        )
                            ->whereIn(
                                $column,
                                $dispatchIds
                            )
                            ->delete();
                    }
                }
            }

            DB::table(
                'notification_dispatches'
            )
                ->whereIn(
                    'notification_id',
                    $notificationIds
                )
                ->delete();
        }

        if (
            Schema::hasTable(
                'notification_deliveries'
            )
            && Schema::hasColumn(
                'notification_deliveries',
                'notification_id'
            )
        ) {
            DB::table(
                'notification_deliveries'
            )
                ->whereIn(
                    'notification_id',
                    $notificationIds
                )
                ->delete();
        }
    }

    private function sourceFor(
        string $category
    ): ?array {
        return match ($category) {
            RetentionPolicy::CATEGORY_CLINICAL => [
                'table' => 'clinical_notes',

                'date_column' => $this->dateColumn(
                    'clinical_notes'
                ),
            ],

            RetentionPolicy::CATEGORY_FINANCIAL => [
                'table' => 'payments',

                'date_column' => $this->dateColumn(
                    'payments'
                ),
            ],

            RetentionPolicy::CATEGORY_DOCUMENTS => [
                'table' => 'secure_documents',

                'date_column' => $this->dateColumn(
                    'secure_documents'
                ),
            ],

            RetentionPolicy::CATEGORY_NOTIFICATIONS => [
                'table' => 'notifications',

                'date_column' => $this->dateColumn(
                    'notifications'
                ),
            ],

            RetentionPolicy::CATEGORY_AUDIT => [
                'table' => 'audit_events',

                'date_column' => Schema::hasColumn(
                    'audit_events',
                    'occurred_at'
                )
                        ? 'occurred_at'
                        : 'created_at',
            ],

            RetentionPolicy::CATEGORY_OPERATIONAL => [
                'table' => 'operational_exceptions',

                'date_column' => $this->dateColumn(
                    'operational_exceptions'
                ),
            ],

            default => null,
        };
    }

    private function dateColumn(
        string $table
    ): string {
        if (
            Schema::hasTable($table)
            && Schema::hasColumn(
                $table,
                'created_at'
            )
        ) {
            return 'created_at';
        }

        if (
            Schema::hasTable($table)
            && Schema::hasColumn(
                $table,
                'updated_at'
            )
        ) {
            return 'updated_at';
        }

        return 'created_at';
    }
}
