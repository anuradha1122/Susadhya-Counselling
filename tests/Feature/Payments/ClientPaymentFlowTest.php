<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingService;
use App\Models\Payment;
use App\Models\User;
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

    $this->makeAppointment = function (
        ClientProfile $clientProfile,
        string $price = '5000.00',
        string $currency = 'LKR',
    ): array {
        $service = CounsellingService::factory()->create([
            'price' => $price,
            'currency' => $currency,
        ]);

        $appointment = Appointment::factory()->create([
            'client_profile_id' => $clientProfile->id,
            'counselling_service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        return [
            $appointment,
            $service,
        ];
    };
});

it('allows a client to create a payment for their own appointment and snapshots the service fee', function (): void {
    [$client, $profile] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $profile,
        '7500.00',
        'LKR'
    );

    $response = $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->first();

    expect($payment)
        ->not
        ->toBeNull();

    $response->assertRedirect(
        route(
            'client.payments.checkout',
            $payment
        )
    );

    $appointment->refresh();

    expect($appointment->fee_amount)
        ->toBe('7500.00')
        ->and($appointment->fee_currency)
        ->toBe('LKR');

    expect($payment->client_profile_id)
        ->toBe($profile->id)
        ->and($payment->appointment_id)
        ->toBe($appointment->id)
        ->and($payment->amount)
        ->toBe('7500.00')
        ->and($payment->currency)
        ->toBe('LKR')
        ->and($payment->provider)
        ->toBe('sandbox')
        ->and($payment->method)
        ->toBe(Payment::METHOD_GATEWAY)
        ->and($payment->status)
        ->toBe(Payment::STATUS_PENDING)
        ->and($payment->reconciliation_status)
        ->toBe(Payment::RECONCILIATION_PENDING);

    expect($payment->provider_payment_id)
        ->not
        ->toBeNull()
        ->and($payment->provider_reference)
        ->not
        ->toBeNull()
        ->and($payment->idempotency_key)
        ->not
        ->toBeNull();

    $invoice = $payment
        ->fresh()
        ->invoice;

    expect($invoice)
        ->not
        ->toBeNull()
        ->and($invoice->invoice_number)
        ->toStartWith('INV-')
        ->and($invoice->total)
        ->toBe('7500.00')
        ->and($invoice->currency)
        ->toBe('LKR')
        ->and($invoice->receipt_number)
        ->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'event_type' => 'payment_intent_created',
            'source' => 'client',
        ]
    );
});

it('completes a successful sandbox payment and generates a receipt', function (): void {
    [$client, $profile] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $profile
    );

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->firstOrFail();

    $response = $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.sandbox.complete',
                $payment
            ),
            [
                'result' => 'success',
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

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_PAID)
        ->and($payment->provider_status)
        ->toBe(Payment::STATUS_PAID)
        ->and($payment->reconciliation_status)
        ->toBe(Payment::RECONCILIATION_MATCHED)
        ->and($payment->paid_at)
        ->not
        ->toBeNull()
        ->and($payment->reconciled_at)
        ->not
        ->toBeNull();

    $invoice = $payment->invoice;

    expect($invoice)
        ->not
        ->toBeNull()
        ->and($invoice->receipt_number)
        ->not
        ->toBeNull()
        ->and($invoice->receipt_number)
        ->toStartWith('RCT-')
        ->and($invoice->paid_at)
        ->not
        ->toBeNull()
        ->and($invoice->receipt_issued_at)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'event_type' => 'payment_paid',
        ]
    );
});

it('records a failed sandbox payment correctly', function (): void {
    [$client, $profile] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $profile
    );

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->firstOrFail();

    $response = $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.sandbox.complete',
                $payment
            ),
            [
                'result' => 'failed',
            ]
        );

    $response
        ->assertRedirect(
            route(
                'client.payments.index'
            )
        );

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_FAILED)
        ->and($payment->provider_status)
        ->toBe(Payment::STATUS_FAILED)
        ->and($payment->reconciliation_status)
        ->toBe(Payment::RECONCILIATION_MATCHED)
        ->and($payment->failed_at)
        ->not
        ->toBeNull();

    expect(
        $payment->invoice
            ?->receipt_number
    )->toBeNull();
});

it('prevents another client from paying for an appointment they do not own', function (): void {
    [$owner, $ownerProfile] = ($this->makeClient)();

    [$otherClient] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $ownerProfile
    );

    $response = $this
        ->actingAs($otherClient)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $response->assertForbidden();

    expect(
        Payment::query()->count()
    )->toBe(0);
});

it('prevents duplicate payment after an appointment has already been paid', function (): void {
    [$client, $profile] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $profile
    );

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->firstOrFail();

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.sandbox.complete',
                $payment
            ),
            [
                'result' => 'success',
            ]
        );

    expect(
        Payment::query()->count()
    )->toBe(1);

    $response = $this
        ->actingAs($client)
        ->from(
            route(
                'client.payments.index'
            )
        )
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $response
        ->assertRedirect(
            route(
                'client.payments.index'
            )
        )
        ->assertSessionHasErrors(
            'appointment'
        );

    expect(
        Payment::query()->count()
    )->toBe(1);
});

it('allows a client to download their own invoice and receipt', function (): void {
    [$client, $profile] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $profile
    );

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->firstOrFail();

    $this
        ->actingAs($client)
        ->post(
            route(
                'client.payments.sandbox.complete',
                $payment
            ),
            [
                'result' => 'success',
            ]
        );

    $payment->refresh();

    $invoiceResponse = $this
        ->actingAs($client)
        ->get(
            route(
                'client.payments.invoice',
                $payment
            )
        );

    $invoiceResponse
        ->assertOk()
        ->assertHeader(
            'content-type',
            'application/pdf'
        );

    $receiptResponse = $this
        ->actingAs($client)
        ->get(
            route(
                'client.payments.receipt',
                $payment
            )
        );

    $receiptResponse
        ->assertOk()
        ->assertHeader(
            'content-type',
            'application/pdf'
        );
});

it('prevents another client from downloading invoice and receipt', function (): void {
    [$owner, $ownerProfile] = ($this->makeClient)();

    [$otherClient] = ($this->makeClient)();

    [$appointment] = ($this->makeAppointment)(
        $ownerProfile
    );

    $this
        ->actingAs($owner)
        ->post(
            route(
                'client.payments.store',
                $appointment
            )
        );

    $payment = Payment::query()->firstOrFail();

    $this
        ->actingAs($owner)
        ->post(
            route(
                'client.payments.sandbox.complete',
                $payment
            ),
            [
                'result' => 'success',
            ]
        );

    $this
        ->actingAs($otherClient)
        ->get(
            route(
                'client.payments.invoice',
                $payment
            )
        )
        ->assertForbidden();

    $this
        ->actingAs($otherClient)
        ->get(
            route(
                'client.payments.receipt',
                $payment
            )
        )
        ->assertForbidden();
});
