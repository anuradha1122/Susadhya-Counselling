<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientPreferenceRequest;
use App\Models\ClientPreference;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PreferenceController extends Controller
{
    public function show(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('view', $clientProfile);

        $preference = $this->preferenceFor($clientProfile);

        return Inertia::render('Client/Preferences/Show', [
            'preference' => $this->preferencePayload($preference),
            'clientProfile' => [
                'id' => $clientProfile->id,
                'preferred_language' => $clientProfile->preferred_language,
                'is_complete' => $clientProfile->completionMissingFields() === [],
                'missing_fields' => $clientProfile->completionMissingFields(),
            ],
        ]);
    }

    public function edit(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('update', $clientProfile);

        $preference = $this->preferenceFor($clientProfile);

        return Inertia::render('Client/Preferences/Edit', [
            'preference' => $this->preferencePayload($preference),
            'options' => $this->options(),
        ]);
    }

    public function update(
        UpdateClientPreferenceRequest $request
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);
        $validated = $request->validated();

        $preference = $this->preferenceFor($clientProfile);

        $preference->update([
            'preferred_counselling_mode' => $validated['preferred_counselling_mode'],
            'preferred_counsellor_gender' => $validated['preferred_counsellor_gender'],
            'preferred_language' => $validated['preferred_language'],
            'general_availability_notes' => $validated['general_availability_notes'] ?? null,
            'accessibility_requirements' => $validated['accessibility_requirements'] ?? null,
            'additional_preferences' => $validated['additional_preferences'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return to_route('client.preferences.show')
            ->with('success', 'Counselling preferences updated successfully.');
    }

    private function clientProfileFor(Request $request): ClientProfile
    {
        $clientProfile = $request
            ->user()
            ->clientProfile()
            ->first();

        abort_if(
            ! $clientProfile,
            404,
            'Client profile has not been created for this account.'
        );

        return $clientProfile;
    }

    private function preferenceFor(
        ClientProfile $clientProfile
    ): ClientPreference {
        return $clientProfile->preference()->firstOrCreate(
            [
                'client_profile_id' => $clientProfile->id,
            ],
            [
                'preferred_counselling_mode' => 'no_preference',
                'preferred_counsellor_gender' => 'no_preference',
                'preferred_language' => $clientProfile->preferred_language,
                'created_by' => $clientProfile->user_id,
                'updated_by' => $clientProfile->user_id,
            ]
        );
    }

    private function preferencePayload(
        ClientPreference $preference
    ): array {
        return [
            'id' => $preference->id,
            'preferred_counselling_mode' => $preference->preferred_counselling_mode,
            'preferred_counsellor_gender' => $preference->preferred_counsellor_gender,
            'preferred_language' => $preference->preferred_language,
            'general_availability_notes' => $preference->general_availability_notes,
            'accessibility_requirements' => $preference->accessibility_requirements,
            'additional_preferences' => $preference->additional_preferences,
            'created_at' => $preference->created_at?->format('Y-m-d'),
            'updated_at' => $preference->updated_at?->format('Y-m-d'),
        ];
    }

    private function options(): array
    {
        return [
            'counsellingModes' => [
                [
                    'value' => 'no_preference',
                    'label' => 'No preference',
                ],
                [
                    'value' => 'online',
                    'label' => 'Online',
                ],
                [
                    'value' => 'in_person',
                    'label' => 'In person',
                ],
            ],
            'counsellorGenders' => [
                [
                    'value' => 'no_preference',
                    'label' => 'No preference',
                ],
                [
                    'value' => 'male',
                    'label' => 'Male',
                ],
                [
                    'value' => 'female',
                    'label' => 'Female',
                ],
            ],
            'preferredLanguages' => [
                [
                    'value' => '',
                    'label' => 'Select preferred language',
                ],
                [
                    'value' => 'sinhala',
                    'label' => 'Sinhala',
                ],
                [
                    'value' => 'tamil',
                    'label' => 'Tamil',
                ],
                [
                    'value' => 'english',
                    'label' => 'English',
                ],
                [
                    'value' => 'no_preference',
                    'label' => 'No preference',
                ],
                [
                    'value' => 'other',
                    'label' => 'Other',
                ],
            ],
        ];
    }
}
