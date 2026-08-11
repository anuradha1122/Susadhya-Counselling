<?php

use App\Models\CounsellingService;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ([
        'dashboard.admin.view',
        'services.view',
        'services.create',
        'services.update',
        'services.archive',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $this->adminRole = Role::findOrCreate('Super Admin', 'web');

    $this->adminRole->syncPermissions([
        'dashboard.admin.view',
        'services.view',
        'services.create',
        'services.update',
        'services.archive',
    ]);

    $this->admin = User::factory()->create([
        'is_active' => true,
    ]);

    $this->admin->assignRole($this->adminRole);

    $this->activeCategory = ServiceCategory::query()->create([
        'name' => 'Individual Counselling',
        'slug' => 'individual-counselling',
        'description' => 'One-to-one counselling services.',
        'display_order' => 1,
        'status' => 'active',
    ]);
});

function servicePayload(
    ServiceCategory $category,
    array $overrides = [],
): array {
    return array_merge([
        'service_category_id' => $category->id,
        'name' => 'Individual Counselling Session',
        'slug' => 'individual-counselling-session',
        'short_description' => 'A private individual counselling session.',
        'description' => 'A structured one-to-one counselling session.',
        'duration_minutes' => 60,
        'service_mode' => 'both',
        'target_age_group' => 'adults',
        'minimum_age' => null,
        'maximum_age' => null,
        'price' => 5000,
        'currency' => 'LKR',
        'display_order' => 1,
        'status' => 'active',
    ], $overrides);
}

function createCounsellingService(
    ServiceCategory $category,
    array $overrides = [],
): CounsellingService {
    return CounsellingService::query()->create(
        servicePayload($category, $overrides)
    );
}

it('redirects guests from the counselling service index', function () {
    $this->get(route('admin.counselling-services.index'))
        ->assertRedirect(route('login'));
});

it('allows an authorised administrator to view the service index', function () {
    createCounsellingService($this->activeCategory);

    $this->actingAs($this->admin)
        ->get(route('admin.counselling-services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CounsellingServices/Index')
            ->has('services.data', 1)
            ->where(
                'services.data.0.name',
                'Individual Counselling Session'
            )
            ->where(
                'services.data.0.category.name',
                'Individual Counselling'
            )
            ->where('permissions.create', true)
            ->where('permissions.update', true)
            ->where('permissions.archive', true)
        );
});

it('prevents a user without view permission from viewing services', function () {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.counselling-services.index'))
        ->assertForbidden();
});

it('shows the create page with active categories', function () {
    ServiceCategory::query()->create([
        'name' => 'Archived Category',
        'slug' => 'archived-category',
        'description' => 'An archived category.',
        'display_order' => 2,
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.counselling-services.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CounsellingServices/Create')
            ->has('categories', 1)
            ->where('categories.0.id', $this->activeCategory->id)
            ->has('serviceModes')
            ->has('targetAgeGroups')
            ->has('durations')
        );
});

it('creates a counselling service', function () {
    $response = $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($this->activeCategory)
        );

    $service = CounsellingService::query()->firstOrFail();

    $response
        ->assertSessionHas(
            'success',
            'Counselling service created successfully.'
        )
        ->assertRedirect(
            route('admin.counselling-services.show', $service)
        );

    $this->assertDatabaseHas('counselling_services', [
        'id' => $service->id,
        'service_category_id' => $this->activeCategory->id,
        'name' => 'Individual Counselling Session',
        'slug' => 'individual-counselling-session',
        'duration_minutes' => 60,
        'service_mode' => 'both',
        'target_age_group' => 'adults',
        'price' => 5000,
        'currency' => 'LKR',
        'status' => 'active',
    ]);
});

it('validates required service fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.counselling-services.store'), [])
        ->assertSessionHasErrors([
            'service_category_id',
            'name',
            'slug',
            'short_description',
            'duration_minutes',
            'service_mode',
            'target_age_group',
            'price',
            'currency',
            'display_order',
            'status',
        ]);

    $this->assertDatabaseCount('counselling_services', 0);
});

