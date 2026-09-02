<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
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
        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return Inertia::render('Client/Dashboard', [
            'appointmentMetrics' => $appointmentMetrics->forClient($clientProfile),
        ]);
    }
}
