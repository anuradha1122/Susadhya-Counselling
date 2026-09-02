<?php

use App\Models\ClientIntake;
use App\Models\ClientProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createM10ReviewUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 333 4444',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function createM10ReviewClientProfile(string $name = 'Review Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => createM10ReviewUser('client', $name)->id,
    ]);
}

function createM10SubmittedIntake(ClientProfile $clientProfile, array $overrides = []): ClientIntake
{
    $intake = ClientIntake::factory()
        ->for($clientProfile)
        ->submitted()
        ->create(array_merge([
            'presenting_concerns' => 'Submitted concern for review.',
            'current_symptoms' => 'Submitted symptoms.',
            'counselling_goals' => 'Submitted goals.',
            'risk_level' => ClientIntake::RISK_MODERATE,
            'risk_notes' => 'Moderate risk calculated from screening.',
        ], $overrides));

    foreach (ClientIntake::screeningQuestions() as $question) {
        $intake->screeningAnswers()->create([
            'question_key' => $question['key'],
            'question_text' => $question['label'],
            'answer_score' => $question['key'] === 'self_harm_thoughts' ? 0 : 1,
            'answer_value' => 'Several days',
            'answer_notes' => null,
        ]);
    }

    return $intake;
}

it('allows admin to view submitted intake review list', function (): void {
    $admin = createM10ReviewUser('admin', 'M10 Admin');
    $clientProfile = createM10ReviewClientProfile();

    createM10SubmittedIntake($clientProfile);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.intakes.index'));

    $response->assertOk();

    $page = $response->viewData('page');
    $intakes = $page['props']['intakes']['data'];

    expect($page['component'])
        ->toBe('Intakes/ReviewIndex')
        ->and($page['props']['routePrefix'])
        ->toBe('admin')
        ->and($intakes)
        ->toHaveCount(1)
        ->and($intakes[0]['client']['name'])
        ->toBe($clientProfile->user->name);
});

it('allows counsellor to view submitted intake review list', function (): void {
    $counsellor = createM10ReviewUser('counsellor', 'M10 Counsellor');
    $clientProfile = createM10ReviewClientProfile();

    createM10SubmittedIntake($clientProfile);

    $response = $this
        ->actingAs($counsellor)
        ->get(route('counsellor.intakes.index'));

    $response->assertOk();

    $page = $response->viewData('page');

    expect($page['props']['routePrefix'])
        ->toBe('counsellor')
        ->and($page['props']['intakes']['data'])
        ->toHaveCount(1);
});

it('filters intake reviews by risk level', function (): void {
    $admin = createM10ReviewUser('admin', 'Risk Admin');

    $firstClientProfile = createM10ReviewClientProfile('Moderate Risk Client');
    $secondClientProfile = createM10ReviewClientProfile('Urgent Risk Client');

    createM10SubmittedIntake($firstClientProfile, [
        'risk_level' => ClientIntake::RISK_MODERATE,
    ]);

    createM10SubmittedIntake($secondClientProfile, [
        'risk_level' => ClientIntake::RISK_URGENT,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.intakes.index', [
            'risk_level' => ClientIntake::RISK_URGENT,
        ]));

    $intakes = $response->viewData('page')['props']['intakes']['data'];

    expect($intakes)
        ->toHaveCount(1)
        ->and($intakes[0]['risk_level'])
        ->toBe(ClientIntake::RISK_URGENT)
        ->and($intakes[0]['client']['name'])
        ->toBe('Urgent Risk Client');
});

it('filters intake reviews by client search', function (): void {
    $admin = createM10ReviewUser('admin', 'Search Admin');

    $searchClientProfile = createM10ReviewClientProfile('Search Intake Client');
    $hiddenClientProfile = createM10ReviewClientProfile('Hidden Intake Client');

    createM10SubmittedIntake($searchClientProfile);
    createM10SubmittedIntake($hiddenClientProfile);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.intakes.index', [
            'search' => 'Search Intake Client',
        ]));

    $intakes = $response->viewData('page')['props']['intakes']['data'];

    expect($intakes)
        ->toHaveCount(1)
        ->and($intakes[0]['client']['name'])
        ->toBe('Search Intake Client');
});

it('allows admin to mark intake as reviewed', function (): void {
    $admin = createM10ReviewUser('admin', 'Reviewer Admin');
    $clientProfile = createM10ReviewClientProfile();

    $intake = createM10SubmittedIntake($clientProfile);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.intakes.index'))
        ->patch(route('admin.intakes.review', $intake), [
            'status' => ClientIntake::STATUS_REVIEWED,
            'risk_level' => ClientIntake::RISK_MODERATE,
            'reviewer_notes' => 'Reviewed and suitable for regular counselling.',
        ]);

    $response->assertRedirect(route('admin.intakes.index'));

    $this->assertDatabaseHas('client_intakes', [
        'id' => $intake->id,
        'status' => ClientIntake::STATUS_REVIEWED,
        'risk_level' => ClientIntake::RISK_MODERATE,
        'reviewer_notes' => 'Reviewed and suitable for regular counselling.',
        'reviewed_by' => $admin->id,
    ]);

    expect($intake->refresh()->reviewed_at)->not->toBeNull();
});

it('allows counsellor to request client follow-up', function (): void {
    $counsellor = createM10ReviewUser('counsellor', 'Follow Up Counsellor');
    $clientProfile = createM10ReviewClientProfile();

    $intake = createM10SubmittedIntake($clientProfile);

    $response = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.intakes.index'))
        ->patch(route('counsellor.intakes.review', $intake), [
            'status' => ClientIntake::STATUS_REQUIRES_FOLLOW_UP,
            'risk_level' => ClientIntake::RISK_HIGH,
            'reviewer_notes' => 'Need more information before first session.',
        ]);

    $response->assertRedirect(route('counsellor.intakes.index'));

    $this->assertDatabaseHas('client_intakes', [
        'id' => $intake->id,
        'status' => ClientIntake::STATUS_REQUIRES_FOLLOW_UP,
        'risk_level' => ClientIntake::RISK_HIGH,
        'reviewer_notes' => 'Need more information before first session.',
        'reviewed_by' => $counsellor->id,
    ]);
});

it('does not show draft intakes in review lists', function (): void {
    $admin = createM10ReviewUser('admin', 'Draft Admin');
    $clientProfile = createM10ReviewClientProfile();

    ClientIntake::factory()
        ->for($clientProfile)
        ->create([
            'status' => ClientIntake::STATUS_DRAFT,
        ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.intakes.index'));

    $intakes = $response->viewData('page')['props']['intakes']['data'];

    expect($intakes)->toHaveCount(0);
});

it('prevents client from accessing admin intake reviews', function (): void {
    $clientProfile = createM10ReviewClientProfile();

    $this
        ->actingAs($clientProfile->user)
        ->get(route('admin.intakes.index'))
        ->assertForbidden();
});
