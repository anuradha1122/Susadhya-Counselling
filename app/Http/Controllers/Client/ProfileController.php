<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientProfileRequest;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('view', $clientProfile);

        $clientProfile->load([
            'user:id,name,email,phone,is_active',
            'emergencyContacts',
            'preference',
        ]);

        return Inertia::render('Client/Profile/Show', [
            'clientProfile' => $this->profilePayload($clientProfile),
        ]);
    }

    public function edit(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('update', $clientProfile);

        $clientProfile->load([
            'user:id,name,email,phone,is_active',
            'emergencyContacts',
            'preference',
        ]);

        return Inertia::render('Client/Profile/Edit', [
            'clientProfile' => $this->profilePayload($clientProfile),
            'options' => $this->options(),
        ]);
    }

    public function update(
        UpdateClientProfileRequest $request
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);
        $validated = $request->validated();

        DB::transaction(function () use (
            $request,
            $clientProfile,
            $validated
        ): void {
            $request->user()->update([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'phone' => $validated['phone'],
            ]);

            $clientProfile->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'preferred_name' => $validated['preferred_name'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'pronouns' => $validated['pronouns'] ?? null,
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'address_line_1' => $validated['address_line_1'] ?? null,
                'address_line_2' => $validated['address_line_2'] ?? null,
                'city' => $validated['city'] ?? null,
                'district' => $validated['district'] ?? null,
                'province' => $validated['province'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'preferred_language' => $validated['preferred_language'] ?? null,
                'preferred_contact_method' => $validated['preferred_contact_method'],
                'occupation' => $validated['occupation'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $clientProfile->load([
                'user',
                'emergencyContacts',
            ]);

            $clientProfile->refreshCompletionStatus();
        });

        return to_route('client.profile.show')
            ->with('success', 'Client profile updated successfully.');
    }

    private function clientProfileFor(Request $request): ClientProfile
    {
        $clientProfile = $request->user()
            ->clientProfile()
            ->first();

        abort_if(
            ! $clientProfile,
            404,
            'Client profile has not been created for this account.'
        );

        return $clientProfile;
    }

    private function profilePayload(ClientProfile $clientProfile): array
    {
        $missingFields = $clientProfile->completionMissingFields();

        return [
            'id' => $clientProfile->id,
            'first_name' => $clientProfile->first_name,
            'last_name' => $clientProfile->last_name,
            'preferred_name' => $clientProfile->preferred_name,
            'date_of_birth' => $clientProfile->date_of_birth?->format('Y-m-d'),
            'gender' => $clientProfile->gender,
            'pronouns' => $clientProfile->pronouns,
            'phone' => $clientProfile->user?->phone,
            'alternate_phone' => $clientProfile->alternate_phone,
            'address_line_1' => $clientProfile->address_line_1,
            'address_line_2' => $clientProfile->address_line_2,
            'city' => $clientProfile->city,
            'district' => $clientProfile->district,
            'province' => $clientProfile->province,
            'postal_code' => $clientProfile->postal_code,
            'preferred_language' => $clientProfile->preferred_language,
            'preferred_contact_method' => $clientProfile->preferred_contact_method,
            'occupation' => $clientProfile->occupation,
            'marital_status' => $clientProfile->marital_status,
            'profile_completed_at' => $clientProfile->profile_completed_at?->toISOString(),
            'terms_accepted_at' => $clientProfile->terms_accepted_at?->toISOString(),
            'privacy_policy_accepted_at' => $clientProfile->privacy_policy_accepted_at?->toISOString(),
            'communication_consent' => (bool) $clientProfile->communication_consent,
            'communication_consent_at' => $clientProfile->communication_consent_at?->toISOString(),
            'emergency_contact_permission' => (bool) $clientProfile->emergency_contact_permission,
            'emergency_contact_permission_at' => $clientProfile->emergency_contact_permission_at?->toISOString(),
            'status' => $clientProfile->status,
            'missing_fields' => $missingFields,
            'is_complete' => $missingFields === [],
            'user' => [
                'id' => $clientProfile->user?->id,
                'name' => $clientProfile->user?->name,
                'email' => $clientProfile->user?->email,
                'phone' => $clientProfile->user?->phone,
                'is_active' => (bool) $clientProfile->user?->is_active,
            ],
            'emergency_contacts_count' => $clientProfile->emergencyContacts->count(),
            'has_preferences' => $clientProfile->preference !== null,
        ];
    }

    private function options(): array
    {
        return [

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
            'genders' => [
                [
                    'value' => '',
                    'label' => 'Prefer not to answer now',
                ],
                [
                    'value' => 'male',
                    'label' => 'Male',
                ],
                [
                    'value' => 'female',
                    'label' => 'Female',
                ],
                [
                    'value' => 'non_binary',
                    'label' => 'Non-binary',
                ],
                [
                    'value' => 'other',
                    'label' => 'Other',
                ],
                [
                    'value' => 'prefer_not_to_say',
                    'label' => 'Prefer not to say',
                ],
            ],
            'contactMethods' => [
                [
                    'value' => 'email',
                    'label' => 'Email',
                ],
                [
                    'value' => 'phone',
                    'label' => 'Phone',
                ],
                [
                    'value' => 'sms',
                    'label' => 'SMS',
                ],
                [
                    'value' => 'whatsapp',
                    'label' => 'WhatsApp',
                ],
                [
                    'value' => 'no_preference',
                    'label' => 'No preference',
                ],
            ],
            'maritalStatuses' => [
                [
                    'value' => '',
                    'label' => 'Prefer not to answer now',
                ],
                [
                    'value' => 'single',
                    'label' => 'Single',
                ],
                [
                    'value' => 'married',
                    'label' => 'Married',
                ],
                [
                    'value' => 'separated',
                    'label' => 'Separated',
                ],
                [
                    'value' => 'divorced',
                    'label' => 'Divorced',
                ],
                [
                    'value' => 'widowed',
                    'label' => 'Widowed',
                ],
                [
                    'value' => 'other',
                    'label' => 'Other',
                ],
                [
                    'value' => 'prefer_not_to_say',
                    'label' => 'Prefer not to say',
                ],
            ],
        ];
    }
}
