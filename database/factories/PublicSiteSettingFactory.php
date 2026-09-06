<?php

namespace Database\Factories;

use App\Models\PublicSiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicSiteSetting>
 */
class PublicSiteSettingFactory extends Factory
{
    protected $model =
        PublicSiteSetting::class;

    public function definition(): array
    {
        return [
            'site_name' => 'Susadhya Counselling',

            'tagline' => 'Professional Online Counselling',

            'logo_path' => '/images/brand/susadhya-logo.jpeg',

            'social_links' => [],

            'default_meta_title' => 'Susadhya Counselling',

            'default_meta_description' => 'Professional online counselling and confidential mental wellness support.',

            'footer_text' => 'Professional online counselling with care, privacy and respect.',

            'booking_cta_label' => 'Find a Counsellor',

            'booking_cta_url' => '/counsellors',
        ];
    }
}
