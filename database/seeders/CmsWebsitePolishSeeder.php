<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Database\Seeder;

class CmsWebsitePolishSeeder extends Seeder
{
    public function run(): void
    {
        $this->home();
        $this->about();
        $this->contact();
    }

    private function home(): void
    {
        $page =
            CmsPage::query()
                ->where(
                    'slug',
                    'home'
                )
                ->firstOrFail();

        $page->update([
            'title' => 'Professional Online Counselling',

            'excerpt' => 'Professional, confidential counselling support designed to help you move forward with greater clarity, balance and confidence.',

            'meta_title' => 'Susadhya Counselling | Professional Online Counselling',

            'meta_description' => 'Connect with professional counsellors through a secure online counselling platform designed around confidentiality, accessibility and compassionate support.',
        ]);

        $this->section(
            $page,
            'hero',
            CmsSection::TYPE_HERO,
            10,
            'Professional support for the moments that matter.',
            'Life can feel overwhelming, confusing or simply exhausting. Susadhya helps you connect with professional counsellors in a confidential online environment, giving you space to talk, understand what you are experiencing and move forward at your own pace.',
            [
                'eyebrow' => 'Professional Online Counselling',

                'primary_cta_label' => 'Find a Counsellor',

                'primary_cta_url' => '/counsellors',

                'secondary_cta_label' => 'Explore Services',

                'secondary_cta_url' => '/services',

                'visual' => 'session',

                'trust_items' => [
                    'Confidential support',
                    'Professional counsellors',
                    'Convenient online access',
                ],
            ]
        );

        $this->section(
            $page,
            'trust-stats',
            CmsSection::TYPE_STATS,
            20,
            null,
            null,
            [
                'items' => [
                    [
                        'value' => 'Private',
                        'label' => 'Confidential counselling environment',
                    ],
                    [
                        'value' => 'Flexible',
                        'label' => 'Online appointment options',
                    ],
                    [
                        'value' => 'Professional',
                        'label' => 'Structured counselling services',
                    ],
                    [
                        'value' => 'Accessible',
                        'label' => 'Support from wherever you are',
                    ],
                ],
            ]
        );

        $this->section(
            $page,
            'how-it-works',
            CmsSection::TYPE_STEPS,
            30,
            'Getting started can be simple.',
            'You do not need to have everything figured out before asking for support. Susadhya guides you through a clear process from exploring available help to meeting your counsellor.',
            [
                'items' => [
                    [
                        'number' => '01',
                        'title' => 'Explore the support available',
                        'description' => 'Read about counselling services and browse public counsellor profiles to understand the options available to you.',
                    ],
                    [
                        'number' => '02',
                        'title' => 'Choose the right counsellor',
                        'description' => 'Review professional information, areas of support and available counselling services before making your choice.',
                    ],
                    [
                        'number' => '03',
                        'title' => 'Book a convenient appointment',
                        'description' => 'Sign in securely, select an available appointment and complete the required booking steps.',
                    ],
                    [
                        'number' => '04',
                        'title' => 'Begin your counselling journey',
                        'description' => 'Attend your session in a professional environment designed around privacy, respect and continuity of care.',
                    ],
                ],
            ]
        );

        $this->section(
            $page,
            'services',
            CmsSection::TYPE_SERVICES,
            40,
            'Support shaped around different needs.',
            'People seek counselling for many different reasons. Explore the professional counselling services currently available through Susadhya.',
            [
                'limit' => 6,
            ]
        );

        $this->section(
            $page,
            'why-counselling',
            CmsSection::TYPE_IMAGE_TEXT,
            50,
            'You do not have to carry everything alone.',
            'Counselling provides a structured and supportive space where you can speak openly, understand difficult experiences and explore healthier ways of responding to life.',
            [
                'eyebrow' => 'A space to understand and grow',

                'visual' => 'journey',

                'image_position' => 'left',

                'body' => 'You may be dealing with stress, relationship difficulties, uncertainty, emotional exhaustion, major life changes or concerns that are simply difficult to explain to the people around you. Professional counselling gives you dedicated time to explore those experiences without pressure to pretend that everything is fine.',

                'points' => [
                    'Talk openly in a respectful environment.',
                    'Understand patterns in thoughts, emotions and behaviour.',
                    'Develop practical ways to manage difficult situations.',
                    'Build healthier coping strategies and personal awareness.',
                    'Work toward realistic goals at a pace that feels manageable.',
                ],
            ]
        );

        $this->section(
            $page,
            'counsellors',
            CmsSection::TYPE_COUNSELLORS,
            60,
            'Meet the professionals behind the support.',
            'Choosing a counsellor is personal. Explore public professional profiles and find someone whose experience and approach feel appropriate for your needs.',
            [
                'limit' => 4,
            ]
        );

        $this->section(
            $page,
            'privacy',
            CmsSection::TYPE_IMAGE_TEXT,
            70,
            'Your privacy is part of the counselling experience.',
            'People can only speak honestly when they feel safe. Susadhya is designed with clear access boundaries and privacy-conscious workflows around counselling information.',
            [
                'eyebrow' => 'Privacy by design',

                'visual' => 'privacy',

                'image_position' => 'right',

                'body' => 'Clinical information is treated differently from ordinary administrative data. Access is restricted according to professional responsibilities, while sensitive activities and records follow controlled workflows across the platform.',

                'points' => [
                    'Counselling information is not exposed on public profiles.',
                    'Clients access their own information through authenticated areas.',
                    'Clinical and financial access remain separated.',
                    'Sensitive platform activity can be audited.',
                    'Privacy requests are handled through a dedicated compliance workflow.',
                ],
            ]
        );

        $this->section(
            $page,
            'testimonials',
            CmsSection::TYPE_TESTIMONIALS,
            80,
            'Experiences shared with permission.',
            'Only testimonials with explicit publication consent can appear on the Susadhya public website.',
            [
                'limit' => 6,
            ]
        );

        $this->section(
            $page,
            'faq',
            CmsSection::TYPE_FAQ,
            90,
            'Questions before you begin?',
            'It is completely normal to want to understand how counselling and the platform work before deciding what to do next.',
            [
                'limit' => 6,
            ]
        );

        $this->section(
            $page,
            'cta',
            CmsSection::TYPE_CTA,
            100,
            'A conversation can be the beginning of change.',
            'Take your time, explore the available support and choose the next step when you feel ready.',
            [
                'primary_cta_label' => 'Find a Counsellor',

                'primary_cta_url' => '/counsellors',
            ]
        );
    }

