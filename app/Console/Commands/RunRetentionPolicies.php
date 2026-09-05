<?php

namespace App\Console\Commands;

use App\Models\RetentionPolicy;
use App\Services\Compliance\RetentionService;
use Illuminate\Console\Command;
use Throwable;

class RunRetentionPolicies extends Command
{
    protected $signature =
        'compliance:run-retention
        {category? : Optional retention policy category}
        {--execute : Execute approved automatic policies instead of dry-run only}';

    protected $description =
        'Run approved compliance retention policies in dry-run or controlled execution mode.';

    public function handle(
        RetentionService $retentionService
    ): int {
        $category =
            $this->argument(
                'category'
            );

        $execute =
            (bool) $this->option(
                'execute'
            );

        $query =
            RetentionPolicy::query()
                ->where(
                    'enabled',
                    true
                )
                ->orderBy(
                    'category'
                );

        if (
            is_string($category)
            && $category !== ''
        ) {
            $query->where(
                'category',
                $category
            );
        }

        $policies =
            $query->get();

        if ($policies->isEmpty()) {
            $this->components->info(
                'No enabled retention policies matched the request.'
            );

            return self::SUCCESS;
        }

        $rows = [];

        foreach (
            $policies as $policy
        ) {
            try {
                $run =
                    $retentionService->run(
                        policy: $policy,
                        actor: null,
                        execute: $execute,
                    );

                $rows[] = [
                    $policy->category,
                    $run->mode,
                    $run->status,
                    $run->candidate_count,
                    $run->processed_count,
                    $run->skipped_count,
                    $run->failed_count,
                ];
            } catch (Throwable $exception) {
                $rows[] = [
                    $policy->category,
                    $execute
                        ? 'execute'
                        : 'dry_run',
                    'error',
                    '-',
                    '-',
                    '-',
                    '-',
                ];

                $this->components->error(
                    sprintf(
                        '%s: %s',
                        $policy->category,
                        $exception->getMessage()
                    )
                );
            }
        }

        $this->table(
            [
                'Category',
                'Mode',
                'Status',
                'Candidates',
                'Processed',
                'Skipped',
                'Failed',
            ],
            $rows
        );

        return self::SUCCESS;
    }
}
