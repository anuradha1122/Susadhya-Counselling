<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\StoreCounsellorAvailabilityBreakRequest;
use App\Http\Requests\Availability\UpdateCounsellorAvailabilityBreakRequest;
use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvailabilityBreakController extends Controller
{
    public function store(
        StoreCounsellorAvailabilityBreakRequest $request,
        CounsellorAvailabilityRule $availabilityRule
    ): RedirectResponse {
        $this->ensureOwnRule($request, $availabilityRule);

        $validated = $request->validated();

        $validated['counsellor_availability_rule_id'] = $availabilityRule->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        CounsellorAvailabilityBreak::create($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability break created successfully.');
    }

    public function update(
        UpdateCounsellorAvailabilityBreakRequest $request,
        CounsellorAvailabilityBreak $availabilityBreak
    ): RedirectResponse {
        $this->ensureOwnBreak($request, $availabilityBreak);

        $validated = $request->validated();

        $validated['counsellor_availability_rule_id'] = $availabilityBreak->counsellor_availability_rule_id;
        $validated['is_active'] = $request->boolean('is_active', $availabilityBreak->is_active);
        $validated['updated_by'] = $request->user()->id;

        $availabilityBreak->update($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability break updated successfully.');
    }

    public function destroy(Request $request, CounsellorAvailabilityBreak $availabilityBreak): RedirectResponse
    {
        $this->ensureOwnBreak($request, $availabilityBreak);

        $availabilityBreak->delete();

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Availability break deleted successfully.');
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

    private function ensureOwnBreak(Request $request, CounsellorAvailabilityBreak $availabilityBreak): void
    {
        $availabilityBreak->loadMissing('availabilityRule');

        $profile = $this->ownedCounsellorProfile($request);

        abort_unless(
            $availabilityBreak->availabilityRule?->counsellor_profile_id === $profile->id,
            404
        );
    }
}
