<?php

use App\Models\ClientConsent;
use App\Models\ClientEmergencyContact;
use App\Models\ClientPreference;
use App\Models\ClientProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createClientUserForModule05(
    array $userOverrides = [],
    array $profileOverrides = []
): User {
    $user = User::factory()->create(array_merge([
        'name' => 'Client User',
        'email' => 'client@example.com',
        'phone' => '+94 77 123 4567',
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('client');

    $profile = ClientProfile::factory()
        ->for($user)
        ->create(array_merge([
            'first_name' => 'Client',
            'last_name' => 'User',
            'preferred_language' => 'english',
            'preferred_contact_method' => 'email',
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $profileOverrides));

    ClientPreference::factory()
        ->for($profile)
        ->create([
            'preferred_language' => $profile->preferred_language,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

    ClientConsent::factory()
        ->terms()
        ->for($profile)
        ->create([
            'recorded_by' => $user->id,
        ]);

    ClientConsent::factory()
        ->privacyPolicy()
        ->for($profile)
        ->create([
            'recorded_by' => $user->id,
        ]);

    return $user->refresh();
}

it('allows a visitor to register as a client with profile preference and consent records', function (): void {
    Event::fake([
        Registered::class,
    ]);

    $response = $this->post(route('register'), [
        'first_name' => 'Test',
        'last_name' => 'Client',
        'preferred_name' => 'TC',
        'email' => 'test.client@example.com',
        'phone' => '+94 77 123 4567',
        'preferred_language' => 'english',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'terms_accepted' => '1',
        'privacy_policy_accepted' => '1',
        'communication_consent' => '1',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    Event::assertDispatched(Registered::class);

    $user = User::where('email', 'test.client@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole('client'))->toBeTrue()
        ->and($user->phone)->toBe('+94 77 123 4567');

    $profile = $user->clientProfile()
        ->with([
            'preference',
            'consents',
        ])
        ->first();

    expect($profile)->not->toBeNull()
        ->and($profile->first_name)->toBe('Test')
        ->and($profile->last_name)->toBe('Client')
        ->and($profile->preferred_name)->toBe('TC')
        ->and($profile->preferred_language)->toBe('english')
        ->and($profile->communication_consent)->toBeTrue()
        ->and($profile->terms_accepted_at)->not->toBeNull()
        ->and($profile->privacy_policy_accepted_at)->not->toBeNull()
        ->and($profile->preference)->not->toBeNull()
        ->and($profile->preference->preferred_language)->toBe('english');

    expect($profile->consents->pluck('consent_type')->all())
        ->toContain(ClientConsent::TYPE_TERMS)
        ->toContain(ClientConsent::TYPE_PRIVACY_POLICY)
        ->toContain(ClientConsent::TYPE_COMMUNICATION);
});

it('requires preferred language during client registration', function (): void {
    $response = $this->from(route('register'))->post(route('register'), [
        'first_name' => 'Test',
        'last_name' => 'Client',
        'email' => 'language.required@example.com',
        'phone' => '+94 77 123 4567',
        'preferred_language' => '',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'terms_accepted' => '1',
        'privacy_policy_accepted' => '1',
    ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('preferred_language');

    $this->assertDatabaseMissing('users', [
        'email' => 'language.required@example.com',
    ]);
});

it('allows a client to view and update their profile', function (): void {
    $user = createClientUserForModule05();

    $this
        ->actingAs($user)
        ->get(route('client.profile.show'))
        ->assertOk();

    $response = $this
        ->actingAs($user)
        ->patch(route('client.profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Client',
            'preferred_name' => 'UC',
            'date_of_birth' => '1995-05-10',
            'gender' => 'prefer_not_to_say',
            'pronouns' => 'they/them',
            'phone' => '+94 71 111 2222',
            'alternate_phone' => '+94 72 333 4444',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Apartment 4',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'postal_code' => '10000',
            'preferred_language' => 'sinhala',
            'preferred_contact_method' => 'whatsapp',
            'occupation' => 'Software Developer',
            'marital_status' => 'single',
        ]);

    $response->assertRedirect(route('client.profile.show'));

    $user->refresh();

    $profile = $user->clientProfile()->first();

    expect($user->name)->toBe('Updated Client')
        ->and($user->phone)->toBe('+94 71 111 2222')
        ->and($profile->first_name)->toBe('Updated')
        ->and($profile->preferred_language)->toBe('sinhala')
        ->and($profile->preferred_contact_method)->toBe('whatsapp')
        ->and($profile->city)->toBe('Colombo');
});

it('rejects an invalid future date of birth on profile update', function (): void {
    $user = createClientUserForModule05();

    $response = $this
        ->actingAs($user)
        ->from(route('client.profile.edit'))
        ->patch(route('client.profile.update'), [
            'first_name' => 'Client',
            'last_name' => 'User',
            'date_of_birth' => now()->addDay()->format('Y-m-d'),
            'gender' => 'prefer_not_to_say',
            'phone' => '+94 77 123 4567',
            'preferred_language' => 'english',
            'preferred_contact_method' => 'email',
        ]);

    $response
        ->assertRedirect(route('client.profile.edit'))
        ->assertSessionHasErrors('date_of_birth');
});

it('allows a client to create update and delete emergency contacts', function (): void {
    $user = createClientUserForModule05();

    $createResponse = $this
        ->actingAs($user)
        ->post(route('client.emergency-contacts.store'), [
            'name' => 'Emergency Person',
            'relationship' => 'Sibling',
            'phone' => '+94 77 111 2222',
            'alternate_phone' => '+94 71 333 4444',
            'email' => 'emergency@example.com',
            'may_contact_in_emergency' => '1',
            'is_primary' => '1',
        ]);

    $createResponse->assertRedirect(route('client.emergency-contacts.index'));

    $profile = $user->clientProfile()->first();

    $contact = $profile->emergencyContacts()->first();

    expect($contact)->not->toBeNull()
        ->and($contact->name)->toBe('Emergency Person')
        ->and($contact->is_primary)->toBeTrue();

    $updateResponse = $this
        ->actingAs($user)
        ->patch(route('client.emergency-contacts.update', $contact), [
            'name' => 'Updated Emergency Person',
            'relationship' => 'Guardian',
            'phone' => '+94 77 555 6666',
            'alternate_phone' => null,
            'email' => 'updated.emergency@example.com',
            'may_contact_in_emergency' => '1',
            'is_primary' => '1',
        ]);

    $updateResponse->assertRedirect(route('client.emergency-contacts.index'));

    expect($contact->refresh()->name)->toBe('Updated Emergency Person')
        ->and($contact->relationship)->toBe('Guardian');

    $deleteResponse = $this
        ->actingAs($user)
        ->delete(route('client.emergency-contacts.destroy', $contact));

    $deleteResponse->assertRedirect(route('client.emergency-contacts.index'));

    $this->assertDatabaseMissing('client_emergency_contacts', [
        'id' => $contact->id,
    ]);
});

it('prevents a client from editing another clients emergency contact', function (): void {
    $owner = createClientUserForModule05([
        'email' => 'owner@example.com',
    ]);

    $otherClient = createClientUserForModule05([
        'email' => 'other@example.com',
    ]);

    $contact = ClientEmergencyContact::factory()
        ->for($owner->clientProfile)
        ->create();

    $this
        ->actingAs($otherClient)
        ->get(route('client.emergency-contacts.edit', $contact))
        ->assertNotFound();

    $this
        ->actingAs($otherClient)
        ->patch(route('client.emergency-contacts.update', $contact), [
            'name' => 'Bad Update',
            'relationship' => 'Friend',
            'phone' => '+94 77 222 3333',
            'may_contact_in_emergency' => '1',
            'is_primary' => '1',
        ])
        ->assertNotFound();

    expect($contact->refresh()->name)->not->toBe('Bad Update');
});

it('allows a client to update counselling preferences', function (): void {
    $user = createClientUserForModule05();

    $response = $this
        ->actingAs($user)
        ->patch(route('client.preferences.update'), [
            'preferred_counselling_mode' => 'online',
            'preferred_counsellor_gender' => 'female',
            'preferred_language' => 'tamil',
            'general_availability_notes' => 'Weekdays after 6 PM.',
            'accessibility_requirements' => 'Online sessions preferred.',
            'additional_preferences' => 'Prefers structured sessions.',
        ]);

    $response->assertRedirect(route('client.preferences.show'));

    $preference = $user->clientProfile->preference()->first();

    expect($preference->preferred_counselling_mode)->toBe('online')
        ->and($preference->preferred_counsellor_gender)->toBe('female')
        ->and($preference->preferred_language)->toBe('tamil')
        ->and($preference->general_availability_notes)->toBe('Weekdays after 6 PM.');
});

it('allows a client to update privacy settings and records new consent history', function (): void {
    $user = createClientUserForModule05([], [
        'communication_consent' => false,
        'communication_consent_at' => null,
        'emergency_contact_permission' => false,
        'emergency_contact_permission_at' => null,
        'privacy_preferences' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('client.privacy.update'), [
            'communication_consent' => '1',
            'emergency_contact_permission' => '1',
            'allow_email_updates' => '1',
            'allow_sms_updates' => '1',
            'allow_whatsapp_updates' => '0',
            'share_profile_with_assigned_counsellor' => '1',
        ]);

    $response->assertRedirect(route('client.privacy.show'));

    $profile = $user->clientProfile()->with('consents')->first();

    expect($profile->communication_consent)->toBeTrue()
        ->and($profile->communication_consent_at)->not->toBeNull()
        ->and($profile->emergency_contact_permission)->toBeTrue()
        ->and($profile->emergency_contact_permission_at)->not->toBeNull()
        ->and($profile->privacy_preferences['allow_sms_updates'])->toBeTrue();

    expect($profile->consents->pluck('consent_type')->all())
        ->toContain(ClientConsent::TYPE_COMMUNICATION)
        ->toContain(ClientConsent::TYPE_EMERGENCY_CONTACT);
});
