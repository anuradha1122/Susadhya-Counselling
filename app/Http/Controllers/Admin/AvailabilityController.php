<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:120',
            ],
            'day_of_week' => [
                'nullable',
                'integer',
                'between:0,6',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        $dayOfWeek = $filters['day_of_week'] ?? null;
        $status = $filters['status'] ?? null;

        $counsellors = CounsellorProfile::query()
            ->with([
                'user:id,name,email,phone,is_active',
                'availabilityRules' => function ($query) use ($dayOfWeek, $status): void {
                    $query
                        ->with(['activeBreaks'])
                        ->when($dayOfWeek !== null && $dayOfWeek !== '', function (Builder $query) use ($dayOfWeek): void {
                            $query->where('day_of_week', (int) $dayOfWeek);
                        })
                        ->when($status === 'active', function (Builder $query): void {
                            $query->where('is_active', true);
                        })
                        ->when($status === 'inactive', function (Builder $query): void {
                            $query->where('is_active', false);
                        })
                        ->orderBy('day_of_week')
                        ->orderBy('start_time');
                },
                'blockedSlots' => function ($query): void {
                    $query
                        ->whereDate('blocked_date', '>=', now()->toDateString())
                        ->orderBy('blocked_date')
                        ->orderBy('start_time');
                },
                'leaveDays' => function ($query): void {
                    $query
                        ->whereDate('leave_date', '>=', now()->toDateString())
                        ->orderBy('leave_date')
                        ->orderBy('start_time');
                },
            ])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('user', function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($dayOfWeek !== null && $dayOfWeek !== '', function (Builder $query) use ($dayOfWeek): void {
                $query->whereHas('availabilityRules', function (Builder $query) use ($dayOfWeek): void {
                    $query->where('day_of_week', (int) $dayOfWeek);
                });
            })
            ->when($status === 'active', function (Builder $query): void {
                $query->whereHas('availabilityRules', function (Builder $query): void {
                    $query->where('is_active', true);
                });
            })
            ->when($status === 'inactive', function (Builder $query): void {
                $query->whereHas('availabilityRules', function (Builder $query): void {
                    $query->where('is_active', false);
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (CounsellorProfile $profile): array => $this->profilePayload($profile));

        return Inertia::render('Admin/Availability/Index', [
            'counsellors' => $counsellors,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'day_of_week' => $filters['day_of_week'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'options' => [
                'days' => collect(CounsellorAvailabilityRule::days())
                    ->map(fn (string $label, int $value): array => [
                        'value' => $value,
                        'label' => $label,
                    ])
                    ->values(),
                'statuses' => [
                    [
                        'value' => '',
                        'label' => 'All rules',
                    ],
                    [
                        'value' => 'active',
                        'label' => 'Active rules',
                    ],
                    [
                        'value' => 'inactive',
                        'label' => 'Inactive rules',
                    ],
                ],
            ],
        ]);
    }

    private function profilePayload(CounsellorProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'display_name' => $profile->user?->name ?? "Counsellor #{$profile->id}",
            'email' => $profile->user?->email,
            'phone' => $profile->user?->phone,
            'is_user_active' => (bool) $profile->user?->is_active,
            'rules_count' => $profile->availabilityRules->count(),
            'active_rules_count' => $profile->availabilityRules
                ->where('is_active', true)
                ->count(),
            'blocked_slots_count' => $profile->blockedSlots->count(),
            'leave_days_count' => $profile->leaveDays->count(),
            'rules' => $profile->availabilityRules
                ->map(fn ($rule): array => [
                    'id' => $rule->id,
                    'day_of_week' => $rule->day_of_week,
                    'day_name' => CounsellorAvailabilityRule::days()[$rule->day_of_week] ?? 'Unknown',
                    'start_time' => $this->formatTime($rule->start_time),
                    'end_time' => $this->formatTime($rule->end_time),
                    'mode' => $rule->mode,
                    'slot_duration_minutes' => $rule->slot_duration_minutes,
                    'buffer_minutes' => $rule->buffer_minutes,
                    'capacity_per_slot' => $rule->capacity_per_slot,
                    'timezone' => $rule->timezone,
                    'effective_from' => $this->formatDate($rule->effective_from),
                    'effective_until' => $this->formatDate($rule->effective_until),
                    'is_active' => $rule->is_active,
                    'notes' => $rule->notes,
                    'breaks' => $rule->activeBreaks
                        ->map(fn ($break): array => [
                            'id' => $break->id,
                            'title' => $break->title,
                            'start_time' => $this->formatTime($break->start_time),
                            'end_time' => $this->formatTime($break->end_time),
                            'is_active' => $break->is_active,
                        ])
                        ->values(),
                ])
                ->values(),
            'blocked_slots' => $profile->blockedSlots
                ->map(fn ($blockedSlot): array => [
                    'id' => $blockedSlot->id,
                    'blocked_date' => $this->formatDate($blockedSlot->blocked_date),
                    'start_time' => $this->formatTime($blockedSlot->start_time),
                    'end_time' => $this->formatTime($blockedSlot->end_time),
                    'is_full_day' => $blockedSlot->is_full_day,
                    'reason' => $blockedSlot->reason,
                    'notes' => $blockedSlot->notes,
                ])
                ->values(),
            'leave_days' => $profile->leaveDays
                ->map(fn ($leaveDay): array => [
                    'id' => $leaveDay->id,
                    'leave_date' => $this->formatDate($leaveDay->leave_date),
                    'start_time' => $this->formatTime($leaveDay->start_time),
                    'end_time' => $this->formatTime($leaveDay->end_time),
                    'is_full_day' => $leaveDay->is_full_day,
                    'reason' => $leaveDay->reason,
                    'notes' => $leaveDay->notes,
                ])
                ->values(),
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (method_exists($value, 'toDateString')) {
            return $value->toDateString();
        }

        return (string) $value;
    }

    private function formatTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }
}
