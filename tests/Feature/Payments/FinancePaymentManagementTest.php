<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingService;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Database\Seeders\PaymentPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

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

    $this->makeFinanceUser = function (): User {
        $user = User::factory()->create();

        $user->assignRole(
            'finance_admin'
        );

        return $user;
    };

    $this->makeClientWithAppointment = function (
        string $price = '5000.00'
    ): array {
        $client = User::factory()->create();

        $client->assignRole('client');

        $profile = ClientProfile::factory()->create([
            'user_id' => $client->id,
        ]);

        $service = CounsellingService::factory()->create([
            'price' => $price,
            'currency' => 'LKR',
        ]);

        $appointment = Appointment::factory()->create([
            'client_profile_id' => $profile->id,
            'counselling_service_id' => $service->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        return [
            $client,
            $profile,
            $appointment,
            $service,
        ];
    };
});

it('allows finance admin to view payment management', function (): void {
    $financeUser = ($this->makeFinanceUser)();

    [
        $client,
        $profile,
        $appointment,
    ] = ($this->makeClientWithAppointment)();

    $payment = app(
        PaymentService::class
    )->recordManual(
        $appointment,
        $financeUser,
        'CASH-TEST-001'
    );

    $response = $this
        ->actingAs($financeUser)
        ->get(
            route(
                'finance.payments.index'
            )
        );

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'Finance/Payments/Index'
                )
                ->has('payments.data', 1)
                ->where(
                    'payments.data.0.uuid',
                    $payment->uuid
                )
                ->where(
                    'payments.data.0.client_profile_id',
                    $profile->id
                )
        );
});

it('allows finance admin to record a manual payment', function (): void {
    $financeUser = ($this->makeFinanceUser)();

    [
        $client,
        $profile,
        $appointment,
    ] = ($this->makeClientWithAppointment)(
        '6500.00'
    );

    $response = $this
        ->actingAs($financeUser)
        ->post(
            route(
                'finance.payments.manual.store'
            ),
            [
                'appointment_id' => $appointment->id,
                'reference' => 'BANK-SLIP-2026-001',
                'paid_at' => now()
                    ->subMinute()
                    ->format('Y-m-d H:i:s'),
            ]
        );

    $response
        ->assertRedirect(
            route(
                'finance.payments.index'
            )
        )
        ->assertSessionHas(
            'success'
        );

    $payment = Payment::query()->firstOrFail();

    expect($payment->appointment_id)
        ->toBe($appointment->id)
        ->and($payment->client_profile_id)
        ->toBe($profile->id)
        ->and($payment->provider)
        ->toBe(Payment::PROVIDER_MANUAL)
        ->and($payment->method)
        ->toBe(Payment::METHOD_MANUAL)
        ->and($payment->provider_reference)
        ->toBe('BANK-SLIP-2026-001')
        ->and($payment->amount)
        ->toBe('6500.00')
        ->and($payment->currency)
        ->toBe('LKR')
        ->and($payment->status)
        ->toBe(Payment::STATUS_PAID)
        ->and($payment->reconciliation_status)
        ->toBe(Payment::RECONCILIATION_MATCHED);

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
            'actor_id' => $financeUser->id,
            'event_type' => 'manual_payment_recorded',
            'source' => 'finance',
        ]
    );
});

it('prevents an ordinary admin from accessing finance payment management', function (): void {
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();

    $admin->assignRole(
        $adminRole
    );

    $this
        ->actingAs($admin)
        ->get(
            route(
                'finance.payments.index'
            )
        )
        ->assertForbidden();
});

it('allows finance admin to reconcile a sandbox provider payment', function (): void {
    $financeUser = ($this->makeFinanceUser)();

    [
        $client,
        $profile,
        $appointment,
    ] = ($this->makeClientWithAppointment)();

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

    $response = $this
        ->actingAs($financeUser)
        ->patch(
            route(
                'finance.payments.reconcile',
                $payment
            )
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $payment->refresh();

    expect($payment->status)
        ->toBe(Payment::STATUS_PAID)
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
            'event_type' => 'payment_reconciled',
        ]
    );
});

it('allows finance admin to download invoices and receipts', function (): void {
    $financeUser = ($this->makeFinanceUser)();

    [
        $client,
        $profile,
        $appointment,
    ] = ($this->makeClientWithAppointment)();

    $payment = app(
        PaymentService::class
    )->recordManual(
        $appointment,
        $financeUser,
        'MANUAL-PAYMENT-002'
    );

    $invoiceResponse = $this
        ->actingAs($financeUser)
        ->get(
            route(
                'finance.payments.invoice',
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
        ->actingAs($financeUser)
        ->get(
            route(
                'finance.payments.receipt',
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

it('does not give finance admin access to counsellor clinical case routes', function (): void {
    $financeUser = ($this->makeFinanceUser)();

    $this
        ->actingAs($financeUser)
        ->get(
            route(
                'counsellor.cases.index'
            )
        )
        ->assertForbidden();
});
