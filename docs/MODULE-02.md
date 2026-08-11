# Module 02 — Admin User, Role, and Permission Management

## Scope

Module 02 provides server-authorized user and access administration under `/admin/users` and `/admin/roles`.

## User management

- Search by name, email, or phone.
- Filter by role and active status.
- Create accounts with verified email, an initial password, one role, and active status.
- Edit identity, role, status, and optionally replace the password.
- Delete accounts only when the acting user has `users.delete`.

## Role management

- Users with `roles.view` can list roles.
- Users with `roles.manage` can create custom roles and edit all non-super-admin roles.
- Permissions are grouped by functional prefix in the interface.
- A custom role must receive `dashboard.admin.view` or `dashboard.counsellor.view` to reach a workspace.

## Security rules

- A regular administrator cannot assign the `super_admin` role.
- The super-administrator role cannot be edited or deleted.
- Built-in roles (`super_admin`, `admin`, `counsellor`) cannot be deleted.
- A role assigned to any user cannot be deleted.
- The primary super-administrator cannot be demoted or deleted.
- A user cannot deactivate or delete their own account.
- Every write is validated and authorized on the server; hidden buttons are not treated as security controls.

## Upgrade commands

Module 02 adds no database migration. Existing permission data is refreshed by the idempotent seeder:

```bash
php artisan optimize:clear
php artisan db:seed --class=RolePermissionSeeder
php artisan permission:cache-reset
npm install
npm run build
php artisan test
```

## Manual acceptance checks

1. Sign in as super administrator and open Users and Roles & Permissions.
2. Create a counsellor and confirm the account appears in search/filter results.
3. Deactivate that account and verify login is refused.
4. Create a custom role with `dashboard.admin.view` and `users.view`.
5. Assign it to a user and verify the Admin dashboard and Users list are accessible.
6. Sign in as a regular administrator and confirm role management is unavailable.
7. Confirm attempts to assign `super_admin`, self-deactivate, demote the super administrator, or delete a system role are rejected.

## Automated coverage

- `tests/Feature/Admin/UserManagementTest.php`
- `tests/Feature/Admin/RoleManagementTest.php`
- Existing Module 01 authentication, dashboard, registration, profile, and seeder tests
