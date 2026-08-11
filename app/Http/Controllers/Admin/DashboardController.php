<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render(
            'Admin/Dashboard',
            [
                'stats' => [
                    'activeUsers' => User::query()
                        ->where('is_active', true)
                        ->count(),

                    'counsellors' => User::role(
                        'counsellor'
                    )->count(),

                    'todayAppointments' => 0,
                    'pendingPayments' => 0,
                ],
            ]
        );
    }
}
