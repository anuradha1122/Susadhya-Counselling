<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest is redirected to login from dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});

test('super administrator is redirected to admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('admin is redirected to admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('counsellor is redirected to counsellor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('counsellor');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('counsellor.dashboard'));
});

test('admin can access admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
        );
});

test('counsellor cannot access admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('counsellor');

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

test('counsellor can access counsellor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('counsellor');

    $this->actingAs($user)
        ->get('/counsellor/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Counsellor/Dashboard')
            ->has('stats')
        );
});

test('admin cannot access counsellor dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/counsellor/dashboard')
        ->assertForbidden();
});

test('inactive user is logged out', function () {
    $user = User::factory()
        ->inactive()
        ->create();

    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
