<?php

namespace Database\Factories;

use App\Models\CmsTestimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsTestimonial>
 */
class CmsTestimonialFactory extends Factory
{
    protected $model =
        CmsTestimonial::class;

    public function definition(): array
    {
        return [
            'display_name' => fake()->firstName(),

            'role_label' => 'Client',

            'quote' => fake()->paragraph(),

            'rating' => 5,

            'is_featured' => false,

            'is_active' => false,

            'display_order' => 0,

            'consent_confirmed' => false,

            'consent_confirmed_at' => null,

            'published_at' => null,
        ];
    }
}
