<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'invoice_number' => 'INV-TEST-'.Str::upper(
                Str::random(10)
            ),
            'subtotal' => '5000.00',
            'total' => '5000.00',
            'currency' => 'LKR',
            'status' => Invoice::STATUS_ISSUED,
            'billing_name' => fake()->name(),
            'billing_email' => fake()->safeEmail(),
            'issued_at' => now(),
        ];
    }
}
