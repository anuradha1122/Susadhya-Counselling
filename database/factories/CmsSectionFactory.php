<?php

namespace Database\Factories;

use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsSection>
 */
class CmsSectionFactory extends Factory
{
    protected $model =
        CmsSection::class;

    public function definition(): array
    {
        return [
            'cms_page_id' => CmsPage::factory(),

            'key' => fake()
                ->unique()
                ->slug(),

            'type' => CmsSection::TYPE_RICH_TEXT,

            'heading' => fake()->sentence(4),

            'subheading' => fake()->sentence(),

            'content' => [
                'body' => fake()->paragraphs(
                    2,
                    true
                ),
            ],

            'settings' => [],

            'display_order' => 0,

            'is_active' => true,
        ];
    }
}
