<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvReportExporter
{
    public function download(
        string $filename,
        array $headings,
        Collection $rows
    ): StreamedResponse {
        return response()->streamDownload(
            function () use (
                $headings,
                $rows
            ): void {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                if ($handle === false) {
                    return;
                }

                /*
                 * UTF-8 BOM improves compatibility with
                 * spreadsheet software opening CSV files.
                 */
                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    $headings
                );

                foreach ($rows as $row) {
                    fputcsv(
                        $handle,
                        array_values($row)
                    );
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }
}
