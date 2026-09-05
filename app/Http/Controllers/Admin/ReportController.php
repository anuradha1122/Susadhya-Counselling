<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Reports\CsvReportExporter;
use App\Services\Reports\OperationalReportService;
use App\Services\Reports\ReportPdfService;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(
        ReportFilterRequest $request,
        OperationalReportService $reports
    ): InertiaResponse {
        $filters = $request->filters();

        return Inertia::render(
            'Admin/Reports/Index',
            [
                'filters' => $filters,
                'summary' => $reports->summary($filters),
                'appointments' => $reports->appointments($filters),
                'counsellorActivity' => $reports->counsellorActivity(
                    $filters
                ),
                'cancellationsByService' => $reports->cancellationsByService(
                    $filters
                ),
                'counsellors' => $reports->counsellorOptions(),
                'options' => [
                    'statuses' => [
                        'pending',
                        'confirmed',
                        'rescheduled',
                        'completed',
                        'cancelled',
                        'no_show',
                    ],
                    'modes' => [
                        'online',
                        'in_person',
                    ],
                ],
            ]
        );
    }

    public function csv(
        ReportFilterRequest $request,
        OperationalReportService $reports,
        CsvReportExporter $csv
    ): StreamedResponse {
        $filters = $request->filters();

        $rows = $reports
            ->exportAppointments($filters)
            ->map(fn (array $row): array => [
                $row['uuid'],
                $row['appointment_date'],
                $row['start_time'],
                $row['end_time'],
                $row['client_name'],
                $row['counsellor_name'],
                $row['service_name'],
                $row['mode'],
                $row['status'],
                $row['fee_currency'],
                $row['fee_amount'],
            ]);

        return $csv->download(
            sprintf(
                'susadhya-operational-report-%s-to-%s.csv',
                $filters['from'],
                $filters['to']
            ),
            [
                'Appointment UUID',
                'Date',
                'Start',
                'End',
                'Client',
                'Counsellor',
                'Service',
                'Mode',
                'Status',
                'Fee Currency',
                'Fee Amount',
            ],
            $rows
        );
    }

    public function pdf(
        ReportFilterRequest $request,
        OperationalReportService $reports,
        ReportPdfService $pdf
    ): Response {
        $filters = $request->filters();

        return $pdf->operational(
            [
                'filters' => $filters,
                'summary' => $reports->summary($filters),
                'appointments' => $reports->exportAppointments(
                    $filters
                ),
                'counsellorActivity' => $reports->counsellorActivity(
                    $filters
                ),
                'cancellationsByService' => $reports->cancellationsByService(
                    $filters
                ),
                'generatedAt' => now(),
                'generatedBy' => $request->user()->name,
            ],
            sprintf(
                'susadhya-operational-report-%s-to-%s.pdf',
                $filters['from'],
                $filters['to']
            )
        );
    }
}
