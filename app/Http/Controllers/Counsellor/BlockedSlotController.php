<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\StoreCounsellorBlockedSlotRequest;
use App\Http\Requests\Availability\UpdateCounsellorBlockedSlotRequest;
use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockedSlotController extends Controller
{
    public function store(StoreCounsellorBlockedSlotRequest $request): RedirectResponse
    {
        $counsellorProfile = $this->ownedCounsellorProfile($request);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $counsellorProfile->id;
        $validated['is_full_day'] = $request->boolean('is_full_day', false);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        if ($validated['is_full_day']) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        CounsellorBlockedSlot::create($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Blocked slot created successfully.');
    }

    public function update(
        UpdateCounsellorBlockedSlotRequest $request,
        CounsellorBlockedSlot $blockedSlot
    ): RedirectResponse {
        $this->ensureOwnBlockedSlot($request, $blockedSlot);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $blockedSlot->counsellor_profile_id;
        $validated['is_full_day'] = $request->boolean('is_full_day', $blockedSlot->is_full_day);
        $validated['updated_by'] = $request->user()->id;

        if ($validated['is_full_day']) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $blockedSlot->update($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Blocked slot updated successfully.');
    }

    public function destroy(Request $request, CounsellorBlockedSlot $blockedSlot): RedirectResponse
    {
        $this->ensureOwnBlockedSlot($request, $blockedSlot);

        $blockedSlot->delete();

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Blocked slot deleted successfully.');
    }

    private function ownedCounsellorProfile(Request $request): CounsellorProfile
    {
        $profile = $request->user()?->counsellorProfile;

        abort_unless($profile instanceof CounsellorProfile, 403);

        return $profile;
    }

    private function ensureOwnBlockedSlot(Request $request, CounsellorBlockedSlot $blockedSlot): void
    {
        $profile = $this->ownedCounsellorProfile($request);

        abort_unless($blockedSlot->counsellor_profile_id === $profile->id, 404);
    }
}
