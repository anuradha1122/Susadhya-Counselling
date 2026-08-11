# Susadhya Counselling Management System

Susadhya is a role-based counselling administration platform built with Laravel 13, React 18, Inertia 2, Tailwind CSS, MySQL/SQLite, and Spatie Laravel Permission.

## Completed modules

### Module 01 — Foundation and authentication

- Login, password recovery, verification, and profile management
- Public registration disabled
- Active-account enforcement
- Super administrator, administrator, and counsellor roles
- Role/permission-aware Admin and Counsellor dashboards

### Module 02 — Users, roles, and permissions

- Searchable and filterable paginated user directory
- User account creation and editing
- Account activation/deactivation and password reset by administrators
- Safe role assignment with privilege-escalation prevention
- Role creation/editing and permission grouping
- Custom roles routed by dashboard permission
- Primary super-administrator, self-deactivation, and system-role protection
- Feature tests for access control and management workflows

See [docs/MODULE-02.md](docs/MODULE-02.md) for operational details and the verification checklist.

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan optimize:clear
npm run build
php artisan test
```

Configure the `DB_*` and `SUPER_ADMIN_*` variables in `.env` before seeding. Never commit `.env`.

## Development

```bash
composer run dev
```

## Quality checks

```bash
./vendor/bin/pint --test
php artisan test
npm run build


### Module 04 — Counsellors and Counselling Services

Status: Completed

Implemented:

- Counsellor administration
- Counsellor status, archive, and restore workflow
- Service category administration
- Counselling service administration
- Delivery-mode and target-age configuration
- Custom minimum and maximum age validation
- Service duration and pricing
- Category-aware service restoration
- Permission-based navigation and authorization
- Feature-test coverage

See [Module 04 documentation](docs/modules/module-04-counsellors-and-services.md).
```
