<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'client_profile_id' => ClientProfile::factory(),
            'provider' => 'sandbox',
            'method' => Payment::METHOD_GATEWAY,
            'provider_payment_id' => 'sbx_'.Str::lower(
                Str::random(24)
            ),
            'provider_reference' => 'SBX-'.Str::upper(
                Str::random(10)
            ),
            'idempotency_key' => (string) Str::uuid(),
            'amount' => '5000.00',
            'currency' => 'LKR',
            'status' => Payment::STATUS_PENDING,
            'reconciliation_status' => Payment::RECONCILIATION_PENDING,
        ];
    }

    public function paid(): static
    {
        return $this->state(
            fn (): array => [
                'status' => Payment::STATUS_PAID,
                'provider_status' => Payment::STATUS_PAID,
                'provider_amount' => '5000.00',
                'provider_currency' => 'LKR',
                'reconciliation_status' => Payment::RECONCILIATION_MATCHED,
                'paid_at' => now(),
                'reconciled_at' => now(),
            ]
        );
    }
}
