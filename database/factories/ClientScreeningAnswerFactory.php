<?php

namespace Database\Factories;

use App\Models\ClientIntake;
use App\Models\ClientScreeningAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientScreeningAnswer>
 */
class ClientScreeningAnswerFactory extends Factory
{
    protected $model = ClientScreeningAnswer::class;

    public function definition(): array
    {
        return [
            'client_intake_id' => ClientIntake::factory(),
            'question_key' => fake()->unique()->slug(2),
            'question_text' => fake()->sentence(),
            'answer_score' => fake()->numberBetween(0, 3),
            'answer_value' => fake()->randomElement([
                'Not at all',
                'Several days',
                'More than half the days',
                'Nearly every day',
            ]),
            'answer_notes' => null,
        ];
    }
}
