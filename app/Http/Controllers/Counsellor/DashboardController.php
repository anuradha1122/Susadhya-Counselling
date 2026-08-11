<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render(
            'Counsellor/Dashboard',
            [
                'stats' => [
                    'todayAppointments' => 0,
                    'upcomingAppointments' => 0,
                    'completedAppointments' => 0,
                    'monthlyEarnings' => 0,
                ],
            ]
        );
    }
}
