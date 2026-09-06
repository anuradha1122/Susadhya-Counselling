<?php

namespace Database\Factories;

use App\Models\CmsPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CmsPage>
 */
class CmsPageFactory extends Factory
{
    protected $model = CmsPage::class;

    public function definition(): array
    {
        $title =
            fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title)
                .'-'
                .fake()->unique()->numberBetween(
                    100,
                    99999
                ),

            'menu_label' => fake()->words(
                2,
                true
            ),

            'excerpt' => fake()->sentence(),

            'body' => fake()->paragraphs(
                3,
                true
            ),

            'template' => CmsPage::TEMPLATE_STANDARD,

            'status' => CmsPage::STATUS_DRAFT,

            'show_in_header' => false,

            'show_in_footer' => false,

            'menu_order' => 0,

            'robots_index' => true,

            'robots_follow' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(
            fn (): array => [
                'status' => CmsPage::STATUS_PUBLISHED,

                'published_at' => now(),
            ]
        );
    }
}
