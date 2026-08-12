<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientEmergencyContactRequest;
use App\Http\Requests\Client\UpdateClientEmergencyContactRequest;
use App\Models\ClientEmergencyContact;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EmergencyContactController extends Controller
{
    public function index(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('view', $clientProfile);

        $contacts = $clientProfile
            ->emergencyContacts()
            ->orderByDesc('is_primary')
            ->latest('id')
            ->get()
            ->map(fn (ClientEmergencyContact $contact): array => $this->contactPayload($contact))
            ->values();

        return Inertia::render('Client/EmergencyContacts/Index', [
            'contacts' => $contacts,
            'clientProfile' => [
                'id' => $clientProfile->id,
                'emergency_contact_permission' => (bool) $clientProfile->emergency_contact_permission,
                'is_complete' => $clientProfile->completionMissingFields() === [],
                'missing_fields' => $clientProfile->completionMissingFields(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $clientProfile = $this->clientProfileFor($request);

        Gate::authorize('update', $clientProfile);

        return Inertia::render('Client/EmergencyContacts/Create', [
            'contact' => $this->emptyContactPayload(
                isPrimary: ! $clientProfile->emergencyContacts()->exists()
            ),
        ]);
    }

    public function store(
        StoreClientEmergencyContactRequest $request
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);
        $validated = $request->validated();

        DB::transaction(function () use (
            $request,
            $clientProfile,
            $validated
        ): void {
            $isFirstContact = ! $clientProfile
                ->emergencyContacts()
                ->exists();

            $isPrimary = (bool) ($validated['is_primary'] ?? false)
                || $isFirstContact;

            if ($isPrimary) {
                $clientProfile
                    ->emergencyContacts()
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            $clientProfile->emergencyContacts()->create([
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'may_contact_in_emergency' => (bool) $validated['may_contact_in_emergency'],
                'is_primary' => $isPrimary,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $clientProfile->load([
                'user',
                'emergencyContacts',
            ]);

            $clientProfile->refreshCompletionStatus();
        });

        return to_route('client.emergency-contacts.index')
            ->with('success', 'Emergency contact added successfully.');
    }

    public function edit(
        Request $request,
        ClientEmergencyContact $emergencyContact
    ): Response {
        $clientProfile = $this->clientProfileFor($request);

        $this->ensureContactBelongsToClient(
            $clientProfile,
            $emergencyContact
        );

        Gate::authorize('update', $clientProfile);

        return Inertia::render('Client/EmergencyContacts/Edit', [
            'contact' => $this->contactPayload($emergencyContact),
        ]);
    }

    public function update(
        UpdateClientEmergencyContactRequest $request,
        ClientEmergencyContact $emergencyContact
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);

        $this->ensureContactBelongsToClient(
            $clientProfile,
            $emergencyContact
        );

        $validated = $request->validated();

        DB::transaction(function () use (
            $request,
            $clientProfile,
            $emergencyContact,
            $validated
        ): void {
            $hasOnlyOneContact = $clientProfile
                ->emergencyContacts()
                ->whereKeyNot($emergencyContact->id)
                ->doesntExist();

            $isPrimary = (bool) ($validated['is_primary'] ?? false)
                || $hasOnlyOneContact;

            if ($isPrimary) {
                $clientProfile
                    ->emergencyContacts()
                    ->whereKeyNot($emergencyContact->id)
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            $emergencyContact->update([
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'may_contact_in_emergency' => (bool) $validated['may_contact_in_emergency'],
                'is_primary' => $isPrimary,
                'updated_by' => $request->user()->id,
            ]);

            $clientProfile->load([
                'user',
                'emergencyContacts',
            ]);

            $clientProfile->refreshCompletionStatus();
        });

        return to_route('client.emergency-contacts.index')
            ->with('success', 'Emergency contact updated successfully.');
    }

    public function destroy(
        Request $request,
        ClientEmergencyContact $emergencyContact
    ): RedirectResponse {
        $clientProfile = $this->clientProfileFor($request);

        $this->ensureContactBelongsToClient(
            $clientProfile,
            $emergencyContact
        );

        Gate::authorize('update', $clientProfile);

        DB::transaction(function () use (
            $clientProfile,
            $emergencyContact
        ): void {
            $wasPrimary = $emergencyContact->is_primary;

            $emergencyContact->delete();

            if ($wasPrimary) {
                $nextContact = $clientProfile
                    ->emergencyContacts()
                    ->latest('id')
                    ->first();

                if ($nextContact) {
                    $nextContact->update([
                        'is_primary' => true,
                    ]);
                }
            }

            $clientProfile->load([
                'user',
                'emergencyContacts',
            ]);

            $clientProfile->refreshCompletionStatus();
        });

        return to_route('client.emergency-contacts.index')
            ->with('success', 'Emergency contact removed successfully.');
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

    private function ensureContactBelongsToClient(
        ClientProfile $clientProfile,
        ClientEmergencyContact $emergencyContact
    ): void {
        abort_unless(
            $emergencyContact->client_profile_id === $clientProfile->id,
            404
        );
    }

    private function emptyContactPayload(bool $isPrimary): array
    {
        return [
            'id' => null,
            'name' => '',
            'relationship' => '',
            'phone' => '',
            'alternate_phone' => '',
            'email' => '',
            'may_contact_in_emergency' => true,
            'is_primary' => $isPrimary,
        ];
    }

    private function contactPayload(
        ClientEmergencyContact $contact
    ): array {
        return [
            'id' => $contact->id,
            'name' => $contact->name,
            'relationship' => $contact->relationship,
            'phone' => $contact->phone,
            'alternate_phone' => $contact->alternate_phone,
            'email' => $contact->email,
            'may_contact_in_emergency' => (bool) $contact->may_contact_in_emergency,
            'is_primary' => (bool) $contact->is_primary,
            'created_at' => $contact->created_at?->format('Y-m-d'),
            'updated_at' => $contact->updated_at?->format('Y-m-d'),
        ];
    }
}
