<?php

namespace Database\Factories;

use App\Models\OperationalException;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalException>
 */
class OperationalExceptionFactory extends Factory
{
    protected $model = OperationalException::class;

    public function definition(): array
    {
        return [
            'type' => OperationalException::TYPE_BOOKING,
            'priority' => OperationalException::PRIORITY_MEDIUM,
            'status' => OperationalException::STATUS_OPEN,
            'source_type' => 'appointment',
            'source_reference' => fake()->uuid(),
            'title' => fake()->sentence(5),
            'description' => fake()->sentence(),
            'assigned_to' => null,
            'due_at' => now()->addDay(),
            'resolution_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
