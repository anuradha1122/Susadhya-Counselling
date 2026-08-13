

# Module 08 - Search & Counsellor Discovery

## Status

Completed.

## Workbook Reference

This module follows the project workbook:

- Workbook Module ID: M08
- Module Name: Search & Counsellor Discovery
- Phase: P3
- Release: MVP
- Primary Role: Client

## Scope

This module allows clients to search and review counsellors before appointment booking.

Implemented scope:

- Client counsellor discovery page
- Keyword search
- Filter by specialization
- Filter by language
- Filter by counselling mode
- Filter by availability day
- Active counsellor visibility rules
- Counsellor profile detail page
- Availability summary display
- Rating placeholder
- Feature tests

Appointment booking is intentionally not implemented in this module. Appointment booking belongs to workbook module M09.

## Business Rules

### Discoverable Counsellors

Only counsellors that meet all of the following conditions are visible:

- Counsellor profile status is active
- Linked user account is active
- Counsellor matches selected filters, if any

Archived counsellors are hidden.

Counsellors with inactive user accounts are hidden.

### Search

Clients can search counsellors by:

- User name
- User email
- Professional title
- Biography
- City
- Specialization name

### Specialization Filter

Clients can filter counsellors by specialization.

The filter uses the existing relationship:

- CounsellorProfile belongs to many Specialization

### Language Filter

Clients can filter counsellors by language.

The filter uses the existing relationship:

- CounsellorProfile belongs to many Language

Language proficiency is displayed from the pivot table when available.

### Mode Filter

Clients can filter by counselling mode:

- Online
- In person
- Online and in person

The filter uses active counsellor availability rules from M07.

When filtering by online or in-person mode, counsellors with `both` mode are also included.

### Availability Day Filter

Clients can filter counsellors by availability day.

The filter uses active recurring availability rules from M07.

### Rating Placeholder

Ratings are displayed as placeholders only.

Actual reviews and ratings are not implemented in this module.

## Database Changes

No new database tables were added in this module.

This module reuses existing tables:

- counsellor_profiles
- users
- specializations
- languages
- counsellor_profile_language
- counsellor_profile_specialization
- counsellor_availability_rules
- counsellor_availability_breaks

## Backend Controller

Added:

- app/Http/Controllers/Client/CounsellorDiscoveryController.php

Controller actions:

- index()
- show()

### index()

Handles counsellor search and filters.

Filters:

- search
- specialization_id
- language_id
- mode
- availability_day

Returns:

- Paginated counsellor cards
- Filter options
- Availability summary
- Rating placeholder

### show()

Handles counsellor profile detail view.

Returns:

- Counsellor profile details
- Specializations
- Languages
- Qualifications
- Availability rules
- Breaks
- Rating placeholder

Archived counsellors return 404.

Counsellors with inactive user accounts return 404.

## Frontend Pages

Added:

- resources/js/Pages/Client/Counsellors/Index.jsx
- resources/js/Pages/Client/Counsellors/Show.jsx

### Discovery Page

The discovery page includes:

- Search input
- Specialty filter
- Language filter
- Mode filter
- Availability day filter
- Reset filters button
- Counsellor cards
- Availability summary
- Rating placeholder
- View profile button

### Profile Detail Page

The profile detail page includes:

- Counsellor name
- Professional title
- City
- Years of experience
- Registration number
- Gender
- Biography
- Availability summary
- Availability breaks
- Qualifications
- Specializations
- Languages
- Contact visibility
- Disabled M09 booking button

## Routes

Added client routes:

- client.counsellors.index
- client.counsellors.show

URLs:

- /client/counsellors
- /client/counsellors/{counsellor}

## Navigation

Updated:

- resources/js/Config/navigation.js

Added client sidebar item:

- Find Counsellors

## Tests

Added:

- tests/Feature/Client/CounsellorDiscoveryTest.php

Test coverage includes:

- Client can view counsellor discovery page
- Only active counsellors with active user accounts are shown
- Keyword search
- Specialization filter
- Language filter
- Counselling mode filter
- Both-mode counsellors are included when filtering by online or in-person mode
- Availability day filter
- Client can view active counsellor profile detail
- Archived counsellor profile detail returns 404
- Inactive user account counsellor profile detail returns 404

## Verification Commands

```bash
./vendor/bin/pint --test \
    app/Http/Controllers/Client/CounsellorDiscoveryController.php \
    tests/Feature/Client/CounsellorDiscoveryTest.php \
    routes/web.php

npx prettier --check \
    resources/js/Pages/Client/Counsellors/Index.jsx \
    resources/js/Pages/Client/Counsellors/Show.jsx \
    resources/js/Config/navigation.js

npm run build

php artisan test tests/Feature/Client/CounsellorDiscoveryTest.php
Manual QA Checklist

Client:

Login as a client.
Open /client/counsellors.
Search by counsellor name.
Search by professional title.
Search by city.
Filter by specialization.
Filter by language.
Filter by online mode.
Filter by in-person mode.
Filter by availability day.
Reset filters.
Click View profile.
Confirm profile detail page opens.
Confirm availability is visible.
Confirm qualifications, languages, and specializations are visible.
Confirm booking button is disabled and marked for M09.

Protection checks:

Archived counsellor should not appear in search.
Counsellor with inactive user account should not appear in search.
Archived counsellor detail URL should return 404.
Inactive user counsellor detail URL should return 404.
Completion Notes

M08 is completed as the client counsellor discovery layer.

This module prepares the platform for M09 Appointment Scheduling by letting clients find and inspect counsellors before booking.

The fee filter is documented but not fully implemented because counsellor-specific service offerings are not yet mapped. Global counselling service prices exist, but there is no confirmed counsellor-to-service offering table yet.

Next workbook module:

M09 Appointments & Scheduling

---

# 3. Development log update

## File

```text
docs/development-log.md

Add this section at the bottom of the file:

## 2026-08-13 - M08 Search & Counsellor Discovery Completed

Completed workbook module M08: Search & Counsellor Discovery.

Implemented:

- Client counsellor discovery page
- Client counsellor profile detail page
- Keyword search
- Specialization filter
- Language filter
- Counselling mode filter
- Availability day filter
- Active counsellor visibility rules
- Archived counsellor protection
- Inactive counsellor user account protection
- Rating placeholder
- Availability summary display
- Profile detail availability display with breaks
- Feature tests for discovery filters and profile detail

Added backend file:

- app/Http/Controllers/Client/CounsellorDiscoveryController.php

Added frontend files:

- resources/js/Pages/Client/Counsellors/Index.jsx
- resources/js/Pages/Client/Counsellors/Show.jsx

Updated:

- routes/web.php
- resources/js/Config/navigation.js

Added tests:

- tests/Feature/Client/CounsellorDiscoveryTest.php

Routes added:

- client.counsellors.index
- client.counsellors.show

Verification:

- Pint passed
- Prettier passed
- Vite production build passed
- M08 client discovery tests passed

Notes:

- Appointment booking was intentionally not implemented in this module.
- Appointment booking belongs to workbook module M09.
- Fee filtering was not fully implemented because counsellor-specific service offerings are not yet mapped.
- Ratings are placeholders until a future review/rating module exists.
