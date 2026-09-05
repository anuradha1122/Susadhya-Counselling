<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        return [
            'group' => 'operations',
            'key' => fake()->unique()->slug(3),
            'label' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => SystemSetting::TYPE_TEXT,
            'value' => fake()->sentence(),
            'options' => null,
            'is_public' => false,
            'updated_by' => null,
        ];
    }
}
