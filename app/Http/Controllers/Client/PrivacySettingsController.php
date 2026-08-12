<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientPrivacySettingsRequest;
use App\Models\ClientConsent;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PrivacySettingsController extends Controller
{
    public function show(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('view', $clientProfile);

        $clientProfile->load([
            'consents' => fn ($query) => $query
                ->latest('accepted_at')
                ->latest('id'),
        ]);

        return Inertia::render('Client/Privacy/Show', [
            'privacy' => $this->privacyPayload($clientProfile),
            'consents' => $clientProfile
                ->consents
                ->map(fn (ClientConsent $consent): array => [
                    'id' => $consent->id,
                    'consent_type' => $consent->consent_type,
                    'version' => $consent->version,
                    'accepted_at' => $consent->accepted_at?->toDayDateTimeString(),
                    'source' => $consent->metadata['source'] ?? null,
                ])
                ->values(),
        ]);
    }

    public function edit(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('update', $clientProfile);

        return Inertia::render('Client/Privacy/Edit', [
            'privacy' => $this->privacyPayload($clientProfile),
        ]);
    }

    public function update(
        UpdateClientPrivacySettingsRequest $request
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);
        $validated = $request->validated();

        DB::transaction(function () use (
            $request,
            $clientProfile,
            $validated
        ): void {
            $now = now();

            $previousCommunicationConsent = (bool) $clientProfile->communication_consent;
            $previousEmergencyPermission = (bool) $clientProfile->emergency_contact_permission;

            $clientProfile->update([
                'communication_consent' => (bool) $validated['communication_consent'],
                'communication_consent_at' => $this->consentTimestamp(
                    accepted: (bool) $validated['communication_consent'],
                    previousAccepted: $previousCommunicationConsent,
                    previousTimestamp: $clientProfile->communication_consent_at,
                    now: $now,
                ),
                'emergency_contact_permission' => (bool) $validated['emergency_contact_permission'],
                'emergency_contact_permission_at' => $this->consentTimestamp(
                    accepted: (bool) $validated['emergency_contact_permission'],
                    previousAccepted: $previousEmergencyPermission,
                    previousTimestamp: $clientProfile->emergency_contact_permission_at,
                    now: $now,
                ),
                'privacy_preferences' => [
                    'allow_email_updates' => (bool) $validated['allow_email_updates'],
                    'allow_sms_updates' => (bool) $validated['allow_sms_updates'],
                    'allow_whatsapp_updates' => (bool) $validated['allow_whatsapp_updates'],
                    'share_profile_with_assigned_counsellor' => (bool) $validated['share_profile_with_assigned_counsellor'],
                ],
                'updated_by' => $request->user()->id,
            ]);

            if (
                (bool) $validated['communication_consent']
                && ! $previousCommunicationConsent
            ) {
                $this->recordConsent(
                    request: $request,
                    clientProfile: $clientProfile,
                    consentType: ClientConsent::TYPE_COMMUNICATION,
                    version: 'module-05-initial',
                    acceptedAt: $now,
                );
            }

            if (
                (bool) $validated['emergency_contact_permission']
                && ! $previousEmergencyPermission
            ) {
                $this->recordConsent(
                    request: $request,
                    clientProfile: $clientProfile,
                    consentType: ClientConsent::TYPE_EMERGENCY_CONTACT,
                    version: 'module-05-initial',
                    acceptedAt: $now,
                );
            }

            $clientProfile->load([
                'user',
                'emergencyContacts',
            ]);

            $clientProfile->refreshCompletionStatus();
        });

        return to_route('client.privacy.show')
            ->with('success', 'Privacy and consent settings updated successfully.');
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

    private function privacyPayload(ClientProfile $clientProfile): array
    {
        $preferences = $clientProfile->privacy_preferences ?? [];

        return [
            'id' => $clientProfile->id,
            'terms_accepted_at' => $clientProfile->terms_accepted_at?->toDayDateTimeString(),
            'privacy_policy_accepted_at' => $clientProfile->privacy_policy_accepted_at?->toDayDateTimeString(),
            'communication_consent' => (bool) $clientProfile->communication_consent,
            'communication_consent_at' => $clientProfile->communication_consent_at?->toDayDateTimeString(),
            'emergency_contact_permission' => (bool) $clientProfile->emergency_contact_permission,
            'emergency_contact_permission_at' => $clientProfile->emergency_contact_permission_at?->toDayDateTimeString(),
            'allow_email_updates' => (bool) ($preferences['allow_email_updates'] ?? true),
            'allow_sms_updates' => (bool) ($preferences['allow_sms_updates'] ?? false),
            'allow_whatsapp_updates' => (bool) ($preferences['allow_whatsapp_updates'] ?? false),
            'share_profile_with_assigned_counsellor' => (bool) ($preferences['share_profile_with_assigned_counsellor'] ?? true),
            'status' => $clientProfile->status,
        ];
    }

    private function consentTimestamp(
        bool $accepted,
        bool $previousAccepted,
        mixed $previousTimestamp,
        mixed $now,
    ): mixed {
        if (! $accepted) {
            return null;
        }

        if ($previousAccepted && $previousTimestamp) {
            return $previousTimestamp;
        }

        return $now;
    }

    private function recordConsent(
        Request $request,
        ClientProfile $clientProfile,
        string $consentType,
        string $version,
        mixed $acceptedAt,
    ): void {
        $clientProfile->consents()->create([
            'consent_type' => $consentType,
            'version' => $version,
            'accepted_at' => $acceptedAt,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'source' => 'client_privacy_settings',
            ],
            'recorded_by' => $request->user()->id,
        ]);
    }
}
