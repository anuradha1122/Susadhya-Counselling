<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $clientProfile = $request->user()
            ->clientProfile()
            ->with([
                'emergencyContacts',
                'preference',
            ])
            ->first();

        return Inertia::render('Client/Dashboard', [
            'clientProfile' => $clientProfile
                ? [
                    'id' => $clientProfile->id,
                    'first_name' => $clientProfile->first_name,
                    'last_name' => $clientProfile->last_name,
                    'preferred_name' => $clientProfile->preferred_name,
                    'status' => $clientProfile->status,
                    'profile_completed_at' => $clientProfile->profile_completed_at?->toISOString(),
                    'missing_fields' => $clientProfile->completionMissingFields(),
                    'emergency_contacts_count' => $clientProfile->emergencyContacts->count(),
                    'has_preferences' => $clientProfile->preference !== null,
                ]
                : null,
        ]);
    }
}
