<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class FixedCmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Susadhya Counselling',
                'menu_label' => 'Home',
                'menu_order' => 10,
                'excerpt' => 'Professional counselling with care, privacy and respect.',
            ],
            [
                'slug' => 'about',
                'title' => 'About Susadhya',
                'menu_label' => 'About',
                'menu_order' => 20,
                'excerpt' => 'Learn about Susadhya Counselling and our approach to professional support.',
            ],
            [
                'slug' => 'services',
                'title' => 'Counselling Services',
                'menu_label' => 'Services',
                'menu_order' => 30,
                'excerpt' => 'Explore professional counselling services designed to support different needs and stages of life.',
            ],
            [
                'slug' => 'counsellors',
                'title' => 'Our Counsellors',
                'menu_label' => 'Counsellors',
                'menu_order' => 40,
                'excerpt' => 'Explore professional counsellor profiles and areas of support.',
            ],
            [
                'slug' => 'faq',
                'title' => 'Frequently Asked Questions',
                'menu_label' => 'FAQ',
                'menu_order' => 50,
                'excerpt' => 'Answers to common questions about using Susadhya Counselling.',
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Susadhya',
                'menu_label' => 'Contact',
                'menu_order' => 60,
                'excerpt' => 'Contact Susadhya Counselling for general information and support.',
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::firstOrCreate(
                [
                    'slug' => $page['slug'],
                ],
                [
                    'title' => $page['title'],

                    'menu_label' => $page['menu_label'],

                    'excerpt' => $page['excerpt'],

                    'template' => CmsPage::TEMPLATE_LANDING,

                    'status' => CmsPage::STATUS_PUBLISHED,

                    'show_in_header' => true,

                    'show_in_footer' => true,

                    'menu_order' => $page['menu_order'],

                    'robots_index' => true,

                    'robots_follow' => true,

                    'published_at' => now(),
                ]
            );
        }
    }
}
