<?php

namespace Database\Factories;

use App\Models\CmsFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsFaq>
 */
class CmsFaqFactory extends Factory
{
    protected $model =
        CmsFaq::class;

    public function definition(): array
    {
        return [
            'category' => 'General',

            'question' => fake()->sentence(),

            'answer' => fake()->paragraph(),

            'display_order' => 0,

            'is_active' => true,

            'published_at' => now(),
        ];
    }
}
