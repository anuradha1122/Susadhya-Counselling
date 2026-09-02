<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\CounsellorProfile;
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
        $counsellorProfile = CounsellorProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return Inertia::render('Counsellor/Dashboard', [
            'appointmentMetrics' => $appointmentMetrics->forCounsellor($counsellorProfile),
        ]);
    }
}
