<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingService;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\Payments\PaymentReconciliationService;
use App\Services\Payments\PaymentService;
use App\Services\Payments\RefundService;
use Database\Seeders\PaymentPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        PaymentPermissionSeeder::class
    );

    config([
        'payments.gateway' => 'sandbox',
        'payments.currency' => 'LKR',
        'payments.sandbox.webhook_secret' => 'test-payment-secret',
    ]);

    $this->makeClient = function (): array {
        $user = User::factory()->create();

        $user->assignRole('client');

        $profile = ClientProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        return [
            $user,
            $profile,
        ];
    };

    $this->makeFinanceUser = function (): User {
        $user = User::factory()->create();

        $user->assignRole(
            'finance_admin'
        );

        return $user;
    };

    $this->makePaidPayment = function (
        string $amount = '5000.00'
    ): array {
        [$client, $profile] =
            ($this->makeClient)();

        $service = CounsellingService::factory()->create([
            'price' => $amount,
            'currency' => 'LKR',
        ]);

        $appointment = Appointment::factory()->create([
            'client_profile_id' => $profile->id,
            'counselling_service_id' => $service->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $result = app(
            PaymentService::class
        )->createGatewayPayment(
            $appointment,
            $client
        );

        /** @var Payment $payment */
        $payment = $result['payment'];

        $metadata = $payment->metadata ?? [];

        $metadata[
            'sandbox_remote_status'
        ] = Payment::STATUS_PAID;

        $metadata[
            'sandbox_remote_amount'
        ] = $payment->amount;

        $metadata[
            'sandbox_remote_currency'
        ] = $payment->currency;

        $payment->forceFill([
            'metadata' => $metadata,
        ])->save();

        app(
            PaymentReconciliationService::class
        )->reconcile(
            $payment,
            $client
        );

        return [
            $client,
            $profile,
            $appointment,
            $payment->refresh(),
        ];
    };
});

it('allows a client to request a refund for their own paid payment', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)();

    $response = $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.refunds.store',
                $payment
            ),
            [
                'requested_amount' => '1500.00',
                'reason' => 'Appointment was cancelled and I would like a partial refund.',
            ]
        );

    $response
        ->assertRedirect(
            route(
                'client.payments.index'
            )
        )
        ->assertSessionHas(
            'success'
        );

    $refund = Refund::query()->firstOrFail();

    expect($refund->payment_id)
        ->toBe($payment->id)
        ->and($refund->requested_by)
        ->toBe($client->id)
        ->and($refund->requested_amount)
        ->toBe('1500.00')
        ->and($refund->currency)
        ->toBe('LKR')
        ->and($refund->status)
        ->toBe(Refund::STATUS_REQUESTED)
        ->and($refund->refund_number)
        ->toStartWith('REF-');

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'refund_id' => $refund->id,
            'event_type' => 'refund_requested',
            'source' => 'client',
        ]
    );
});

it('prevents another client from requesting a refund for someone elses payment', function (): void {
    [
        $owner,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)();

    [$otherClient] =
        ($this->makeClient)();

    $response = $this
        ->actingAs($otherClient)
        ->post(
            route(
                'client.payments.refunds.store',
                $payment
            ),
            [
                'requested_amount' => '1000.00',
                'reason' => 'Attempting refund.',
            ]
        );

    $response->assertForbidden();

    expect(
        Refund::query()->count()
    )->toBe(0);
});

it('prevents a refund request from exceeding the refundable payment balance', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)(
        '5000.00'
    );

    $response = $this
        ->actingAs($client)
        ->from(
            route(
                'client.payments.index'
            )
        )
        ->post(
            route(
                'client.payments.refunds.store',
                $payment
            ),
            [
                'requested_amount' => '6000.00',
                'reason' => 'Invalid excessive request.',
            ]
        );

    $response
        ->assertRedirect(
            route(
                'client.payments.index'
            )
        )
        ->assertSessionHasErrors(
            'requested_amount'
        );

    expect(
        Refund::query()->count()
    )->toBe(0);
});

