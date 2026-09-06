<?php

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();

    foreach ([
        'cms.view',
        'cms.pages.manage',
        'cms.sections.manage',
    ] as $permission) {
        Permission::findOrCreate(
            $permission,
            'web'
        );
    }

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

    $this->cmsManager = User::factory()->create([
        'is_active' => true,
    ]);

    $this->cmsManager->givePermissionTo([
        'cms.view',
        'cms.pages.manage',
        'cms.sections.manage',
    ]);
});

it('redirects guests from cms management', function (): void {
    $this->get(
        route('admin.cms.dashboard')
    )->assertRedirect(
        route('login')
    );
});

it('prevents users without cms view permission from accessing cms', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(
            route('admin.cms.dashboard')
        )
        ->assertForbidden();
});

it('allows authorized users to view fixed cms content', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->get(
            route(
                'admin.cms.content.index'
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'Admin/Cms/Content/Index'
                )
                ->has('pages', 6)
        );

    $this
        ->actingAs($this->cmsManager)
        ->get(
            route(
                'admin.cms.content.edit',
                'home'
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'Admin/Cms/Content/Edit'
                )
                ->where(
                    'pageDefinition.slug',
                    'home'
                )
                ->where(
                    'pageDefinition.label',
                    'Home'
                )
                ->where(
                    'page.title',
                    'Susadhya Counselling'
                )
                ->has('sections')
                ->has('pages', 6)
                ->has('media')
        );
});

it('updates allowed fixed page content without changing the page slug', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->patch(
            route(
                'admin.cms.content.page.update',
                'home'
            ),
            [
                'title' => 'Professional Counselling Support',

                'excerpt' => 'Confidential professional support.',

                'body' => 'Updated fixed website content.',

                'meta_title' => 'Susadhya Counselling',

                'meta_description' => 'Professional counselling support.',

                'og_image_path' => '/images/site-defaults/home.jpg',

                'robots_index' => true,

                'robots_follow' => true,
            ]
        )
        ->assertRedirect();

    $this->assertDatabaseHas(
        'cms_pages',
        [
            'slug' => 'home',
            'title' => 'Professional Counselling Support',

            'excerpt' => 'Confidential professional support.',

            'updated_by' => $this->cmsManager->id,
        ]
    );
});

it('updates a developer defined fixed section', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->patch(
            route(
                'admin.cms.content.section.update',
                [
                    'page' => 'home',
                    'section' => 'hero',
                ]
            ),
            [
                'heading' => 'Support when life feels difficult.',

                'subheading' => 'Professional counselling with privacy and care.',

                'content' => [
                    'eyebrow' => 'Confidential Support',

                    'image_path' => '/images/site-defaults/home-hero.jpg',

                    'image_alt' => 'Counselling conversation',

                    'primary_cta_label' => 'Find a Counsellor',

                    'primary_cta_url' => '/counsellors',
                ],
            ]
        )
        ->assertRedirect();

    $home = CmsPage::query()
        ->where('slug', 'home')
        ->firstOrFail();

    $this->assertDatabaseHas(
        'cms_sections',
        [
            'cms_page_id' => $home->id,

            'key' => 'hero',

            'heading' => 'Support when life feels difficult.',

            'is_active' => true,

            'updated_by' => $this->cmsManager->id,
        ]
    );
});

it('rejects unknown fixed pages', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->get(
            '/admin/cms/content/not-a-real-page'
        )
        ->assertNotFound();
});

it('rejects unknown fixed sections', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->patch(
            '/admin/cms/content/home/not-a-real-section',
            [
                'heading' => 'Invalid',
                'content' => [],
            ]
        )
        ->assertNotFound();
});

it('does not expose legacy page builder routes', function (): void {
    expect(
        Route::has(
            'admin.cms.pages.index'
        )
    )->toBeFalse();

    expect(
        Route::has(
            'admin.cms.pages.create'
        )
    )->toBeFalse();

    expect(
        Route::has(
            'admin.cms.pages.store'
        )
    )->toBeFalse();

    expect(
        Route::has(
            'admin.cms.sections.store'
        )
    )->toBeFalse();

    expect(
        Route::has(
            'admin.cms.sections.destroy'
        )
    )->toBeFalse();
});
