<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;

class AppointmentConflictService
{
    public function counsellorHasConflict(
        CounsellorProfile $counsellorProfile,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreAppointmentId = null
    ): bool {
        return Appointment::query()
            ->activeBooking()
            ->where('counsellor_profile_id', $counsellorProfile->id)
            ->whereDate('appointment_date', $date)
            ->when(
                $ignoreAppointmentId,
                fn (Builder $query, int $appointmentId) => $query->whereKeyNot($appointmentId)
            )
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function clientHasConflict(
        ClientProfile $clientProfile,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreAppointmentId = null
    ): bool {
        return Appointment::query()
            ->activeBooking()
            ->where('client_profile_id', $clientProfile->id)
            ->whereDate('appointment_date', $date)
            ->when(
                $ignoreAppointmentId,
                fn (Builder $query, int $appointmentId) => $query->whereKeyNot($appointmentId)
            )
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function hasAnyConflict(
        ClientProfile $clientProfile,
        CounsellorProfile $counsellorProfile,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreAppointmentId = null
    ): bool {
        return $this->clientHasConflict(
            $clientProfile,
            $date,
            $startTime,
            $endTime,
            $ignoreAppointmentId
        ) || $this->counsellorHasConflict(
            $counsellorProfile,
            $date,
            $startTime,
            $endTime,
            $ignoreAppointmentId
        );
    }
}
