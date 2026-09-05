<?php

namespace App\Console\Commands;

use App\Services\Compliance\PrivacyExportService;
use Illuminate\Console\Command;

class PurgePrivacyExports extends Command
{
    protected $signature =
        'compliance:purge-privacy-exports';

    protected $description =
        'Remove expired temporary privacy export files.';

    public function handle(
        PrivacyExportService $privacyExportService
    ): int {
        $count =
            $privacyExportService
                ->purgeExpiredExports();

        $this->components->info(
            sprintf(
                '%d expired privacy export file(s) purged.',
                $count
            )
        );

        return self::SUCCESS;
    }
}
