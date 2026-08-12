<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreClientRegistrationRequest;
use App\Models\ClientConsent;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'consentLabels' => [
                'terms' => 'I accept the terms of service.',
                'privacy' => 'I accept the privacy policy.',
                'communication' => 'I agree to receive appointment and service-related communication.',
            ],
            'languageOptions' => $this->languageOptions(),
        ]);
    }

    public function store(
        StoreClientRegistrationRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $user = DB::transaction(function () use (
            $request,
            $validated
        ): User {
            $user = User::create([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
                'password_changed_at' => now(),
            ]);

            $user->assignRole('client');

            $now = now();

            $clientProfile = ClientProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'preferred_name' => $validated['preferred_name'] ?? null,
                'preferred_contact_method' => 'email',
                'preferred_language' => $validated['preferred_language'],
                'terms_accepted_at' => $now,
                'privacy_policy_accepted_at' => $now,
                'communication_consent' => (bool) ($validated['communication_consent'] ?? false),
                'communication_consent_at' => ($validated['communication_consent'] ?? false)
                    ? $now
                    : null,
                'emergency_contact_permission' => false,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $clientProfile->preference()->create([
                'preferred_counselling_mode' => 'no_preference',
                'preferred_counsellor_gender' => 'no_preference',
                'preferred_language' => $validated['preferred_language'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->recordConsent(
                request: $request,
                clientProfile: $clientProfile,
                user: $user,
                consentType: ClientConsent::TYPE_TERMS,
                version: ClientConsent::CURRENT_TERMS_VERSION,
                acceptedAt: $now,
            );

            $this->recordConsent(
                request: $request,
                clientProfile: $clientProfile,
                user: $user,
                consentType: ClientConsent::TYPE_PRIVACY_POLICY,
                version: ClientConsent::CURRENT_PRIVACY_POLICY_VERSION,
                acceptedAt: $now,
            );

            if ($validated['communication_consent'] ?? false) {
                $this->recordConsent(
                    request: $request,
                    clientProfile: $clientProfile,
                    user: $user,
                    consentType: ClientConsent::TYPE_COMMUNICATION,
                    version: 'module-05-initial',
                    acceptedAt: $now,
                );
            }

            $clientProfile->refreshCompletionStatus();

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function recordConsent(
        StoreClientRegistrationRequest $request,
        ClientProfile $clientProfile,
        User $user,
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
                'source' => 'public_registration',
            ],
            'recorded_by' => $user->id,
        ]);
    }

    private function languageOptions(): array
    {
        return [
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
        ];
    }
}
