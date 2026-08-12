<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientConsent;
use App\Models\ClientEmergencyContact;
use App\Models\ClientPreference;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ClientProfile::class);

        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:120',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                    'archived',
                ]),
            ],
            'completion' => [
                'nullable',
                Rule::in([
                    'complete',
                    'incomplete',
                ]),
            ],
        ]);

        $clients = ClientProfile::query()
            ->with([
                'user:id,name,email,phone,is_active',
            ])
            ->withCount([
                'emergencyContacts',
                'consents',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('preferred_name', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('district', 'like', "%{$search}%")
                            ->orWhereHas(
                                'user',
                                function ($query) use ($search): void {
                                    $query
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%");
                                }
                            );
                    });
                }
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                ($filters['completion'] ?? null) === 'complete',
                fn ($query) => $query->whereNotNull('profile_completed_at')
            )
            ->when(
                ($filters['completion'] ?? null) === 'incomplete',
                fn ($query) => $query->whereNull('profile_completed_at')
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(
                fn (ClientProfile $clientProfile): array => $this->indexPayload(
                    $clientProfile
                )
            );

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'completion' => $filters['completion'] ?? '',
            ],
            'statusOptions' => $this->statusOptions(),
            'completionOptions' => $this->completionOptions(),
        ]);
    }

    public function show(ClientProfile $client): Response
    {
        Gate::authorize('view', $client);

        $client->load([
            'user:id,name,email,phone,is_active,email_verified_at,last_login_at,created_at',
            'emergencyContacts' => fn ($query) => $query
                ->orderByDesc('is_primary')
                ->latest('id'),
            'preference',
            'consents' => fn ($query) => $query
                ->latest('accepted_at')
                ->latest('id'),
        ]);

        return Inertia::render('Admin/Clients/Show', [
            'clientProfile' => $this->showPayload($client),
        ]);
    }

    public function updateStatus(
        Request $request,
        ClientProfile $client
    ): RedirectResponse {
        Gate::authorize('update', $client);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $client,
            $validated
        ): void {
            $client->update([
                'status' => $validated['status'],
                'archived_at' => null,
                'archived_by' => null,
                'updated_by' => $request->user()->id,
            ]);

            $client->user()->update([
                'is_active' => $validated['status'] === 'active',
            ]);
        });

        return back()->with(
            'success',
            'Client status updated successfully.'
        );
    }

    public function destroy(
        Request $request,
        ClientProfile $client
    ): RedirectResponse {
        Gate::authorize('delete', $client);

        DB::transaction(function () use ($request, $client): void {
            $client->update([
                'status' => 'archived',
                'archived_at' => now(),
                'archived_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $client->user()->update([
                'is_active' => false,
            ]);
        });

        return to_route('admin.clients.index')
            ->with('success', 'Client archived successfully.');
    }

    public function restore(
        Request $request,
        ClientProfile $client
    ): RedirectResponse {
        Gate::authorize('restore', $client);

        DB::transaction(function () use ($request, $client): void {
            $client->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null,
                'updated_by' => $request->user()->id,
            ]);

            $client->user()->update([
                'is_active' => true,
            ]);
        });

        return back()->with(
            'success',
            'Client restored successfully.'
        );
    }

    private function indexPayload(
        ClientProfile $clientProfile
    ): array {
        $missingFields = $clientProfile->completionMissingFields();

        return [
            'id' => $clientProfile->id,
            'full_name' => $clientProfile->full_name,
            'preferred_name' => $clientProfile->preferred_name,
            'email' => $clientProfile->user?->email,
            'phone' => $clientProfile->user?->phone,
            'city' => $clientProfile->city,
            'district' => $clientProfile->district,
            'preferred_language' => $clientProfile->preferred_language,
            'preferred_contact_method' => $clientProfile->preferred_contact_method,
            'status' => $clientProfile->status,
            'is_user_active' => (bool) $clientProfile->user?->is_active,
            'is_complete' => $missingFields === [],
            'missing_fields_count' => count($missingFields),
            'emergency_contacts_count' => $clientProfile->emergency_contacts_count,
            'consents_count' => $clientProfile->consents_count,
            'created_at' => $clientProfile->created_at?->format('Y-m-d'),
            'profile_completed_at' => $clientProfile->profile_completed_at?->format('Y-m-d'),
            'archived_at' => $clientProfile->archived_at?->format('Y-m-d'),
        ];
    }

    private function showPayload(
        ClientProfile $clientProfile
    ): array {
        $missingFields = $clientProfile->completionMissingFields();

        return [
            'id' => $clientProfile->id,
            'full_name' => $clientProfile->full_name,
            'first_name' => $clientProfile->first_name,
            'last_name' => $clientProfile->last_name,
            'preferred_name' => $clientProfile->preferred_name,
            'date_of_birth' => $clientProfile->date_of_birth?->format('Y-m-d'),
            'gender' => $clientProfile->gender,
            'pronouns' => $clientProfile->pronouns,
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
            'status' => $clientProfile->status,
            'is_complete' => $missingFields === [],
            'missing_fields' => $missingFields,
            'terms_accepted_at' => $clientProfile->terms_accepted_at?->toDayDateTimeString(),
            'privacy_policy_accepted_at' => $clientProfile->privacy_policy_accepted_at?->toDayDateTimeString(),
            'communication_consent' => (bool) $clientProfile->communication_consent,
            'communication_consent_at' => $clientProfile->communication_consent_at?->toDayDateTimeString(),
            'emergency_contact_permission' => (bool) $clientProfile->emergency_contact_permission,
            'emergency_contact_permission_at' => $clientProfile->emergency_contact_permission_at?->toDayDateTimeString(),
            'privacy_preferences' => $clientProfile->privacy_preferences ?? [],
            'created_at' => $clientProfile->created_at?->toDayDateTimeString(),
            'updated_at' => $clientProfile->updated_at?->toDayDateTimeString(),
            'profile_completed_at' => $clientProfile->profile_completed_at?->toDayDateTimeString(),
            'archived_at' => $clientProfile->archived_at?->toDayDateTimeString(),
            'user' => [
                'id' => $clientProfile->user?->id,
                'name' => $clientProfile->user?->name,
                'email' => $clientProfile->user?->email,
                'phone' => $clientProfile->user?->phone,
                'is_active' => (bool) $clientProfile->user?->is_active,
                'email_verified_at' => $clientProfile->user?->email_verified_at?->toDayDateTimeString(),
                'last_login_at' => $clientProfile->user?->last_login_at?->toDayDateTimeString(),
                'created_at' => $clientProfile->user?->created_at?->toDayDateTimeString(),
            ],
            'emergency_contacts' => $clientProfile
                ->emergencyContacts
                ->map(
                    fn (
                        ClientEmergencyContact $contact
                    ): array => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'relationship' => $contact->relationship,
                        'phone' => $contact->phone,
                        'alternate_phone' => $contact->alternate_phone,
                        'email' => $contact->email,
                        'may_contact_in_emergency' => (bool) $contact->may_contact_in_emergency,
                        'is_primary' => (bool) $contact->is_primary,
                    ]
                )
                ->values(),
            'preference' => $clientProfile->preference instanceof ClientPreference
                ? [
                    'preferred_counselling_mode' => $clientProfile->preference->preferred_counselling_mode,
                    'preferred_counsellor_gender' => $clientProfile->preference->preferred_counsellor_gender,
                    'preferred_language' => $clientProfile->preference->preferred_language,
                    'general_availability_notes' => $clientProfile->preference->general_availability_notes,
                    'accessibility_requirements' => $clientProfile->preference->accessibility_requirements,
                    'additional_preferences' => $clientProfile->preference->additional_preferences,
                ]
                : null,
            'consents' => $clientProfile
                ->consents
                ->map(
                    fn (ClientConsent $consent): array => [
                        'id' => $consent->id,
                        'consent_type' => $consent->consent_type,
                        'version' => $consent->version,
                        'accepted_at' => $consent->accepted_at?->toDayDateTimeString(),
                        'source' => $consent->metadata['source'] ?? null,
                    ]
                )
                ->values(),
        ];
    }

    private function statusOptions(): array
    {
        return [
            [
                'value' => '',
                'label' => 'All statuses',
            ],
            [
                'value' => 'active',
                'label' => 'Active',
            ],
            [
                'value' => 'inactive',
                'label' => 'Inactive',
            ],
            [
                'value' => 'archived',
                'label' => 'Archived',
            ],
        ];
    }

    private function completionOptions(): array
    {
        return [
            [
                'value' => '',
                'label' => 'All completion states',
            ],
            [
                'value' => 'complete',
                'label' => 'Complete',
            ],
            [
                'value' => 'incomplete',
                'label' => 'Incomplete',
            ],
        ];
    }
}
