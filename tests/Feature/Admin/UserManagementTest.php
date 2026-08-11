<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function moduleTwoUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('admin can list users with filters', function () {
    $admin = moduleTwoUser('admin');
    moduleTwoUser('counsellor')->update(['name' => 'Nimali Counsellor']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'Nimali', 'role' => 'counsellor', 'status' => 'active']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Users/Index')->has('users.data', 1));
});

test('admin can create a counsellor account', function () {
    $admin = moduleTwoUser('admin');

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Counsellor',
        'email' => 'new@example.com',
        'phone' => '0712345678',
        'password' => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
        'role' => 'counsellor',
        'is_active' => true,
    ])->assertRedirect(route('admin.users.index'));

    expect(User::whereEmail('new@example.com')->first()->hasRole('counsellor'))->toBeTrue();
});

test('regular admin cannot create a super administrator', function () {
    $admin = moduleTwoUser('admin');

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Escalated User', 'email' => 'escalated@example.com', 'password' => 'SecurePass1',
        'password_confirmation' => 'SecurePass1', 'role' => 'super_admin', 'is_active' => true,
    ])->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => 'escalated@example.com']);
});

test('regular admin cannot assign a custom role containing permissions they do not hold', function () {
    $admin = moduleTwoUser('admin');
    $role = Role::create(['name' => 'privileged_manager', 'guard_name' => 'web']);
    $role->givePermissionTo(['dashboard.admin.view', 'users.delete', 'roles.manage']);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Escalated User', 'email' => 'custom-escalated@example.com', 'password' => 'SecurePass1',
        'password_confirmation' => 'SecurePass1', 'role' => 'privileged_manager', 'is_active' => true,
    ])->assertSessionHasErrors('role');
});

test('admin can update a counsellor without changing the password', function () {
    $admin = moduleTwoUser('admin');
    $subject = moduleTwoUser('counsellor');
    $password = $subject->password;

    $this->actingAs($admin)->put(route('admin.users.update', $subject), [
        'name' => 'Updated Name', 'email' => $subject->email, 'phone' => '', 'password' => '',
        'password_confirmation' => '', 'role' => 'counsellor', 'is_active' => true,
    ])->assertRedirect(route('admin.users.index'));

    expect($subject->fresh()->name)->toBe('Updated Name')->and($subject->fresh()->password)->toBe($password);
});

test('user cannot deactivate their own account', function () {
    $admin = moduleTwoUser('admin');

    $this->actingAs($admin)->put(route('admin.users.update', $admin), [
        'name' => $admin->name, 'email' => $admin->email, 'phone' => '', 'password' => '',
        'password_confirmation' => '', 'role' => 'admin', 'is_active' => false,
    ])->assertSessionHasErrors('is_active');

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('super administrator cannot be demoted or deleted', function () {
    $superAdmin = moduleTwoUser('super_admin');

    $this->actingAs($superAdmin)->put(route('admin.users.update', $superAdmin), [
        'name' => $superAdmin->name, 'email' => $superAdmin->email, 'phone' => '', 'password' => '',
        'password_confirmation' => '', 'role' => 'admin', 'is_active' => true,
    ])->assertSessionHasErrors('role');

    $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin))->assertForbidden();
});

test('regular admin cannot delete users', function () {
    $admin = moduleTwoUser('admin');
    $subject = moduleTwoUser('counsellor');

    $this->actingAs($admin)->delete(route('admin.users.destroy', $subject))->assertForbidden();
});