it('requires a unique service slug', function () {
    createCounsellingService($this->activeCategory);

    $secondCategory = ServiceCategory::query()->create([
        'name' => 'Family Counselling',
        'slug' => 'family-counselling',
        'description' => 'Counselling for families.',
        'display_order' => 2,
        'status' => 'active',
    ]);

    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($secondCategory, [
                'name' => 'Another Service',
            ])
        )
        ->assertSessionHasErrors('slug');

    $this->assertDatabaseCount('counselling_services', 1);
});

it('does not allow a service to be created under an archived category', function () {
    $archivedCategory = ServiceCategory::query()->create([
        'name' => 'Archived Category',
        'slug' => 'archived-category',
        'description' => 'An archived category.',
        'display_order' => 2,
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($archivedCategory)
        )
        ->assertSessionHasErrors('service_category_id');

    $this->assertDatabaseCount('counselling_services', 0);
});

it('requires minimum and maximum ages for a custom age group', function () {
    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($this->activeCategory, [
                'target_age_group' => 'custom',
                'minimum_age' => null,
                'maximum_age' => null,
            ])
        )
        ->assertSessionHasErrors([
            'minimum_age',
            'maximum_age',
        ]);
});

it('requires maximum age to be greater than or equal to minimum age', function () {
    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($this->activeCategory, [
                'target_age_group' => 'custom',
                'minimum_age' => 40,
                'maximum_age' => 20,
            ])
        )
        ->assertSessionHasErrors('maximum_age');
});

it('creates a service with a valid custom age range', function () {
    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($this->activeCategory, [
                'target_age_group' => 'custom',
                'minimum_age' => 18,
                'maximum_age' => 35,
            ])
        )
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('counselling_services', [
        'target_age_group' => 'custom',
        'minimum_age' => 18,
        'maximum_age' => 35,
    ]);
});

it('clears custom ages for a predefined age group', function () {
    $this->actingAs($this->admin)
        ->post(
            route('admin.counselling-services.store'),
            servicePayload($this->activeCategory, [
                'target_age_group' => 'adults',
                'minimum_age' => 18,
                'maximum_age' => 60,
            ])
        )
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('counselling_services', [
        'target_age_group' => 'adults',
        'minimum_age' => null,
        'maximum_age' => null,
    ]);
});

it('shows a counselling service', function () {
    $service = createCounsellingService($this->activeCategory);

    $this->actingAs($this->admin)
        ->get(
            route('admin.counselling-services.show', $service)
        )
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CounsellingServices/Show')
            ->where('service.id', $service->id)
            ->where(
                'service.category.id',
                $this->activeCategory->id
            )
            ->where('permissions.update', true)
            ->where('permissions.archive', true)
            ->where('permissions.restore', false)
        );
});

it('shows the edit page', function () {
    $service = createCounsellingService($this->activeCategory);

    $this->actingAs($this->admin)
        ->get(
            route('admin.counselling-services.edit', $service)
        )
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CounsellingServices/Edit')
            ->where('service.id', $service->id)
            ->where('service.name', $service->name)
            ->has('categories')
            ->has('serviceModes')
            ->has('targetAgeGroups')
            ->has('durations')
        );
});

it('updates a counselling service', function () {
    $service = createCounsellingService($this->activeCategory);

    $response = $this->actingAs($this->admin)
        ->put(
            route(
                'admin.counselling-services.update',
                $service
            ),
            servicePayload($this->activeCategory, [
                'name' => 'Updated Counselling Session',
                'slug' => 'updated-counselling-session',
                'duration_minutes' => 90,
                'price' => 7500,
                'status' => 'inactive',
            ])
        );

    $response
        ->assertSessionHas(
            'success',
            'Counselling service updated successfully.'
        )
        ->assertRedirect(
            route('admin.counselling-services.show', $service)
        );

    $this->assertDatabaseHas('counselling_services', [
        'id' => $service->id,
        'name' => 'Updated Counselling Session',
        'slug' => 'updated-counselling-session',
        'duration_minutes' => 90,
        'price' => 7500,
        'status' => 'inactive',
    ]);
});

it('allows a service to retain its slug during an update', function () {
    $service = createCounsellingService($this->activeCategory);

    $this->actingAs($this->admin)
        ->put(
            route(
                'admin.counselling-services.update',
                $service
            ),
            servicePayload($this->activeCategory, [
                'name' => 'Renamed Counselling Session',
                'slug' => $service->slug,
            ])
        )
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('counselling_services', [
        'id' => $service->id,
        'name' => 'Renamed Counselling Session',
        'slug' => $service->slug,
    ]);
});

