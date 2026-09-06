<?php

use App\Models\CmsFaq;
use App\Models\CmsMedia;
use App\Models\CmsTestimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();

    foreach ([
        'cms.view',
        'cms.faqs.manage',
        'cms.testimonials.manage',
        'cms.media.manage',
    ] as $permission) {
        Permission::findOrCreate(
            $permission,
            'web'
        );
    }

    $this->cmsManager = User::factory()->create([
        'is_active' => true,
    ]);

    $this->cmsManager->givePermissionTo([
        'cms.view',
        'cms.faqs.manage',
        'cms.testimonials.manage',
        'cms.media.manage',
    ]);
});

it('allows authorized cms users to create and toggle faqs', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->post(
            route('admin.cms.faqs.store'),
            [
                'category' => 'Appointments',

                'question' => 'How do I book an appointment?',

                'answer' => 'Choose a counsellor and available appointment slot.',

                'display_order' => 10,

                'is_active' => true,
            ]
        )
        ->assertRedirect();

    $faq = CmsFaq::query()
        ->where(
            'question',
            'How do I book an appointment?'
        )
        ->firstOrFail();

    expect($faq->is_active)
        ->toBeTrue();

    expect($faq->published_at)
        ->not->toBeNull();

    $this
        ->actingAs($this->cmsManager)
        ->post(
            route(
                'admin.cms.faqs.toggle',
                $faq
            )
        )
        ->assertRedirect();

    expect(
        $faq->fresh()->is_active
    )->toBeFalse();

    expect(
        $faq->fresh()->published_at
    )->toBeNull();
});

it('prevents publication of testimonials without confirmed consent', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->from(
            route(
                'admin.cms.testimonials.index'
            )
        )
        ->post(
            route(
                'admin.cms.testimonials.store'
            ),
            [
                'display_name' => 'Client',

                'role_label' => 'Client',

                'quote' => 'Helpful counselling support.',

                'rating' => 5,

                'image_path' => null,

                'is_featured' => false,

                'is_active' => true,

                'display_order' => 10,

                'consent_confirmed' => false,
            ]
        )
        ->assertRedirect(
            route(
                'admin.cms.testimonials.index'
            )
        )
        ->assertSessionHasErrors(
            'consent_confirmed'
        );

    expect(
        CmsTestimonial::query()->count()
    )->toBe(0);
});

it('allows publication of testimonials when consent is confirmed', function (): void {
    $this
        ->actingAs($this->cmsManager)
        ->post(
            route(
                'admin.cms.testimonials.store'
            ),
            [
                'display_name' => 'Anonymous Client',

                'role_label' => 'Client',

                'quote' => 'I received professional support.',

                'rating' => 5,

                'image_path' => null,

                'is_featured' => true,

                'is_active' => true,

                'display_order' => 10,

                'consent_confirmed' => true,
            ]
        )
        ->assertRedirect();

    $testimonial =
        CmsTestimonial::query()
            ->firstOrFail();

    expect(
        $testimonial->consent_confirmed
    )->toBeTrue();

    expect(
        $testimonial->consent_confirmed_at
    )->not->toBeNull();

    expect(
        $testimonial->published_at
    )->not->toBeNull();

    expect(
        CmsTestimonial::query()
            ->visible()
            ->count()
    )->toBe(1);
});

it('requires accessible alternative text for cms media', function (): void {
    Storage::fake('public');

    $this
        ->actingAs($this->cmsManager)
        ->from(
            route('admin.cms.media.index')
        )
        ->post(
            route('admin.cms.media.store'),
            [
                'file' => UploadedFile::fake()
                    ->image(
                        'hero.jpg',
                        1200,
                        800
                    ),
            ]
        )
        ->assertRedirect(
            route('admin.cms.media.index')
        )
        ->assertSessionHasErrors(
            'alt_text'
        );

    expect(
        CmsMedia::query()->count()
    )->toBe(0);
});

it('uploads cms media with checksum dimensions and alt text', function (): void {
    Storage::fake('public');

    $this
        ->actingAs($this->cmsManager)
        ->post(
            route('admin.cms.media.store'),
            [
                'file' => UploadedFile::fake()
                    ->image(
                        'counselling-hero.jpg',
                        1200,
                        800
                    )
                    ->size(500),

                'alt_text' => 'Professional counselling conversation',
            ]
        )
        ->assertRedirect();

    $media =
        CmsMedia::query()
            ->firstOrFail();

    expect($media->disk)
        ->toBe('public');

    expect($media->alt_text)
        ->toBe(
            'Professional counselling conversation'
        );

    expect($media->checksum)
        ->not->toBeEmpty();

    expect($media->width)
        ->toBe(1200);

    expect($media->height)
        ->toBe(800);

    expect($media->is_active)
        ->toBeTrue();

    Storage::disk('public')
        ->assertExists(
            $media->path
        );
});

it('archives cms media without deleting the physical file and can restore it', function (): void {
    Storage::fake('public');

    $this
        ->actingAs($this->cmsManager)
        ->post(
            route('admin.cms.media.store'),
            [
                'file' => UploadedFile::fake()
                    ->image(
                        'website-image.jpg',
                        1000,
                        700
                    ),

                'alt_text' => 'Counselling support',
            ]
        )
        ->assertRedirect();

    $media =
        CmsMedia::query()
            ->firstOrFail();

    $path = $media->path;

    $this
        ->actingAs($this->cmsManager)
        ->post(
            route(
                'admin.cms.media.archive',
                $media
            )
        )
        ->assertRedirect();

    expect(
        $media->fresh()->is_active
    )->toBeFalse();

    Storage::disk('public')
        ->assertExists($path);

    $this
        ->actingAs($this->cmsManager)
        ->post(
            route(
                'admin.cms.media.restore',
                $media
            )
        )
        ->assertRedirect();

    expect(
        $media->fresh()->is_active
    )->toBeTrue();

    Storage::disk('public')
        ->assertExists($path);
});
