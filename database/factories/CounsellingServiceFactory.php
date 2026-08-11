<?php

namespace Database\Factories;

use App\Models\CounsellingService;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CounsellingService>
 */
class CounsellingServiceFactory extends Factory
{
    protected $model = CounsellingService::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(
                1000,
                9999
            ),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'duration_minutes' => fake()->randomElement([
                30,
                45,
                60,
                90,
                120,
            ]),
            'service_mode' => fake()->randomElement([
                'online',
                'in_person',
                'both',
            ]),
            'target_age_group' => 'all_ages',
            'minimum_age' => null,
            'maximum_age' => null,
            'price' => fake()->randomFloat(2, 1000, 10000),
            'currency' => 'LKR',
            'display_order' => fake()->numberBetween(0, 20),
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }
}