it('filters services by search text', function () {
    createCounsellingService($this->activeCategory);

    createCounsellingService($this->activeCategory, [
        'name' => 'Family Therapy',
        'slug' => 'family-therapy',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.counselling-services.index', [
            'search' => 'Family',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('services.data', 1)
            ->where('services.data.0.name', 'Family Therapy')
            ->where('filters.search', 'Family')
        );
});

it('filters services by category', function () {
    createCounsellingService($this->activeCategory);

    $secondCategory = ServiceCategory::query()->create([
        'name' => 'Family Counselling',
        'slug' => 'family-counselling',
        'description' => 'Counselling for families.',
        'display_order' => 2,
        'status' => 'active',
    ]);

    createCounsellingService($secondCategory, [
        'name' => 'Family Session',
        'slug' => 'family-session',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.counselling-services.index', [
            'service_category_id' => $secondCategory->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('services.data', 1)
            ->where('services.data.0.name', 'Family Session')
        );
});

it('filters services by status mode and target age group', function () {
    createCounsellingService($this->activeCategory, [
        'name' => 'Online Child Session',
        'slug' => 'online-child-session',
        'service_mode' => 'online',
        'target_age_group' => 'children',
        'status' => 'inactive',
    ]);

    createCounsellingService($this->activeCategory, [
        'name' => 'Adult In-person Session',
        'slug' => 'adult-in-person-session',
        'service_mode' => 'in_person',
        'target_age_group' => 'adults',
        'status' => 'active',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.counselling-services.index', [
            'status' => 'inactive',
            'service_mode' => 'online',
            'target_age_group' => 'children',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('services.data', 1)
            ->where(
                'services.data.0.name',
                'Online Child Session'
            )
        );
});

it('archives a counselling service', function () {
    $service = createCounsellingService($this->activeCategory);

    $this->actingAs($this->admin)
        ->delete(
            route(
                'admin.counselling-services.destroy',
                $service
            )
        )
        ->assertSessionHas(
            'success',
            'Counselling service archived successfully.'
        )
        ->assertRedirect(
            route('admin.counselling-services.index')
        );

    $service->refresh();

    expect($service->status)->toBe('archived')
        ->and($service->archived_at)->not->toBeNull()
        ->and($service->archived_by)->toBe($this->admin->id);
});

it('does not display the edit page for an archived service', function () {
    $service = createCounsellingService($this->activeCategory, [
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->get(
            route('admin.counselling-services.edit', $service)
        )
        ->assertForbidden();
});

it('restores a service as active when its category is active', function () {
    $service = createCounsellingService($this->activeCategory, [
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->patch(
            route(
                'admin.counselling-services.restore',
                $service
            )
        )
        ->assertSessionHas(
            'success',
            'Counselling service restored successfully.'
        )
        ->assertRedirect(
            route('admin.counselling-services.show', $service)
        );

    $service->refresh();

    expect($service->status)->toBe('active')
        ->and($service->archived_at)->toBeNull()
        ->and($service->archived_by)->toBeNull();
});

it('restores a service as inactive when its category is inactive', function () {
    $this->activeCategory->update([
        'status' => 'inactive',
    ]);

    $service = createCounsellingService($this->activeCategory, [
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->patch(
            route(
                'admin.counselling-services.restore',
                $service
            )
        )
        ->assertSessionHasNoErrors();

    expect($service->fresh()->status)->toBe('inactive');
});

it('enforces individual service permissions', function () {
    $viewer = User::factory()->create([
        'is_active' => true,
    ]);

    $viewer->givePermissionTo([
        'dashboard.admin.view',
        'services.view',
    ]);

    $service = createCounsellingService($this->activeCategory);

    $this->actingAs($viewer)
        ->get(route('admin.counselling-services.index'))
        ->assertOk();

    $this->actingAs($viewer)
        ->get(route('admin.counselling-services.create'))
        ->assertForbidden();

    $this->actingAs($viewer)
        ->get(
            route('admin.counselling-services.edit', $service)
        )
        ->assertForbidden();

    $this->actingAs($viewer)
        ->delete(
            route(
                'admin.counselling-services.destroy',
                $service
            )
        )
        ->assertForbidden();

    expect($service->fresh()->status)->toBe('active');
});
