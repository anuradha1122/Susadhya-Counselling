<?php

namespace Database\Seeders;

use App\Models\CmsFaq;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\PublicSiteSetting;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedHomePage();
        $this->seedAboutPage();
        $this->seedContactPage();
        $this->seedLegalPages();
        $this->seedFaqs();
    }

    private function seedSettings(): void
    {
        if (
            PublicSiteSetting::query()
                ->exists()
        ) {
            return;
        }

        PublicSiteSetting::query()
            ->create([
                'site_name' => 'Susadhya Counselling',

                'tagline' => 'Professional Online Counselling',

                'logo_path' => '/images/brand/susadhya-logo.jpeg',

                'social_links' => [],

                'default_meta_title' => 'Susadhya Counselling | Professional Online Counselling',

                'default_meta_description' => 'Professional online counselling with confidential, compassionate support from qualified counsellors.',

                'default_og_image_path' => '/images/brand/susadhya-logo.jpeg',

                'footer_text' => 'Professional online counselling with care, privacy and respect.',

                'emergency_notice' => 'Susadhya Counselling is not an emergency response service. If you or someone else is in immediate danger, contact the appropriate local emergency service.',

                'booking_cta_label' => 'Find a Counsellor',

                'booking_cta_url' => '/counsellors',
            ]);
    }

    private function seedHomePage(): void
    {
        $page =
            CmsPage::query()
                ->firstOrCreate(
                    [
                        'slug' => 'home',
                    ],
                    [
                        'title' => 'Home',

                        'menu_label' => 'Home',

                        'template' => CmsPage::TEMPLATE_LANDING,

                        'status' => CmsPage::STATUS_PUBLISHED,

                        'show_in_header' => false,

                        'show_in_footer' => false,

                        'menu_order' => 0,

                        'meta_title' => 'Susadhya Counselling | Professional Online Counselling',

                        'meta_description' => 'Confidential and compassionate online counselling from qualified professionals.',

                        'published_at' => now(),
                    ]
                );

        $this->section(
            page: $page,
            key: 'hero',
            type: CmsSection::TYPE_HERO,
            heading: 'Professional support for a healthier mind.',
            subheading: 'Confidential online counselling that helps you connect with the right professional, wherever you are.',
            order: 10,
            content: [
                'eyebrow' => 'Professional Online Counselling',

                'primary_cta_label' => 'Find a Counsellor',

                'primary_cta_url' => '/counsellors',

                'secondary_cta_label' => 'Explore Services',

                'secondary_cta_url' => '/services',

                'image_path' => '/images/brand/susadhya-logo.jpeg',
            ]
        );

        $this->section(
            page: $page,
            key: 'trust-features',
            type: CmsSection::TYPE_FEATURE_GRID,
            heading: 'Counselling designed around trust and care.',
            subheading: 'A professional environment that respects confidentiality, choice and accessibility.',
            order: 20,
            content: [
                'items' => [
                    [
                        'title' => 'Confidential',
                        'description' => 'Your privacy is treated as a core part of the counselling experience.',
                        'icon' => 'shield',
                    ],
                    [
                        'title' => 'Professional',
                        'description' => 'Connect with qualified counsellors and clearly defined counselling services.',
                        'icon' => 'badge',
                    ],
                    [
                        'title' => 'Accessible',
                        'description' => 'Explore support options and book counselling through a simple online experience.',
                        'icon' => 'heart',
                    ],
                ],
            ]
        );

        $this->section(
            page: $page,
            key: 'services',
            type: CmsSection::TYPE_SERVICES,
            heading: 'Counselling services',
            subheading: 'Explore available professional support based on your needs.',
            order: 30,
            content: [
                'limit' => 6,
            ]
        );

        $this->section(
            page: $page,
            key: 'counsellors',
            type: CmsSection::TYPE_COUNSELLORS,
            heading: 'Meet our counsellors',
            subheading: 'Learn about available professionals before choosing who feels right for you.',
            order: 40,
            content: [
                'limit' => 4,
            ]
        );

        $this->section(
            page: $page,
            key: 'faq',
            type: CmsSection::TYPE_FAQ,
            heading: 'Frequently asked questions',
            subheading: 'A few useful things to know before getting started.',
            order: 50,
            content: [
                'limit' => 6,
            ]
        );

        $this->section(
            page: $page,
            key: 'cta',
            type: CmsSection::TYPE_CTA,
            heading: 'Ready to take the next step?',
            subheading: 'Explore counsellors and available services when you feel ready.',
            order: 60,
            content: [
                'primary_cta_label' => 'Find a Counsellor',

                'primary_cta_url' => '/counsellors',
            ]
        );
    }

    private function seedAboutPage(): void
    {
        CmsPage::query()
            ->firstOrCreate(
                [
                    'slug' => 'about',
                ],
                [
                    'title' => 'About Susadhya',

                    'menu_label' => 'About',

                    'excerpt' => 'Learn about Susadhya Counselling and our approach to professional online counselling.',

                    'body' => 'Susadhya Counselling is designed to make professional counselling easier to discover and access through a secure online platform. Our aim is to provide a respectful, confidential and well-structured experience for people seeking counselling support.',

                    'template' => CmsPage::TEMPLATE_STANDARD,

                    'status' => CmsPage::STATUS_PUBLISHED,

                    'show_in_header' => true,

                    'show_in_footer' => true,

                    'menu_order' => 20,

                    'meta_title' => 'About Susadhya Counselling',

                    'meta_description' => 'Learn about Susadhya Counselling and our approach to professional online counselling.',

                    'published_at' => now(),
                ]
            );
    }

    private function seedContactPage(): void
    {
        CmsPage::query()
            ->firstOrCreate(
                [
                    'slug' => 'contact',
                ],
                [
                    'title' => 'Contact Us',

                    'menu_label' => 'Contact',

                    'excerpt' => 'Find Susadhya Counselling contact information and office details.',

                    'body' => 'Use the contact information published on this page for general enquiries. Support and feedback workflows are managed separately from counselling records.',

                    'template' => CmsPage::TEMPLATE_STANDARD,

                    'status' => CmsPage::STATUS_PUBLISHED,

                    'show_in_header' => true,

                    'show_in_footer' => true,

                    'menu_order' => 60,

                    'meta_title' => 'Contact Susadhya Counselling',

                    'published_at' => now(),
                ]
            );
    }

    private function seedLegalPages(): void
    {
        CmsPage::query()
            ->firstOrCreate(
                [
                    'slug' => 'privacy-policy',
                ],
                [
                    'title' => 'Privacy Policy',

                    'menu_label' => 'Privacy Policy',

                    'body' => 'Draft only. Replace this text with the approved Susadhya Counselling privacy policy before publishing.',

                    'template' => CmsPage::TEMPLATE_LEGAL,

                    'status' => CmsPage::STATUS_DRAFT,

                    'show_in_footer' => true,

                    'menu_order' => 90,

                    'robots_index' => false,
                ]
            );

        CmsPage::query()
            ->firstOrCreate(
                [
                    'slug' => 'terms-and-conditions',
                ],
                [
                    'title' => 'Terms & Conditions',

                    'menu_label' => 'Terms & Conditions',

                    'body' => 'Draft only. Replace this text with approved legal terms before publishing.',

                    'template' => CmsPage::TEMPLATE_LEGAL,

                    'status' => CmsPage::STATUS_DRAFT,

                    'show_in_footer' => true,

                    'menu_order' => 100,

                    'robots_index' => false,
                ]
            );
    }

    private function seedFaqs(): void
    {
        $items = [
            [
                'question' => 'How do I choose a counsellor?',
                'answer' => 'You can review available counsellor profiles, their professional information and the counselling services they provide before choosing a suitable option.',
                'order' => 10,
            ],
            [
                'question' => 'How do I book a counselling appointment?',
                'answer' => 'Select an available counsellor and counselling service, choose an available time and follow the appointment booking steps.',
                'order' => 20,
            ],
            [
                'question' => 'Can I see my upcoming appointments online?',
                'answer' => 'Registered clients can view and manage their appointments from the secure client area, subject to the appointment rules of the platform.',
                'order' => 30,
            ],
            [
                'question' => 'Is Susadhya Counselling an emergency service?',
                'answer' => 'No. Susadhya Counselling is not an emergency response service. If you or another person is in immediate danger, contact the appropriate local emergency service.',
                'order' => 40,
            ],
        ];

        foreach ($items as $item) {
            CmsFaq::query()
                ->firstOrCreate(
                    [
                        'question' => $item[
                                'question'
                            ],
                    ],
                    [
                        'category' => 'General',

                        'answer' => $item[
                                'answer'
                            ],

                        'display_order' => $item[
                                'order'
                            ],

                        'is_active' => true,

                        'published_at' => now(),
                    ]
                );
        }
    }

    private function section(
        CmsPage $page,
        string $key,
        string $type,
        string $heading,
        ?string $subheading,
        int $order,
        array $content = [],
        array $settings = [],
    ): void {
        CmsSection::query()
            ->firstOrCreate(
                [
                    'cms_page_id' => $page->id,

                    'key' => $key,
                ],
                [
                    'type' => $type,

                    'heading' => $heading,

                    'subheading' => $subheading,

                    'content' => $content,

                    'settings' => $settings,

                    'display_order' => $order,

                    'is_active' => true,
                ]
            );
    }
}
