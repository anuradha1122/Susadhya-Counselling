<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GroupCounsellingEnrollment;
use App\Models\GroupCounsellingProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupCounsellingController extends Controller
{
    public function index(Request $request): Response
    {
        $clientProfile = $request->user()->clientProfile;

        $programs = GroupCounsellingProgram::query()
            ->with(['service', 'leadCounsellorProfile.user'])
            ->withCount('enrollments')
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        $enrolledProgramUuids = $clientProfile
            ? GroupCounsellingEnrollment::query()
                ->with('program:id,uuid')
                ->where('client_profile_id', $clientProfile->id)
                ->get()
                ->map(fn (GroupCounsellingEnrollment $enrollment) => $enrollment->program?->uuid)
                ->filter()
                ->values()
            : [];

        return Inertia::render('Client/AdvancedProduct/Groups', [
            'programs' => $programs,
            'enrolledProgramUuids' => $enrolledProgramUuids,
        ]);
    }

    public function enroll(Request $request, GroupCounsellingProgram $program): RedirectResponse
    {
        $clientProfile = $request->user()->clientProfile;

        abort_unless($clientProfile, 403);
        abort_unless($program->is_active, 404);

        $approvedCount = $program->enrollments()
            ->where('status', 'approved')
            ->count();

        $status = $program->requires_approval
            ? 'pending'
            : ($approvedCount >= $program->capacity ? 'waitlisted' : 'approved');

        GroupCounsellingEnrollment::firstOrCreate(
            [
                'program_id' => $program->id,
                'client_profile_id' => $clientProfile->id,
            ],
            [
                'status' => $status,
                'enrolled_at' => now(),
                'client_note' => $request->string('client_note')->toString(),
            ]
        );

        return back()->with('success', 'Group counselling enrollment submitted.');
    }
}
