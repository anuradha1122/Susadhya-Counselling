<?php

namespace Database\Factories;

use App\Models\CaseEscalation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseEscalation>
 */
class CaseEscalationFactory extends Factory
{
    protected $model = CaseEscalation::class;

    public function definition(): array
    {
        return [
            'case_reference' => fake()->bothify('CASE-####-????'),
            'reason_code' => CaseEscalation::REASON_COORDINATION,
            'priority' => CaseEscalation::PRIORITY_MEDIUM,
            'status' => CaseEscalation::STATUS_OPEN,
            'assigned_to' => null,
            'due_at' => now()->addDay(),
            'admin_note' => 'Administrative coordination required.',
            'resolution_notes' => null,
            'resolved_by' => null,
            'resolved_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
