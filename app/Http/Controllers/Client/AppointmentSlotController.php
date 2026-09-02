<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AppointmentSlotRequest;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Services\Appointments\AppointmentSlotService;
use Illuminate\Http\JsonResponse;

class AppointmentSlotController extends Controller
{
    public function index(
        AppointmentSlotRequest $request,
        CounsellorProfile $counsellor,
        AppointmentSlotService $slotService
    ): JsonResponse {
        abort_unless($counsellor->status === 'active', 404);
        abort_unless((bool) $counsellor->user?->is_active, 404);

        $validated = $request->validated();

        $clientProfile = ClientProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $slots = $slotService->availableSlotsForDate(
            counsellorProfile: $counsellor,
            date: $validated['appointment_date'],
            clientProfile: $clientProfile,
            mode: $validated['mode']
        );

        return response()->json([
            'counsellor_profile_id' => $counsellor->id,
            'appointment_date' => $validated['appointment_date'],
            'mode' => $validated['mode'],
            'slots' => $slots->values(),
        ]);
    }
}
