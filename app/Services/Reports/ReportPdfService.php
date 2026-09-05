<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReportPdfService
{
    public function operational(
        array $data,
        string $filename
    ): Response {
        return Pdf::loadView(
            'reports.operational',
            $data
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download($filename);
    }

    public function financial(
        array $data,
        string $filename
    ): Response {
        return Pdf::loadView(
            'reports.revenue',
            $data
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download($filename);
    }
}
