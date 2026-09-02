<?php

namespace App\Services\Appointments;

use App\Models\ClientProfile;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AppointmentSlotService
{
    public function __construct(
        private readonly AppointmentConflictService $conflictService = new AppointmentConflictService
    ) {}

    public function availableSlotsForDate(
        CounsellorProfile $counsellorProfile,
        string $date,
        ?ClientProfile $clientProfile = null,
        ?string $mode = null
    ): Collection {
        $targetDate = CarbonImmutable::parse($date);
        $dayOfWeek = (int) $targetDate->dayOfWeek;

        $rules = $counsellorProfile
            ->availabilityRules()
            ->with(['activeBreaks'])
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($targetDate): void {
                $query
                    ->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', $targetDate->toDateString());
            })
            ->where(function ($query) use ($targetDate): void {
                $query
                    ->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $targetDate->toDateString());
            })
            ->when($mode, function ($query, string $mode): void {
                $query->where(function ($query) use ($mode): void {
                    $query
                        ->where('mode', $mode)
                        ->orWhere('mode', CounsellorAvailabilityRule::MODE_BOTH);
                });
            })
            ->orderBy('start_time')
            ->get();

        return $rules
            ->flatMap(function (CounsellorAvailabilityRule $rule) use ($targetDate, $clientProfile): Collection {
                return $this->slotsForRule(
                    rule: $rule,
                    targetDate: $targetDate,
                    clientProfile: $clientProfile
                );
            })
            ->values();
    }

    public function isSlotAvailable(
        CounsellorProfile $counsellorProfile,
        string $date,
        string $startTime,
        string $endTime,
        ?ClientProfile $clientProfile = null,
        ?string $mode = null,
        ?int $ignoreAppointmentId = null
    ): bool {
        $slots = $this->availableSlotsForDate(
            counsellorProfile: $counsellorProfile,
            date: $date,
            clientProfile: $clientProfile,
            mode: $mode
        );

        return $slots->contains(function (array $slot) use (
            $startTime,
            $endTime,
            $ignoreAppointmentId,
            $clientProfile,
            $counsellorProfile,
            $date
        ): bool {
            if ($slot['start_time'] !== $startTime || $slot['end_time'] !== $endTime) {
                return false;
            }

            if (! $clientProfile) {
                return true;
            }

            return ! $this->conflictService->hasAnyConflict(
                clientProfile: $clientProfile,
                counsellorProfile: $counsellorProfile,
                date: $date,
                startTime: $startTime,
                endTime: $endTime,
                ignoreAppointmentId: $ignoreAppointmentId
            );
        });
    }

    private function slotsForRule(
        CounsellorAvailabilityRule $rule,
        CarbonImmutable $targetDate,
        ?ClientProfile $clientProfile = null
    ): Collection {
        $date = $targetDate->toDateString();

        if ($this->isFullDayLeave($rule->counsellorProfile, $date)) {
            return collect();
        }

        if ($this->isFullDayBlocked($rule->counsellorProfile, $date)) {
            return collect();
        }

        $ruleStart = $this->combineDateAndTime($targetDate, $rule->start_time);
        $ruleEnd = $this->combineDateAndTime($targetDate, $rule->end_time);

        $slotLength = max(1, (int) $rule->slot_duration_minutes);
        $buffer = max(0, (int) $rule->buffer_minutes);
        $stepMinutes = $slotLength + $buffer;

        $latestStart = $ruleEnd->subMinutes($slotLength);

        if ($latestStart->lt($ruleStart)) {
            return collect();
        }

        return collect(CarbonPeriod::create($ruleStart, "{$stepMinutes} minutes", $latestStart))
            ->map(function (CarbonInterface $periodDate) use ($rule, $targetDate, $slotLength, $clientProfile): array {
                $slotStart = CarbonImmutable::instance($periodDate);
                $slotEnd = $slotStart->addMinutes($slotLength);
                $date = $targetDate->toDateString();

                return [
                    'date' => $date,
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'mode' => $rule->mode,
                    'timezone' => $rule->timezone,
                    'capacity_per_slot' => $rule->capacity_per_slot,
                    'available' => $this->slotPassesAvailabilityChecks(
                        rule: $rule,
                        date: $date,
                        startTime: $slotStart->format('H:i'),
                        endTime: $slotEnd->format('H:i'),
                        clientProfile: $clientProfile
                    ),
                ];
            })
            ->filter(fn (array $slot): bool => $slot['available'])
            ->map(fn (array $slot): array => collect($slot)->except('available')->all())
            ->values();
    }

    private function slotPassesAvailabilityChecks(
        CounsellorAvailabilityRule $rule,
        string $date,
        string $startTime,
        string $endTime,
        ?ClientProfile $clientProfile = null
    ): bool {
        $counsellorProfile = $rule->counsellorProfile;

        if ($this->overlapsBreak($rule, $startTime, $endTime)) {
            return false;
        }

        if ($this->overlapsBlockedSlot($counsellorProfile, $date, $startTime, $endTime)) {
            return false;
        }

        if ($this->overlapsLeaveDay($counsellorProfile, $date, $startTime, $endTime)) {
            return false;
        }

        if ($this->conflictService->counsellorHasConflict(
            counsellorProfile: $counsellorProfile,
            date: $date,
            startTime: $startTime,
            endTime: $endTime
        )) {
            return false;
        }

        if ($clientProfile && $this->conflictService->clientHasConflict(
            clientProfile: $clientProfile,
            date: $date,
            startTime: $startTime,
            endTime: $endTime
        )) {
            return false;
        }

        return true;
    }

    private function overlapsBreak(
        CounsellorAvailabilityRule $rule,
        string $startTime,
        string $endTime
    ): bool {
        return $rule->activeBreaks
            ->contains(fn ($break): bool => $this->timeRangesOverlap(
                $startTime,
                $endTime,
                $this->formatTime($break->start_time),
                $this->formatTime($break->end_time)
            ));
    }

    private function overlapsBlockedSlot(
        CounsellorProfile $counsellorProfile,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        return $counsellorProfile
            ->blockedSlots()
            ->whereDate('blocked_date', $date)
            ->where(function ($query) use ($startTime, $endTime): void {
                $query
                    ->where('is_full_day', true)
                    ->orWhere(function ($query) use ($startTime, $endTime): void {
                        $query
                            ->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
            })
            ->exists();
    }

    private function overlapsLeaveDay(
        CounsellorProfile $counsellorProfile,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        return $counsellorProfile
            ->leaveDays()
            ->whereDate('leave_date', $date)
            ->where(function ($query) use ($startTime, $endTime): void {
                $query
                    ->where('is_full_day', true)
                    ->orWhere(function ($query) use ($startTime, $endTime): void {
                        $query
                            ->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
            })
            ->exists();
    }

    private function isFullDayBlocked(CounsellorProfile $counsellorProfile, string $date): bool
    {
        return $counsellorProfile
            ->blockedSlots()
            ->whereDate('blocked_date', $date)
            ->where('is_full_day', true)
            ->exists();
    }

    private function isFullDayLeave(CounsellorProfile $counsellorProfile, string $date): bool
    {
        return $counsellorProfile
            ->leaveDays()
            ->whereDate('leave_date', $date)
            ->where('is_full_day', true)
            ->exists();
    }

    private function timeRangesOverlap(
        string $firstStart,
        string $firstEnd,
        string $secondStart,
        string $secondEnd
    ): bool {
        return $firstStart < $secondEnd && $firstEnd > $secondStart;
    }

    private function combineDateAndTime(CarbonImmutable $date, mixed $time): CarbonImmutable
    {
        return CarbonImmutable::parse($date->toDateString().' '.$this->formatTime($time));
    }

    private function formatTime(mixed $value): string
    {
        if (method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }
}
