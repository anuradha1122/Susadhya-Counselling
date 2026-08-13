<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\StoreCounsellorAvailabilityRuleRequest;
use App\Http\Requests\Availability\UpdateCounsellorAvailabilityRuleRequest;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function index(Request $request): Response
    {
        $counsellorProfile = $this->ownedCounsellorProfile($request);

        $rules = $counsellorProfile
            ->availabilityRules()
            ->with(['activeBreaks'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(fn (CounsellorAvailabilityRule $rule): array => $this->rulePayload($rule))
            ->values();

        $blockedSlots = $counsellorProfile
            ->blockedSlots()
            ->whereDate('blocked_date', '>=', now()->toDateString())
            ->orderBy('blocked_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($blockedSlot): array => [
                'id' => $blockedSlot->id,
                'blocked_date' => $blockedSlot->blocked_date?->toDateString(),
                'start_time' => $blockedSlot->start_time?->format('H:i'),
                'end_time' => $blockedSlot->end_time?->format('H:i'),
                'is_full_day' => $blockedSlot->is_full_day,
                'reason' => $blockedSlot->reason,
                'notes' => $blockedSlot->notes,
                'created_at' => $blockedSlot->created_at?->toDateTimeString(),
            ])
            ->values();

        $leaveDays = $counsellorProfile
            ->leaveDays()
            ->whereDate('leave_date', '>=', now()->toDateString())
            ->orderBy('leave_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($leaveDay): array => [
                'id' => $leaveDay->id,
                'leave_date' => $leaveDay->leave_date?->toDateString(),
                'start_time' => $leaveDay->start_time?->format('H:i'),
                'end_time' => $leaveDay->end_time?->format('H:i'),
                'is_full_day' => $leaveDay->is_full_day,
                'reason' => $leaveDay->reason,
                'notes' => $leaveDay->notes,
                'created_at' => $leaveDay->created_at?->toDateTimeString(),
            ])
            ->values();

        return Inertia::render('Counsellor/Availability/Index', [
            'counsellorProfile' => [
                'id' => $counsellorProfile->id,
                'display_name' => $counsellorProfile->display_name
                    ?? $counsellorProfile->user?->name,
            ],
            'rules' => $rules,
            'blockedSlots' => $blockedSlots,
            'leaveDays' => $leaveDays,
            'options' => [
                'days' => collect(CounsellorAvailabilityRule::days())
                    ->map(fn (string $label, int $value): array => [
                        'value' => $value,
                        'label' => $label,
                    ])
                    ->values(),
                'modes' => [
                    [
                        'value' => CounsellorAvailabilityRule::MODE_ONLINE,
                        'label' => 'Online',
                    ],
                    [
                        'value' => CounsellorAvailabilityRule::MODE_IN_PERSON,
                        'label' => 'In person',
                    ],
                    [
                        'value' => CounsellorAvailabilityRule::MODE_BOTH,
                        'label' => 'Online and in person',
                    ],
                ],
                'slotDurations' => [
                    15,
                    30,
                    45,
                    60,
                    90,
                    120,
                ],
                'timezones' => [
                    [
                        'value' => 'Asia/Colombo',
                        'label' => 'Sri Lanka - Asia/Colombo',
                    ],
                ],
            ],
        ]);
    }

    public function store(StoreCounsellorAvailabilityRuleRequest $request): RedirectResponse
    {
        $counsellorProfile = $this->ownedCounsellorProfile($request);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $counsellorProfile->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        CounsellorAvailabilityRule::create($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability rule created successfully.');
    }

    public function update(
        UpdateCounsellorAvailabilityRuleRequest $request,
        CounsellorAvailabilityRule $availabilityRule
    ): RedirectResponse {
        $this->ensureOwnRule($request, $availabilityRule);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $availabilityRule->counsellor_profile_id;
        $validated['is_active'] = $request->boolean('is_active', $availabilityRule->is_active);
        $validated['updated_by'] = $request->user()->id;

        $availabilityRule->update($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability rule updated successfully.');
    }

    public function destroy(Request $request, CounsellorAvailabilityRule $availabilityRule): RedirectResponse
    {
        $this->ensureOwnRule($request, $availabilityRule);

        $availabilityRule->delete();

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability rule deleted successfully.');
    }

    private function ownedCounsellorProfile(Request $request): CounsellorProfile
    {
        $profile = $request->user()?->counsellorProfile;

        abort_unless($profile instanceof CounsellorProfile, 403);

        return $profile;
    }

    private function ensureOwnRule(Request $request, CounsellorAvailabilityRule $availabilityRule): void
    {
        $profile = $this->ownedCounsellorProfile($request);

        abort_unless($availabilityRule->counsellor_profile_id === $profile->id, 404);
    }

    private function rulePayload(CounsellorAvailabilityRule $rule): array
    {
        return [
            'id' => $rule->id,
            'day_of_week' => $rule->day_of_week,
            'day_name' => CounsellorAvailabilityRule::days()[$rule->day_of_week] ?? 'Unknown',
            'start_time' => $rule->start_time?->format('H:i'),
            'end_time' => $rule->end_time?->format('H:i'),
            'mode' => $rule->mode,
            'slot_duration_minutes' => $rule->slot_duration_minutes,
            'buffer_minutes' => $rule->buffer_minutes,
            'capacity_per_slot' => $rule->capacity_per_slot,
            'timezone' => $rule->timezone,
            'effective_from' => $rule->effective_from?->toDateString(),
            'effective_until' => $rule->effective_until?->toDateString(),
            'is_active' => $rule->is_active,
            'notes' => $rule->notes,
            'breaks' => $rule->activeBreaks
                ->map(fn ($break): array => [
                    'id' => $break->id,
                    'title' => $break->title,
                    'start_time' => $break->start_time?->format('H:i'),
                    'end_time' => $break->end_time?->format('H:i'),
                    'is_active' => $break->is_active,
                ])
                ->values(),
            'created_at' => $rule->created_at?->toDateTimeString(),
        ];
    }
}
