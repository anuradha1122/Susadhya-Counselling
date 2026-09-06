<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\SessionFeedback;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SupportPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();

    $this->seed(
        RolePermissionSeeder::class
    );

    $this->seed(
        SupportPermissionSeeder::class
    );

    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();
});

function feedbackClient(): array
{
    $user =
        User::factory()->create([
            'is_active' => true,
        ]);

    $user->assignRole(
        'client'
    );

    $profile =
        ClientProfile::factory()
            ->create([
                'user_id' => $user->id,
            ]);

    return [
        $user,
        $profile,
    ];
}

it(
    'allows feedback for a completed owned appointment',
    function (): void {
        [
            $client,
            $profile,
        ] = feedbackClient();

        $appointment =
            Appointment::factory()
                ->create([
                    'client_profile_id' => $profile->id,

                    'status' => Appointment::STATUS_COMPLETED,
                ]);

        $this
            ->actingAs($client)
            ->post(
                route(
                    'client.support.feedback.store',
                    $appointment->uuid
                ),
                [
                    'overall_rating' => 5,

                    'technical_rating' => 4,

                    'comment' => 'The platform worked well.',

                    'would_recommend' => true,

                    'consent_to_follow_up' => false,
                ]
            )
            ->assertSessionHasNoErrors();

        expect(
            SessionFeedback::count()
        )->toBe(1);
    }
);

it(
    'rejects feedback for an appointment that is not completed',
    function (): void {
        [
            $client,
            $profile,
        ] = feedbackClient();

        $appointment =
            Appointment::factory()
                ->create([
                    'client_profile_id' => $profile->id,

                    'status' => Appointment::STATUS_CONFIRMED,
                ]);

        $this
            ->actingAs($client)
            ->post(
                route(
                    'client.support.feedback.store',
                    $appointment->uuid
                ),
                [
                    'overall_rating' => 5,

                    'consent_to_follow_up' => false,
                ]
            )
            ->assertSessionHasErrors(
                'appointment'
            );

        expect(
            SessionFeedback::count()
        )->toBe(0);
    }
);

it(
    'allows only one feedback submission per appointment',
    function (): void {
        [
            $client,
            $profile,
        ] = feedbackClient();

        $appointment =
            Appointment::factory()
                ->create([
                    'client_profile_id' => $profile->id,

                    'status' => Appointment::STATUS_COMPLETED,
                ]);

        SessionFeedback::query()
            ->create([
                'appointment_id' => $appointment->id,

                'client_profile_id' => $profile->id,

                'overall_rating' => 5,

                'consent_to_follow_up' => false,

                'submitted_at' => now(),
            ]);

        $this
            ->actingAs($client)
            ->post(
                route(
                    'client.support.feedback.store',
                    $appointment->uuid
                ),
                [
                    'overall_rating' => 4,

                    'consent_to_follow_up' => false,
                ]
            )
            ->assertSessionHasErrors(
                'appointment'
            );

        expect(
            SessionFeedback::count()
        )->toBe(1);
    }
);
