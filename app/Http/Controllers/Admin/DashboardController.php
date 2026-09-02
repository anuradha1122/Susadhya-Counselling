<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Appointments\AppointmentDashboardMetricService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        AppointmentDashboardMetricService $appointmentMetrics
    ): Response {
        return $this->index($request, $appointmentMetrics);
    }

    public function index(
        Request $request,
        AppointmentDashboardMetricService $appointmentMetrics
    ): Response {
        return Inertia::render('Admin/Dashboard', [
            'appointmentMetrics' => $appointmentMetrics->admin(),
        ]);
    }
}
