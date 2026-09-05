<?php

namespace Database\Factories;

use App\Models\ContentSnippet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentSnippet>
 */
class ContentSnippetFactory extends Factory
{
    protected $model = ContentSnippet::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'placement' => 'admin_operations',
            'status' => ContentSnippet::STATUS_DRAFT,
            'published_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
