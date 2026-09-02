<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;

class AppointmentDashboardMetricService
{
    public function admin(): array
    {
        return $this->metricsForQuery(Appointment::query());
    }

    public function forClient(ClientProfile $clientProfile): array
    {
        return $this->metricsForQuery(
            Appointment::query()->where('client_profile_id', $clientProfile->id)
        );
    }

    public function forCounsellor(CounsellorProfile $counsellorProfile): array
    {
        return $this->metricsForQuery(
            Appointment::query()->where('counsellor_profile_id', $counsellorProfile->id)
        );
    }

    private function metricsForQuery(Builder $query): array
    {
        $today = now()->toDateString();
        $nextSevenDays = now()->addDays(7)->toDateString();

        return [
            'total' => (clone $query)->count(),
            'pending' => $this->countByStatus($query, Appointment::STATUS_PENDING),
            'confirmed' => $this->countByStatus($query, Appointment::STATUS_CONFIRMED),
            'rescheduled' => $this->countByStatus($query, Appointment::STATUS_RESCHEDULED),
            'completed' => $this->countByStatus($query, Appointment::STATUS_COMPLETED),
            'cancelled' => $this->countByStatus($query, Appointment::STATUS_CANCELLED),
            'no_show' => $this->countByStatus($query, Appointment::STATUS_NO_SHOW),
            'today' => (clone $query)
                ->whereDate('appointment_date', $today)
                ->count(),
            'upcoming' => (clone $query)
                ->whereDate('appointment_date', '>=', $today)
                ->count(),
            'upcoming_next_7_days' => (clone $query)
                ->whereDate('appointment_date', '>=', $today)
                ->whereDate('appointment_date', '<=', $nextSevenDays)
                ->count(),
            'past' => (clone $query)
                ->whereDate('appointment_date', '<', $today)
                ->count(),
            'due_reminders' => (clone $query)
                ->reminderDue()
                ->count(),
        ];
    }

    private function countByStatus(Builder $query, string $status): int
    {
        return (clone $query)
            ->where('status', $status)
            ->count();
    }
}
