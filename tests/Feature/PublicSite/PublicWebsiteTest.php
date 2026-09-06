<?php

use App\Models\CmsFaq;
use App\Models\CmsPage;
use App\Models\CmsTestimonial;
use App\Models\CounsellingService;
use App\Models\CounsellorProfile;
use App\Models\PublicSiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $pages = [
        'home' => 'Susadhya Counselling',
        'about' => 'About Susadhya',
        'services' => 'Counselling Services',
        'counsellors' => 'Our Counsellors',
        'faq' => 'Frequently Asked Questions',
        'contact' => 'Contact Susadhya',
    ];

    $order = 10;

    foreach ($pages as $slug => $title) {
        CmsPage::query()->create([
            'title' => $title,
            'slug' => $slug,
            'menu_label' => $title,
            'excerpt' => "{$title} page.",
            'template' => CmsPage::TEMPLATE_LANDING,
            'status' => CmsPage::STATUS_PUBLISHED,
            'show_in_header' => true,
            'show_in_footer' => true,
            'menu_order' => $order,
            'robots_index' => true,
            'robots_follow' => true,
            'published_at' => now(),
        ]);

        $order += 10;
    }

    PublicSiteSetting::factory()->create();
});

it('renders all canonical public website pages', function (): void {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/Home')
        );

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/About')
        );

    $this->get(route('public.services.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/Services/Index')
        );

    $this->get(route('public.counsellors.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/Counsellors/Index')
        );

    $this->get(route('public.faq'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/Faq')
        );

    $this->get(route('public.contact'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Public/Contact')
        );
});

it('rejects legacy generic routes for fixed website pages', function (): void {
    foreach ([
        'home',
        'about',
        'services',
        'counsellors',
        'faq',
        'contact',
    ] as $slug) {
        $this->get("/pages/{$slug}")
            ->assertNotFound();
    }
});

it('uses service slugs instead of numeric ids in public urls', function (): void {
    $service = CounsellingService::factory()->create([
        'status' => 'active',
    ]);

    $this->get(
        route(
            'public.services.show',
            $service->slug
        )
    )->assertOk();

    $this->get(
        "/services/{$service->id}"
    )->assertNotFound();
});

it('uses counsellor uuids instead of numeric ids in public urls', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
        'profile_photo_path' => 'counsellors/profile-photos/1/profile.jpg',
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    expect($counsellor->uuid)
        ->not->toBeNull();

    $this->get(
        route(
            'public.counsellors.show',
            $counsellor->uuid
        )
    )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'Public/Counsellors/Show'
                )
                ->where(
                    'counsellor.uuid',
                    $counsellor->uuid
                )
                ->where(
                    'counsellor.name',
                    $user->name
                )
                ->where(
                    'counsellor.profile_image_url',
                    $user->profile_photo_url
                )
        );

    $this->get(
        "/counsellors/{$counsellor->id}"
    )->assertNotFound();
});

it('only exposes visible faqs on the public faq page', function (): void {
    CmsFaq::query()->create([
        'category' => 'General',
        'question' => 'Visible FAQ?',
        'answer' => 'Visible answer.',
        'display_order' => 1,
        'is_active' => true,
        'published_at' => now(),
    ]);

    CmsFaq::query()->create([
        'category' => 'General',
        'question' => 'Hidden FAQ?',
        'answer' => 'Hidden answer.',
        'display_order' => 2,
        'is_active' => false,
        'published_at' => null,
    ]);

    $response = $this->get(
        route('public.faq')
    );

    $response->assertOk();

    $response->assertSee(
        'Visible FAQ?',
        false
    );

    $response->assertDontSee(
        'Hidden FAQ?',
        false
    );
});

it('only exposes consent approved testimonials', function (): void {
    CmsTestimonial::factory()->create([
        'display_name' => 'Approved Client',
        'quote' => 'Approved testimonial.',
        'is_active' => true,
        'consent_confirmed' => true,
        'consent_confirmed_at' => now(),
        'published_at' => now(),
    ]);

    CmsTestimonial::factory()->create([
        'display_name' => 'Unapproved Client',
        'quote' => 'Unapproved testimonial.',
        'is_active' => true,
        'consent_confirmed' => false,
        'consent_confirmed_at' => null,
        'published_at' => now(),
    ]);

    $response = $this->get(
        route('public.home')
    );

    $response->assertOk();

    $response->assertSee(
        'Approved testimonial.',
        false
    );

    $response->assertDontSee(
        'Unapproved testimonial.',
        false
    );
});

it('publishes canonical routes in the sitemap', function (): void {
    $service = CounsellingService::factory()->create([
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->get(
        route('public.sitemap')
    );

    $response->assertOk();

    $response->assertHeader(
        'Content-Type',
        'application/xml; charset=UTF-8'
    );

    $response->assertSee(
        route('public.home'),
        false
    );

    $response->assertSee(
        route('public.about'),
        false
    );

    $response->assertSee(
        route('public.services.index'),
        false
    );

    $response->assertSee(
        route('public.counsellors.index'),
        false
    );

    $response->assertSee(
        route('public.faq'),
        false
    );

    $response->assertSee(
        route('public.contact'),
        false
    );

    $response->assertSee(
        route(
            'public.services.show',
            $service->slug
        ),
        false
    );

    $response->assertSee(
        route(
            'public.counsellors.show',
            $counsellor->uuid
        ),
        false
    );

    $response->assertDontSee(
        '/pages/services',
        false
    );

    $response->assertDontSee(
        '/pages/counsellors',
        false
    );

    $response->assertDontSee(
        '/pages/faq',
        false
    );
});

it('publishes public robots rules and sitemap location', function (): void {
    $response = $this->get(
        route('public.robots')
    );

    $response
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'text/plain; charset=UTF-8'
        )
        ->assertSee(
            'User-agent: *',
            false
        )
        ->assertSee(
            'Disallow: /admin/',
            false
        )
        ->assertSee(
            'Disallow: /client/',
            false
        )
        ->assertSee(
            route('public.sitemap'),
            false
        );
});
