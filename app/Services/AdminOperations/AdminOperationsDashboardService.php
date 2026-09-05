<?php

namespace App\Services\AdminOperations;

use App\Models\Appointment;
use App\Models\CaseEscalation;
use App\Models\NotificationDelivery;
use App\Models\OperationalException;

class AdminOperationsDashboardService
{
    public function metrics(): array
    {
        return [
            'todayAppointments' => Appointment::query()
                ->whereDate(
                    'appointment_date',
                    now()->toDateString()
                )
                ->count(),

            'pendingAppointments' => Appointment::query()
                ->where(
                    'status',
                    Appointment::STATUS_PENDING
                )
                ->count(),

            'openExceptions' => OperationalException::query()
                ->whereIn(
                    'status',
                    OperationalException::activeStatuses()
                )
                ->count(),

            'overdueExceptions' => OperationalException::query()
                ->whereIn(
                    'status',
                    OperationalException::activeStatuses()
                )
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count(),

            'openEscalations' => CaseEscalation::query()
                ->whereIn(
                    'status',
                    CaseEscalation::activeStatuses()
                )
                ->count(),

            'recentNotificationFailures' => NotificationDelivery::query()
                ->whereIn(
                    'status',
                    [
                        'failed',
                        'unavailable',
                    ]
                )
                ->where(
                    'created_at',
                    '>=',
                    now()->subDay()
                )
                ->count(),
        ];
    }
}
