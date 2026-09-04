<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingService;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\User;
use App\Services\Payments\PaymentService;
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
        'payments.sandbox.webhook_secret' => 'test-payment-webhook-secret',
    ]);

    $this->makePendingPayment = function (
        string $amount = '5000.00'
    ): array {
        $client = User::factory()->create();

        $client->assignRole('client');

        $profile = ClientProfile::factory()->create([
            'user_id' => $client->id,
        ]);

        $service = CounsellingService::factory()->create([
            'price' => $amount,
            'currency' => 'LKR',
        ]);

        $appointment = Appointment::factory()->create([
            'client_profile_id' => $profile->id,
            'counselling_service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $result = app(
            PaymentService::class
        )->createGatewayPayment(
            $appointment,
            $client
        );

        /** @var Payment $payment */
        $payment = $result['payment'];

        return [
            $client,
            $profile,
            $appointment,
            $payment->refresh(),
        ];
    };

    $this->sendWebhook = function (
        array $payload,
        ?string $signature = null
    ) {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        $signature ??= hash_hmac(
            'sha256',
            $json,
            (string) config(
                'payments.sandbox.webhook_secret'
            )
        );

        return $this->call(
            'POST',
            route(
                'payments.webhook',
                [
                    'provider' => 'sandbox',
                ]
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_SUSADHYA_PAYMENT_SIGNATURE' => $signature,
            ],
            $json
        );
    };
});

it('accepts a correctly signed provider callback and marks the payment paid', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)();

    $payload = [
        'event_id' => 'evt-payment-paid-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_PAID,
        'amount' => '5000.00',
        'currency' => 'LKR',
    ];

    $response = ($this->sendWebhook)(
        $payload
    );

    $response
        ->assertOk()
        ->assertJson([
            'ok' => true,
        ]);

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_PAID)
        ->and($payment->provider_status)
        ->toBe(Payment::STATUS_PAID)
        ->and($payment->provider_amount)
        ->toBe('5000.00')
        ->and($payment->provider_currency)
        ->toBe('LKR')
        ->and($payment->reconciliation_status)
        ->toBe(Payment::RECONCILIATION_MATCHED)
        ->and($payment->paid_at)
        ->not
        ->toBeNull();

    expect($payment->invoice)
        ->not
        ->toBeNull()
        ->and($payment->invoice->receipt_number)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'provider_event_id' => 'evt-payment-paid-001',
            'event_type' => 'provider_callback',
            'status' => 'processed',
        ]
    );
});

it('rejects a provider callback with an invalid signature', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)();

    $payload = [
        'event_id' => 'evt-invalid-signature-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_PAID,
        'amount' => '5000.00',
        'currency' => 'LKR',
    ];

    $response = ($this->sendWebhook)(
        $payload,
        'definitely-not-a-valid-signature'
    );

    $response->assertUnauthorized();

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_PENDING);

    expect(
        PaymentEvent::query()
            ->where(
                'provider_event_id',
                'evt-invalid-signature-001'
            )
            ->exists()
    )->toBeFalse();
});

it('does not mark a payment paid when provider amount does not match local amount', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)(
        '5000.00'
    );

    $payload = [
        'event_id' => 'evt-payment-mismatch-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_PAID,
        'amount' => '4500.00',
        'currency' => 'LKR',
    ];

    $response = ($this->sendWebhook)(
        $payload
    );

    $response
        ->assertOk()
        ->assertJson([
            'ok' => true,
        ]);

    $payment->refresh();

    expect($payment->status)
        ->toBe(
            Payment::STATUS_PROCESSING
        )
        ->and($payment->reconciliation_status)
        ->toBe(
            Payment::RECONCILIATION_MISMATCH
        )
        ->and($payment->provider_amount)
        ->toBe('4500.00');

    expect(
        $payment->invoice
            ?->receipt_number
    )->toBeNull();

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'event_type' => 'payment_reconciliation_mismatch',
        ]
    );
});

it('does not mark a payment paid when provider currency does not match local currency', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)(
        '5000.00'
    );

    $payload = [
        'event_id' => 'evt-currency-mismatch-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_PAID,
        'amount' => '5000.00',
        'currency' => 'USD',
    ];

    $response = ($this->sendWebhook)(
        $payload
    );

    $response->assertOk();

    $payment->refresh();

    expect($payment->status)
        ->toBe(
            Payment::STATUS_PROCESSING
        )
        ->and($payment->reconciliation_status)
        ->toBe(
            Payment::RECONCILIATION_MISMATCH
        )
        ->and($payment->provider_currency)
        ->toBe('USD');

    expect(
        $payment->invoice
            ?->receipt_number
    )->toBeNull();
});

it('handles duplicate provider callbacks idempotently', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)();

    $payload = [
        'event_id' => 'evt-idempotent-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_PAID,
        'amount' => '5000.00',
        'currency' => 'LKR',
    ];

    $firstResponse = ($this->sendWebhook)(
        $payload
    );

    $firstResponse
        ->assertOk()
        ->assertJson([
            'ok' => true,
        ]);

    $secondResponse = ($this->sendWebhook)(
        $payload
    );

    $secondResponse
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'duplicate' => true,
        ]);

    expect(
        PaymentEvent::query()
            ->where(
                'provider_event_id',
                'evt-idempotent-001'
            )
            ->count()
    )->toBe(1);

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_PAID);

    expect(
        $payment->invoice
            ?->receipt_number
    )->not->toBeNull();
});

it('records a failed provider callback without issuing a receipt', function (): void {
    [
        $client,
        $profile,
        $appointment,
        $payment,
    ] = ($this->makePendingPayment)();

    $payload = [
        'event_id' => 'evt-payment-failed-001',
        'payment_uuid' => $payment->uuid,
        'provider_payment_id' => $payment->provider_payment_id,
        'status' => Payment::STATUS_FAILED,
        'amount' => '5000.00',
        'currency' => 'LKR',
    ];

    $response = ($this->sendWebhook)(
        $payload
    );

    $response
        ->assertOk()
        ->assertJson([
            'ok' => true,
        ]);

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

    $this->assertDatabaseHas(
        'payment_events',
        [
            'payment_id' => $payment->id,
            'provider_event_id' => 'evt-payment-failed-001',
            'status' => 'processed',
        ]
    );
});