    private function about(): void
    {
        $page =
            CmsPage::query()
                ->where(
                    'slug',
                    'about'
                )
                ->firstOrFail();

        $page->update([
            'excerpt' => 'Susadhya is a professional online counselling platform designed to make finding and accessing counselling support clearer, safer and more convenient.',

            'body' => 'Seeking counselling should not feel confusing or intimidating. Susadhya brings together professional counselling services, counsellor discovery, appointment management and secure client workflows in one structured platform.

Our aim is not to replace the human relationship at the centre of counselling. Technology should reduce unnecessary barriers so that clients and counsellors can focus on the part that actually matters: meaningful professional support.',

            'meta_description' => 'Learn about Susadhya Counselling, our approach to professional online counselling, privacy and accessible mental wellness support.',
        ]);

        $this->section(
            $page,
            'mission',
            CmsSection::TYPE_IMAGE_TEXT,
            10,
            'Making professional support easier to reach.',
            'Susadhya was created around a simple idea: people seeking counselling deserve a clear, respectful and professionally structured path to support.',
            [
                'eyebrow' => 'Our purpose',

                'visual' => 'session',

                'image_position' => 'right',

                'body' => 'The platform helps people explore counselling services, understand counsellor profiles and manage appointments while preserving important boundaries around private and clinical information.',

                'points' => [
                    'Make professional counselling easier to discover.',
                    'Reduce unnecessary administrative barriers.',
                    'Support confidentiality and responsible information access.',
                    'Give clients clearer choices about available support.',
                    'Provide counsellors with structured digital workflows.',
                ],
            ]
        );

        $this->section(
            $page,
            'values',
            CmsSection::TYPE_FEATURE_GRID,
            20,
            'The principles behind Susadhya.',
            'Technology should support good counselling practice rather than getting in the way of it.',
            [
                'items' => [
                    [
                        'title' => 'Respect',
                        'description' => 'Every client deserves to be treated with dignity, empathy and without unnecessary judgement.',
                        'icon' => 'heart',
                    ],
                    [
                        'title' => 'Confidentiality',
                        'description' => 'Privacy and responsible access to sensitive information are fundamental to trust.',
                        'icon' => 'shield',
                    ],
                    [
                        'title' => 'Professionalism',
                        'description' => 'Clear roles, accountable workflows and professional boundaries guide how the platform operates.',
                        'icon' => 'badge',
                    ],
                ],
            ]
        );

        $this->section(
            $page,
            'about-cta',
            CmsSection::TYPE_CTA,
            30,
            'Explore support when you are ready.',
            'Browse available counselling services or learn more about the professionals available through Susadhya.',
            [
                'primary_cta_label' => 'Meet Our Counsellors',

                'primary_cta_url' => '/counsellors',
            ]
        );
    }

    private function contact(): void
    {
        $page =
            CmsPage::query()
                ->where(
                    'slug',
                    'contact'
                )
                ->firstOrFail();

        $page->update([
            'excerpt' => 'Contact Susadhya for general platform and service enquiries. Counselling records and sensitive personal information should only be handled through the secure authenticated areas of the platform.',

            'body' => 'For general questions about Susadhya, available services or using the platform, use the contact information shown here.

Please do not send counselling notes, detailed medical information, passwords, payment credentials or other highly sensitive information through ordinary email or messaging channels.',

            'meta_description' => 'Contact Susadhya Counselling for general enquiries about the online counselling platform and available services.',
        ]);
    }

    private function section(
        CmsPage $page,
        string $key,
        string $type,
        int $order,
        ?string $heading,
        ?string $subheading,
        array $content = [],
        array $settings = [],
    ): void {
        CmsSection::query()
            ->updateOrCreate(
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
