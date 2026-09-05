<?php

namespace App\Services\Reports;

use App\Models\Appointment;
use App\Models\CounsellorProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperationalReportService
{
    public function summary(array $filters): array
    {
        $query = $this->appointmentQuery(
            $filters,
            includeStatusFilter: false
        );

        $total = (clone $query)->count();

        $pending = $this->countStatus(
            $query,
            Appointment::STATUS_PENDING
        );

        $confirmed = $this->countStatus(
            $query,
            Appointment::STATUS_CONFIRMED
        );

        $completed = $this->countStatus(
            $query,
            Appointment::STATUS_COMPLETED
        );

        $cancelled = $this->countStatus(
            $query,
            Appointment::STATUS_CANCELLED
        );

        $noShow = $this->countStatus(
            $query,
            Appointment::STATUS_NO_SHOW
        );

        $rescheduled = $this->countStatus(
            $query,
            Appointment::STATUS_RESCHEDULED
        );

        $attendanceResolved =
            $completed + $noShow;

        $utilisationRate =
            $attendanceResolved > 0
                ? round(
                    ($completed / $attendanceResolved) * 100,
                    2
                )
                : 0.0;

        $cancellationRate =
            $total > 0
                ? round(
                    ($cancelled / $total) * 100,
                    2
                )
                : 0.0;

        $noShowRate =
            $attendanceResolved > 0
                ? round(
                    ($noShow / $attendanceResolved) * 100,
                    2
                )
                : 0.0;

        return [
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_show' => $noShow,
            'rescheduled' => $rescheduled,

            'attendance_resolved' => $attendanceResolved,

            'utilisation_rate' => $utilisationRate,

            'cancellation_rate' => $cancellationRate,

            'no_show_rate' => $noShowRate,
        ];
    }

    public function appointments(
        array $filters,
        int $perPage = 25
    ): LengthAwarePaginator {
        return $this
            ->appointmentQuery($filters)
            ->with([
                'clientProfile.user:id,name',
                'counsellorProfile.user:id,name',
                'counsellingService:id,name',
            ])
            ->orderByDesc(
                'appointment_date'
            )
            ->orderByDesc(
                'start_time'
            )
            ->paginate($perPage)
            ->withQueryString()
            ->through(
                fn (
                    Appointment $appointment
                ): array => $this->formatAppointment(
                    $appointment
                )
            );
    }

    public function exportAppointments(
        array $filters
    ): Collection {
        return $this
            ->appointmentQuery($filters)
            ->with([
                'clientProfile.user:id,name',
                'counsellorProfile.user:id,name',
                'counsellingService:id,name',
            ])
            ->orderBy(
                'appointment_date'
            )
            ->orderBy(
                'start_time'
            )
            ->get()
            ->map(
                fn (
                    Appointment $appointment
                ): array => $this->formatAppointment(
                    $appointment
                )
            );
    }

    public function counsellorActivity(
        array $filters
    ): Collection {
        $query = DB::table(
            'counsellor_profiles as cp'
        )
            ->join(
                'users as u',
                'u.id',
                '=',
                'cp.user_id'
            )
            ->leftJoin(
                'appointments as a',
                function ($join) use (
                    $filters
                ): void {
                    $join->on(
                        'a.counsellor_profile_id',
                        '=',
                        'cp.id'
                    );

                    /*
                     * Important:
                     * use date comparisons rather
                     * than same-day whereBetween.
                     */
                    $join->whereDate(
                        'a.appointment_date',
                        '>=',
                        $filters['from']
                    );

                    $join->whereDate(
                        'a.appointment_date',
                        '<=',
                        $filters['to']
                    );

                    if ($filters['mode']) {
                        $join->where(
                            'a.mode',
                            $filters['mode']
                        );
                    }
                }
            )
            ->when(
                $filters[
                    'counsellor_profile_id'
                ],
                fn (
                    $query,
                    $counsellorId
                ) => $query->where(
                    'cp.id',
                    $counsellorId
                )
            )
            ->select([
                'cp.id',
                'cp.registration_number',
                'u.name',
            ])
            ->selectRaw(
                'COUNT(a.id) as total_appointments'
            )
            ->selectRaw(
                "SUM(
                    CASE
                        WHEN a.status = 'completed'
                        THEN 1
                        ELSE 0
                    END
                ) as completed_appointments"
            )
            ->selectRaw(
                "SUM(
                    CASE
                        WHEN a.status = 'no_show'
                        THEN 1
                        ELSE 0
                    END
                ) as no_show_appointments"
            )
            ->selectRaw(
                "SUM(
                    CASE
                        WHEN a.status = 'cancelled'
                        THEN 1
                        ELSE 0
                    END
                ) as cancelled_appointments"
            )
            ->groupBy([
                'cp.id',
                'cp.registration_number',
                'u.name',
            ])
            ->orderBy(
                'u.name'
            );

        return $query
            ->get()
            ->map(
                function (
                    object $row
                ): array {
                    $completed =
                        (int) (
                            $row
                                ->completed_appointments
                            ?? 0
                        );

                    $noShow =
                        (int) (
                            $row
                                ->no_show_appointments
                            ?? 0
                        );

                    $resolved =
                        $completed + $noShow;

                    return [
                        'id' => (int) $row->id,

                        'registration_number' => $row
                            ->registration_number,

                        'name' => $row->name,

                        'total_appointments' => (int) (
                            $row
                                ->total_appointments
                            ?? 0
                        ),

                        'completed_appointments' => $completed,

                        'no_show_appointments' => $noShow,

                        'cancelled_appointments' => (int) (
                            $row
                                ->cancelled_appointments
                            ?? 0
                        ),

                        'utilisation_rate' => $resolved > 0
                                ? round(
                                    (
                                        $completed
                                        / $resolved
                                    ) * 100,
                                    2
                                )
                                : 0.0,
                    ];
                }
            );
    }

    public function cancellationsByService(
        array $filters
    ): Collection {
        return DB::table(
            'appointments as a'
        )
            ->leftJoin(
                'counselling_services as cs',
                'cs.id',
                '=',
                'a.counselling_service_id'
            )
            ->where(
                'a.status',
                Appointment::STATUS_CANCELLED
            )
            ->whereDate(
                'a.appointment_date',
                '>=',
                $filters['from']
            )
            ->whereDate(
                'a.appointment_date',
                '<=',
                $filters['to']
            )
            ->when(
                $filters[
                    'counsellor_profile_id'
                ],
                fn (
                    $query,
                    $counsellorId
                ) => $query->where(
                    'a.counsellor_profile_id',
                    $counsellorId
                )
            )
            ->when(
                $filters['mode'],
                fn (
                    $query,
                    $mode
                ) => $query->where(
                    'a.mode',
                    $mode
                )
            )
            ->selectRaw(
                "COALESCE(
                    cs.name,
                    'Legacy / unassigned service'
                ) as service_name"
            )
            ->selectRaw(
                'COUNT(a.id) as cancellation_count'
            )
            ->groupBy(
                'cs.id',
                'cs.name'
            )
            ->orderByDesc(
                'cancellation_count'
            )
            ->get()
            ->map(
                fn (
                    object $row
                ): array => [
                    'service_name' => $row->service_name,

                    'cancellation_count' => (int) (
                        $row
                            ->cancellation_count
                    ),
                ]
            );
    }

    public function counsellorOptions(): Collection
    {
        return CounsellorProfile::query()
            ->with(
                'user:id,name'
            )
            ->whereHas(
                'user',
                fn (
                    Builder $query
                ) => $query->where(
                    'is_active',
                    true
                )
            )
            ->orderBy(
                'registration_number'
            )
            ->get()
            ->map(
                fn (
                    CounsellorProfile $profile
                ): array => [
                    'id' => $profile->id,

                    'name' => $profile
                        ->user
                        ?->name
                        ?? 'Unknown counsellor',

                    'registration_number' => $profile
                        ->registration_number,
                ]
            )
            ->values();
    }

    private function appointmentQuery(
        array $filters,
        bool $includeStatusFilter = true
    ): Builder {
        return Appointment::query()
            ->whereDate(
                'appointment_date',
                '>=',
                $filters['from']
            )
            ->whereDate(
                'appointment_date',
                '<=',
                $filters['to']
            )
            ->when(
                $filters[
                    'counsellor_profile_id'
                ],
                fn (
                    Builder $query,
                    $counsellorId
                ) => $query->where(
                    'counsellor_profile_id',
                    $counsellorId
                )
            )
            ->when(
                $filters['mode'],
                fn (
                    Builder $query,
                    $mode
                ) => $query->where(
                    'mode',
                    $mode
                )
            )
            ->when(
                $includeStatusFilter
                    ? $filters['status']
                    : null,
                fn (
                    Builder $query,
                    $status
                ) => $query->where(
                    'status',
                    $status
                )
            );
    }

    private function countStatus(
        Builder $query,
        string $status
    ): int {
        return (clone $query)
            ->where(
                'status',
                $status
            )
            ->count();
    }

    private function formatAppointment(
        Appointment $appointment
    ): array {
        return [
            'uuid' => $appointment->uuid,

            'appointment_date' => $appointment
                ->appointment_date
                ?->format('Y-m-d'),

            'start_time' => $appointment
                ->start_time
                ?->format('H:i'),

            'end_time' => $appointment
                ->end_time
                ?->format('H:i'),

            'mode' => $appointment->mode,

            'status' => $appointment->status,

            'client_name' => $appointment
                ->clientProfile
                ?->user
                ?->name
                ?? 'Not available',

            'counsellor_name' => $appointment
                ->counsellorProfile
                ?->user
                ?->name
                ?? 'Not available',

            'service_name' => $appointment
                ->counsellingService
                ?->name
                ?? 'Legacy / unassigned',

            'fee_amount' => $appointment
                ->fee_amount,

            'fee_currency' => $appointment
                ->fee_currency,
        ];
    }
}