it('allows finance admin to approve a requested refund', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)();

    $financeUser =
        ($this->makeFinanceUser)();

    $refund = app(
        RefundService::class
    )->request(
        $payment,
        $client,
        '2000.00',
        'Requesting a partial refund.'
    );

    $response = $this
        ->actingAs($financeUser)
        ->patch(
            route(
                'finance.refunds.decide',
                $refund
            ),
            [
                'decision' => 'approve',
                'approved_amount' => '1500.00',
                'decision_notes' => 'Approved after finance review.',
            ]
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $refund->refresh();

    expect($refund->status)
        ->toBe(Refund::STATUS_APPROVED)
        ->and($refund->approved_amount)
        ->toBe('1500.00')
        ->and($refund->decided_by)
        ->toBe($financeUser->id)
        ->and($refund->decided_at)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'refund_id' => $refund->id,
            'event_type' => 'refund_approved',
            'source' => 'finance',
        ]
    );
});

it('allows finance admin to reject a requested refund', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)();

    $financeUser =
        ($this->makeFinanceUser)();

    $refund = app(
        RefundService::class
    )->request(
        $payment,
        $client,
        '1000.00',
        'Requesting refund.'
    );

    $response = $this
        ->actingAs($financeUser)
        ->patch(
            route(
                'finance.refunds.decide',
                $refund
            ),
            [
                'decision' => 'reject',
                'decision_notes' => 'The appointment was already completed and the refund request does not meet policy.',
            ]
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $refund->refresh();

    expect($refund->status)
        ->toBe(Refund::STATUS_REJECTED)
        ->and($refund->decided_by)
        ->toBe($financeUser->id)
        ->and($refund->decision_notes)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'refund_id' => $refund->id,
            'event_type' => 'refund_rejected',
        ]
    );
});

it('processes a partial sandbox refund and updates payment status', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)(
        '5000.00'
    );

    $financeUser =
        ($this->makeFinanceUser)();

    $refundService = app(
        RefundService::class
    );

    $refund = $refundService->request(
        $payment,
        $client,
        '1000.00',
        'Partial refund request.'
    );

    $refundService->approve(
        $refund,
        $financeUser,
        '1000.00',
        'Approved.'
    );

    $response = $this
        ->actingAs($financeUser)
        ->patch(
            route(
                'finance.refunds.process',
                $refund
            )
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $refund->refresh();
    $payment->refresh();

    expect($refund->status)
        ->toBe(Refund::STATUS_REFUNDED)
        ->and($refund->provider_refund_id)
        ->not
        ->toBeNull()
        ->and($refund->processed_at)
        ->not
        ->toBeNull();

    expect($payment->status)
        ->toBe(
            Payment::STATUS_PARTIALLY_REFUNDED
        );

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'refund_id' => $refund->id,
            'event_type' => 'refund_processed',
            'source' => 'finance',
        ]
    );
});

it('processes a full sandbox refund and marks the payment refunded', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePaidPayment)(
        '5000.00'
    );

    $financeUser =
        ($this->makeFinanceUser)();

    $refundService = app(
        RefundService::class
    );

    $refund = $refundService->request(
        $payment,
        $client,
        '5000.00',
        'Full refund request.'
    );

    $refundService->approve(
        $refund,
        $financeUser,
        '5000.00',
        'Full refund approved.'
    );

    $this
        ->actingAs($financeUser)
        ->patch(
            route(
                'finance.refunds.process',
                $refund
            )
        )
        ->assertRedirect();

    $refund->refresh();
    $payment->refresh();

    expect($refund->status)
        ->toBe(Refund::STATUS_REFUNDED);

    expect($payment->status)
        ->toBe(
            Payment::STATUS_REFUNDED
        );
});
