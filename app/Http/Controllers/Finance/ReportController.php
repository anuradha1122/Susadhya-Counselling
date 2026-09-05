<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Reports\CsvReportExporter;
use App\Services\Reports\FinancialReportService;
use App\Services\Reports\ReportPdfService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(
        ReportFilterRequest $request,
        FinancialReportService $reports
    ): InertiaResponse {
        $filters = $request->filters();

        return Inertia::render(
            'Finance/Reports/Index',
            [
                'filters' => $filters,
                'summary' => $reports->summary($filters),
                'payments' => $reports->payments($filters),
                'options' => [
                    'methods' => [
                        'gateway',
                        'manual',
                        'waived',
                    ],
                ],
            ]
        );
    }

    public function csv(
        ReportFilterRequest $request,
        FinancialReportService $reports,
        CsvReportExporter $csv
    ): StreamedResponse {
        $filters = $request->filters();

        $rows = $reports
            ->exportPayments($filters)
            ->map(fn (array $row): array => [
                $row['uuid'],
                $row['paid_at'],
                $row['appointment_uuid'],
                $row['counsellor_name'],
                $row['service_name'],
                $row['method'],
                $row['provider'],
                $row['currency'],
                $row['amount'],
                $row['status'],
                $row['reconciliation_status'],
            ]);

        return $csv->download(
            sprintf(
                'susadhya-revenue-report-%s-to-%s.csv',
                $filters['from'],
                $filters['to']
            ),
            [
                'Payment UUID',
                'Paid At',
                'Appointment UUID',
                'Counsellor',
                'Service',
                'Method',
                'Provider',
                'Currency',
                'Amount',
                'Status',
                'Reconciliation',
            ],
            $rows
        );
    }

    public function pdf(
        ReportFilterRequest $request,
        FinancialReportService $reports,
        ReportPdfService $pdf
    ): Response {
        $filters = $request->filters();

        return $pdf->financial(
            [
                'filters' => $filters,
                'summary' => $reports->summary($filters),
                'payments' => $reports->exportPayments(
                    $filters
                ),
                'generatedAt' => now(),
                'generatedBy' => $request->user()->name,
            ],
            sprintf(
                'susadhya-revenue-report-%s-to-%s.pdf',
                $filters['from'],
                $filters['to']
            )
        );
    }
}
