<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory()->paid(),
            'refund_number' => 'REF-TEST-'.Str::upper(
                Str::random(10)
            ),
            'requested_by' => User::factory(),
            'requested_amount' => '1000.00',
            'currency' => 'LKR',
            'reason' => fake()->sentence(),
            'status' => Refund::STATUS_REQUESTED,
            'requested_at' => now(),
        ];
    }
}
