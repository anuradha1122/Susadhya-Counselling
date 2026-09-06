<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fixed public website structure
    |--------------------------------------------------------------------------
    |
    | These sections are defined by the application, not by CMS users.
    | Administrators can edit content and images only.
    |
    */

    'pages' => [
        'home' => [
            'label' => 'Home',
            'title' => 'Professional Online Counselling',

            'sections' => [
                'hero' => [
                    'label' => 'Hero Banner',
                    'type' => 'hero',

                    'heading' => 'Professional support for the moments that matter.',

                    'subheading' => 'Life can feel overwhelming, confusing or simply exhausting. Susadhya helps you connect with professional counsellors in a confidential online environment, giving you space to talk, understand what you are experiencing and move forward at your own pace.',

                    'content' => [
                        'eyebrow' => 'Professional Online Counselling',

                        'image_path' => 'https://images.pexels.com/photos/7579315/pexels-photo-7579315.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Professional counselling session',

                        'primary_cta_label' => 'Find a Counsellor',

                        'primary_cta_url' => '/counsellors',

                        'secondary_cta_label' => 'Explore Services',

                        'secondary_cta_url' => '/services',

                        'trust_items' => [
                            'Confidential support',
                            'Professional counsellors',
                            'Convenient online access',
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/7579315/pexels-photo-7579315.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],

                        [
                            'key' => 'primary_cta_label',
                            'type' => 'text',
                            'label' => 'Primary button text',
                        ],

                        [
                            'key' => 'primary_cta_url',
                            'type' => 'text',
                            'label' => 'Primary button link',
                        ],

                        [
                            'key' => 'secondary_cta_label',
                            'type' => 'text',
                            'label' => 'Secondary button text',
                        ],

                        [
                            'key' => 'secondary_cta_url',
                            'type' => 'text',
                            'label' => 'Secondary button link',
                        ],

                        [
                            'key' => 'trust_items',
                            'type' => 'list',
                            'label' => 'Trust points',
                        ],
                    ],
                ],

                'intro' => [
                    'label' => 'Why Susadhya',
                    'type' => 'image_text',

                    'heading' => 'Professional support should feel easier to reach.',

                    'subheading' => 'Taking the first step toward counselling can feel difficult. Susadhya is designed to make finding professional support clearer, safer and more convenient.',

                    'content' => [
                        'image_path' => 'https://images.pexels.com/photos/7176292/pexels-photo-7176292.jpeg?auto=compress&cs=tinysrgb&w=1400',

                        'image_alt' => 'Counsellor listening during a professional session',

                        'body' => 'You can explore available counselling services, learn about professional counsellors and manage appointments through one structured platform. The technology stays in the background so that the counselling relationship can remain at the centre of the experience.',

                        'points' => [
                            'Explore professional support before making a decision.',
                            'Choose a counsellor based on appropriate public professional information.',
                            'Manage appointments through a secure client account.',
                            'Keep sensitive counselling information within controlled workflows.',
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Section image',
                            'default' => 'https://images.pexels.com/photos/7176292/pexels-photo-7176292.jpeg?auto=compress&cs=tinysrgb&w=1400',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],

                        [
                            'key' => 'body',
                            'type' => 'textarea',
                            'label' => 'Main text',
                        ],

                        [
                            'key' => 'points',
                            'type' => 'list',
                            'label' => 'Supporting points',
                        ],
                    ],
                ],

                'how_it_works' => [
                    'label' => 'How It Works',
                    'type' => 'steps',

                    'heading' => 'Getting started can be simple.',

                    'subheading' => 'You do not need to have everything figured out before asking for support. Susadhya gives you a clear path from exploring available help to attending your counselling session.',

                    'content' => [
                        'items' => [
                            [
                                'number' => '01',
                                'title' => 'Explore support',
                                'description' => 'Learn about available counselling services and the kinds of support offered through Susadhya.',
                            ],

                            [
                                'number' => '02',
                                'title' => 'Find a counsellor',
                                'description' => 'Review public professional profiles and choose someone whose experience and services fit your needs.',
                            ],

                            [
                                'number' => '03',
                                'title' => 'Book your appointment',
                                'description' => 'Sign in securely, review available times and reserve an appointment that works for you.',
                            ],

                            [
                                'number' => '04',
                                'title' => 'Begin your journey',
                                'description' => 'Attend your session in a professional environment built around privacy, respect and continuity of care.',
                            ],
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'items',
                            'type' => 'repeater',
                            'label' => 'Steps',

                            'fields' => [
                                [
                                    'key' => 'number',
                                    'type' => 'text',
                                    'label' => 'Number',
                                ],

                                [
                                    'key' => 'title',
                                    'type' => 'text',
                                    'label' => 'Title',
                                ],

                                [
                                    'key' => 'description',
                                    'type' => 'textarea',
                                    'label' => 'Description',
                                ],
                            ],
                        ],
                    ],
                ],

                'services' => [
                    'label' => 'Services Section',
                    'type' => 'services',

                    'heading' => 'Support shaped around different needs.',

                    'subheading' => 'People seek counselling for many different reasons. Explore professional counselling services currently available through Susadhya.',

                    'content' => [
                        'limit' => 6,
                    ],

                    'fields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'label' => 'Number of services to show',
                            'min' => 1,
                            'max' => 12,
                        ],
                    ],
                ],

                'why_counselling' => [
                    'label' => 'Why Counselling',
                    'type' => 'image_text',

                    'heading' => 'You do not have to carry everything alone.',

                    'subheading' => 'Counselling provides a structured and supportive space where you can speak openly, understand difficult experiences and explore healthier ways of responding to life.',

                    'content' => [
                        'image_path' => 'https://images.pexels.com/photos/5699476/pexels-photo-5699476.jpeg?auto=compress&cs=tinysrgb&w=1400',

                        'image_alt' => 'Counsellor speaking with a client',

                        'body' => 'You may be dealing with stress, relationship difficulties, uncertainty, emotional exhaustion, major life changes or concerns that are simply difficult to explain to people around you. Professional counselling gives you dedicated time to explore those experiences without pressure to pretend that everything is fine.',

                        'points' => [
                            'Talk openly in a respectful professional environment.',
                            'Understand patterns in thoughts, emotions and behaviour.',
                            'Develop practical ways to handle difficult situations.',
                            'Build healthier coping strategies and self-awareness.',
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Section image',
                            'default' => 'https://images.pexels.com/photos/5699476/pexels-photo-5699476.jpeg?auto=compress&cs=tinysrgb&w=1400',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],

                        [
                            'key' => 'body',
                            'type' => 'textarea',
                            'label' => 'Main text',
                        ],

                        [
                            'key' => 'points',
                            'type' => 'list',
                            'label' => 'Benefits',
                        ],
                    ],
                ],

                'counsellors' => [
                    'label' => 'Counsellors Section',
                    'type' => 'counsellors',

                    'heading' => 'Meet the professionals behind the support.',

                    'subheading' => 'Choosing a counsellor is personal. Explore professional profiles and find someone whose experience and services feel appropriate for your needs.',

                    'content' => [
                        'limit' => 4,
                    ],

                    'fields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'label' => 'Number of counsellors to show',
                            'min' => 1,
                            'max' => 12,
                        ],
                    ],
                ],

                'privacy' => [
                    'label' => 'Privacy & Confidentiality',
                    'type' => 'image_text',

                    'heading' => 'Your privacy is part of the counselling experience.',

                    'subheading' => 'People can only speak honestly when they feel safe. Susadhya uses clear access boundaries and privacy-conscious workflows around counselling information.',

                    'content' => [
                        'image_path' => 'https://images.pexels.com/photos/9065264/pexels-photo-9065264.jpeg?auto=compress&cs=tinysrgb&w=1400',

                        'image_alt' => 'Private counselling conversation',

                        'body' => 'Clinical information is treated differently from ordinary administrative information. Access is restricted according to professional responsibilities, while sensitive actions and records follow controlled workflows across the platform.',

                        'points' => [
                            'Counselling narratives are never exposed through public profiles.',
                            'Clients access private information through authenticated areas.',
                            'Clinical and financial responsibilities remain separated.',
                            'Sensitive platform activity can be audited.',
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Section image',
                            'default' => 'https://images.pexels.com/photos/9065264/pexels-photo-9065264.jpeg?auto=compress&cs=tinysrgb&w=1400',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],

                        [
                            'key' => 'body',
                            'type' => 'textarea',
                            'label' => 'Main text',
                        ],

                        [
                            'key' => 'points',
                            'type' => 'list',
                            'label' => 'Privacy points',
                        ],
                    ],
                ],

                'testimonials' => [
                    'label' => 'Testimonials',
                    'type' => 'testimonials',

                    'heading' => 'Experiences shared with permission.',

                    'subheading' => 'Only testimonials with explicit publication consent can appear on the Susadhya public website.',

                    'content' => [
                        'limit' => 6,
                    ],

                    'fields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'label' => 'Number of testimonials to show',
                            'min' => 1,
                            'max' => 12,
                        ],
                    ],
                ],

                'faq' => [
                    'label' => 'FAQ Preview',
                    'type' => 'faq',

                    'heading' => 'Questions before you begin?',

                    'subheading' => 'It is normal to want to understand how counselling and the platform work before deciding what to do next.',

                    'content' => [
                        'limit' => 6,
                    ],

                    'fields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'label' => 'Number of FAQs to show',
                            'min' => 1,
                            'max' => 12,
                        ],
                    ],
                ],

                'cta' => [
                    'label' => 'Final Call to Action',
                    'type' => 'cta',

                    'heading' => 'A conversation can be the beginning of change.',

                    'subheading' => 'Take your time, explore the available support and choose the next step when you feel ready.',

                    'content' => [
                        'primary_cta_label' => 'Find a Counsellor',

                        'primary_cta_url' => '/counsellors',
                    ],

                    'fields' => [
                        [
                            'key' => 'primary_cta_label',
                            'type' => 'text',
                            'label' => 'Button text',
                        ],

                        [
                            'key' => 'primary_cta_url',
                            'type' => 'text',
                            'label' => 'Button link',
                        ],
                    ],
                ],
            ],
        ],

        'about' => [
            'label' => 'About',
            'title' => 'About Susadhya',

            'sections' => [
                'hero' => [
                    'label' => 'About Hero',
                    'type' => 'hero',

                    'heading' => 'Making professional support easier to reach.',

                    'subheading' => 'Susadhya is a professional online counselling platform designed to make finding and accessing counselling support clearer, safer and more convenient.',

                    'content' => [
                        'eyebrow' => 'About Susadhya',

                        'image_path' => 'https://images.pexels.com/photos/3958375/pexels-photo-3958375.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Professional counselling conversation',
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/3958375/pexels-photo-3958375.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],
                    ],
                ],

                'mission' => [
                    'label' => 'Our Purpose',
                    'type' => 'image_text',

                    'heading' => 'Technology should make counselling easier to access, not more complicated.',

                    'subheading' => 'Susadhya brings together professional counselling discovery, appointments and secure client workflows in one structured platform.',

                    'content' => [
                        'image_path' => 'https://images.pexels.com/photos/23496505/pexels-photo-23496505.jpeg?auto=compress&cs=tinysrgb&w=1400',

                        'image_alt' => 'Professional therapist and client',

                        'body' => 'Our aim is not to replace the human relationship at the centre of counselling. The platform reduces unnecessary administrative barriers so clients and counsellors can focus on meaningful professional support.',

                        'points' => [
                            'Clear pathways to professional counselling support.',
                            'Professional boundaries between clinical, administrative and financial responsibilities.',
                            'Privacy-conscious handling of sensitive information.',
                            'Structured appointment and counselling workflows.',
                        ],
                    ],

                    'fields' => [
                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Section image',
                            'default' => 'https://images.pexels.com/photos/23496505/pexels-photo-23496505.jpeg?auto=compress&cs=tinysrgb&w=1400',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],

                        [
                            'key' => 'body',
                            'type' => 'textarea',
                            'label' => 'Main text',
                        ],

                        [
                            'key' => 'points',
                            'type' => 'list',
                            'label' => 'Key points',
                        ],
                    ],
                ],

                'cta' => [
                    'label' => 'About CTA',
                    'type' => 'cta',

                    'heading' => 'Explore support when you are ready.',

                    'subheading' => 'Learn about available counselling services or meet the professionals providing support through Susadhya.',

                    'content' => [
                        'primary_cta_label' => 'Meet Our Counsellors',

                        'primary_cta_url' => '/counsellors',
                    ],

                    'fields' => [
                        [
                            'key' => 'primary_cta_label',
                            'type' => 'text',
                            'label' => 'Button text',
                        ],

                        [
                            'key' => 'primary_cta_url',
                            'type' => 'text',
                            'label' => 'Button link',
                        ],
                    ],
                ],
            ],
        ],

        'services' => [
            'label' => 'Services',
            'title' => 'Counselling Services',

            'sections' => [
                'hero' => [
                    'label' => 'Services Hero',
                    'type' => 'hero',

                    'heading' => 'Counselling support for different moments in life.',

                    'subheading' => 'There is no single reason people seek counselling. Explore professional services and learn what each option offers before deciding what feels appropriate for you.',

                    'content' => [
                        'eyebrow' => 'Professional Support',

                        'image_path' => 'https://images.pexels.com/photos/4101156/pexels-photo-4101156.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Professional counselling session',
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/4101156/pexels-photo-4101156.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],
                    ],
                ],
            ],
        ],

        'counsellors' => [
            'label' => 'Counsellors',
            'title' => 'Find a Counsellor',

            'sections' => [
                'hero' => [
                    'label' => 'Counsellors Hero',
                    'type' => 'hero',

                    'heading' => 'A good counselling relationship starts with the right connection.',

                    'subheading' => 'Explore public professional profiles and choose a counsellor whose experience, services and approach feel appropriate for your needs.',

                    'content' => [
                        'eyebrow' => 'Find the Right Professional',

                        'image_path' => 'https://images.pexels.com/photos/9064712/pexels-photo-9064712.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Therapist speaking with a client',
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/9064712/pexels-photo-9064712.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],
                    ],
                ],
            ],
        ],

        'faq' => [
            'label' => 'FAQ',
            'title' => 'Frequently Asked Questions',

            'sections' => [
                'hero' => [
                    'label' => 'FAQ Hero',
                    'type' => 'hero',

                    'heading' => 'Questions are part of taking the first step.',

                    'subheading' => 'Find answers to common questions about counselling, appointments, privacy and using Susadhya.',

                    'content' => [
                        'eyebrow' => 'Frequently Asked Questions',

                        'image_path' => 'https://images.pexels.com/photos/7579183/pexels-photo-7579183.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Professional counselling discussion',
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/7579183/pexels-photo-7579183.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],
                    ],
                ],
            ],
        ],

        'contact' => [
            'label' => 'Contact',
            'title' => 'Contact Susadhya',

            'sections' => [
                'hero' => [
                    'label' => 'Contact Hero',
                    'type' => 'hero',

                    'heading' => 'We are here to help you find the right next step.',

                    'subheading' => 'Contact Susadhya for general questions about the platform and available services. Sensitive counselling information should remain within the secure authenticated areas of the system.',

                    'content' => [
                        'eyebrow' => 'Contact Susadhya',

                        'image_path' => 'https://images.pexels.com/photos/14797780/pexels-photo-14797780.jpeg?auto=compress&cs=tinysrgb&w=1800',

                        'image_alt' => 'Professional counselling office',
                    ],

                    'fields' => [
                        [
                            'key' => 'eyebrow',
                            'type' => 'text',
                            'label' => 'Small heading',
                        ],

                        [
                            'key' => 'image_path',
                            'type' => 'image',
                            'label' => 'Hero image',
                            'default' => 'https://images.pexels.com/photos/14797780/pexels-photo-14797780.jpeg?auto=compress&cs=tinysrgb&w=1800',
                        ],

                        [
                            'key' => 'image_alt',
                            'type' => 'text',
                            'label' => 'Image description',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
