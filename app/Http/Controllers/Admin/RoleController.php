<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('roles.view'), 403);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::query()->where('guard_name', 'web')->withCount(['permissions', 'users'])->orderBy('name')->get(),
            'canManage' => $request->user()->can('roles.manage'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('roles.manage'), 403);

        return Inertia::render('Admin/Roles/Create', ['permissionGroups' => $this->permissionGroups()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Request $request, Role $role): Response
    {
        abort_unless($request->user()->can('roles.manage') && $role->name !== 'super_admin', 403);

        return Inertia::render('Admin/Roles/Edit', [
            'managedRole' => ['id' => $role->id, 'name' => $role->name, 'permissions' => $role->permissions()->pluck('name')],
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->validated('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->name === 'super_admin') {
            abort(
                422,
                'The super administrator role cannot be deleted.',
            );
        }

        if ($role->users()->exists()) {
            abort(
                422,
                'This role cannot be deleted because it is assigned to users.',
            );
        }

        $role->delete();

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return to_route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function permissionGroups(): array
    {
        return Permission::query()->where('guard_name', 'web')->orderBy('name')->get()->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->before('-')->headline()->toString())->map->pluck('name')->toArray();
    }
}
