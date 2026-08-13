<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\StoreCounsellorLeaveDayRequest;
use App\Http\Requests\Availability\UpdateCounsellorLeaveDayRequest;
use App\Models\CounsellorLeaveDay;
use App\Models\CounsellorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveDayController extends Controller
{
    public function store(StoreCounsellorLeaveDayRequest $request): RedirectResponse
    {
        $counsellorProfile = $this->ownedCounsellorProfile($request);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $counsellorProfile->id;
        $validated['is_full_day'] = $request->boolean('is_full_day', true);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        if ($validated['is_full_day']) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        CounsellorLeaveDay::create($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Leave day created successfully.');
    }

    public function update(
        UpdateCounsellorLeaveDayRequest $request,
        CounsellorLeaveDay $leaveDay
    ): RedirectResponse {
        $this->ensureOwnLeaveDay($request, $leaveDay);

        $validated = $request->validated();

        $validated['counsellor_profile_id'] = $leaveDay->counsellor_profile_id;
        $validated['is_full_day'] = $request->boolean('is_full_day', $leaveDay->is_full_day);
        $validated['updated_by'] = $request->user()->id;

        if ($validated['is_full_day']) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $leaveDay->update($validated);

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Leave day updated successfully.');
    }

    public function destroy(Request $request, CounsellorLeaveDay $leaveDay): RedirectResponse
    {
        $this->ensureOwnLeaveDay($request, $leaveDay);

        $leaveDay->delete();

        return redirect()
            ->route('counsellor.availability.index')
            ->with('success', 'Leave day deleted successfully.');
    }

    private function ownedCounsellorProfile(Request $request): CounsellorProfile
    {
        $profile = $request->user()?->counsellorProfile;

        abort_unless($profile instanceof CounsellorProfile, 403);

        return $profile;
    }

    private function ensureOwnLeaveDay(Request $request, CounsellorLeaveDay $leaveDay): void
    {
        $profile = $this->ownedCounsellorProfile($request);

        abort_unless($leaveDay->counsellor_profile_id === $profile->id, 404);
    }
}
