# Module 07 - Availability & Calendar

## Status

Completed.

## Workbook Reference

This module follows the project workbook:

- Workbook Module ID: M07
- Module Name: Availability & Calendar
- Phase: P2
- Release: MVP
- Primary Roles: Counsellor, Admin

## Scope

This module provides the foundation for counsellor availability management.

Implemented scope:

- Recurring counsellor availability rules
- Availability breaks
- Counsellor blocked slots
- Counsellor leave days
- Capacity per slot
- Timezone support
- Overlap validation
- Counsellor self-management page
- Admin availability oversight page
- Feature tests for counsellor and admin availability workflows

Appointment booking is not included in this module. Appointment scheduling belongs to workbook module M09.

## Business Rules

### Recurring Availability

Counsellors can define weekly recurring availability rules.

Each rule includes:

- Day of week
- Start time
- End time
- Counselling mode
- Slot duration
- Buffer minutes
- Capacity per slot
- Timezone
- Effective from date
- Effective until date
- Active/inactive state
- Notes

Active availability rules cannot overlap for the same counsellor and day.

### Availability Breaks

Counsellors can add breaks inside an availability rule.

Break rules:

- Breaks must belong to an availability rule.
- Break start time must be inside the parent availability rule.
- Break end time must be inside the parent availability rule.
- Active breaks cannot overlap inside the same availability rule.

### Blocked Slots

Counsellors can block one-off unavailable dates or time ranges.

Blocked slot rules:

- A blocked slot belongs to a counsellor profile.
- A blocked slot can be full day or partial day.
- Partial day blocked slots require start and end times.
- Blocked slots cannot overlap for the same counsellor and date.

### Leave Days

Counsellors can record leave days.

Leave day rules:

- A leave day belongs to a counsellor profile.
- A leave day can be full day or partial day.
- Partial leave requires start and end times.
- Leave records cannot overlap for the same counsellor and date.

## Database Tables

### counsellor_availability_rules

Stores weekly recurring availability.

Important fields:

- counsellor_profile_id
- day_of_week
- start_time
- end_time
- mode
- slot_duration_minutes
- buffer_minutes
- capacity_per_slot
- timezone
- effective_from
- effective_until
- is_active
- notes
- created_by
- updated_by

### counsellor_availability_breaks

Stores breaks inside recurring availability rules.

Important fields:

- counsellor_availability_rule_id
- title
- start_time
- end_time
- is_active
- created_by
- updated_by

### counsellor_blocked_slots

Stores one-off blocked slots.

Important fields:

- counsellor_profile_id
- blocked_date
- start_time
- end_time
- is_full_day
- reason
- notes
- created_by
- updated_by

### counsellor_leave_days

Stores counsellor leave days.

Important fields:

- counsellor_profile_id
- leave_date
- start_time
- end_time
- is_full_day
- reason
- notes
- created_by
- updated_by

## Models

Added models:

- App\Models\CounsellorAvailabilityRule
- App\Models\CounsellorAvailabilityBreak
- App\Models\CounsellorBlockedSlot
- App\Models\CounsellorLeaveDay

Updated model:

- App\Models\CounsellorProfile

Added relationships to CounsellorProfile:

- availabilityRules()
- availabilityBreaks()
- blockedSlots()
- leaveDays()

## Backend Controllers

### Counsellor

Added:

- App\Http\Controllers\Counsellor\AvailabilityController
- App\Http\Controllers\Counsellor\AvailabilityBreakController
- App\Http\Controllers\Counsellor\BlockedSlotController
- App\Http\Controllers\Counsellor\LeaveDayController

Counsellor routes allow counsellors to manage only their own availability records.

### Admin

Added:

- App\Http\Controllers\Admin\AvailabilityController

Admin route provides read-only oversight of counsellor availability.

## Request Validation

Added request classes under:

- app/Http/Requests/Availability

Implemented request validation for:

- Creating availability rules
- Updating availability rules
- Creating availability breaks
- Updating availability breaks
- Creating blocked slots
- Updating blocked slots
- Creating leave days
- Updating leave days

Validation protects against:

- Invalid time ranges
- Invalid day of week
- Invalid counselling mode
- Invalid slot duration
- Overlapping availability rules
- Breaks outside availability ranges
- Overlapping breaks
- Overlapping blocked slots
- Overlapping leave days

## Frontend Pages

### Counsellor

Added:

- resources/js/Pages/Counsellor/Availability/Index.jsx
- resources/js/Layouts/CounsellorLayout.jsx

Counsellor page includes:

- Add recurring availability form
- Current availability rules list
- Add break form
- Blocked slots form and list
- Leave days form and list
- Delete actions

### Admin

Added:

- resources/js/Pages/Admin/Availability/Index.jsx

Admin page includes:

- Search by counsellor name, email, or phone
- Filter by day
- Filter by rule status
- Counsellor availability cards
- Recurring availability table
- Break visibility
- Upcoming blocked slots
- Upcoming leave days

## Routes

### Counsellor Routes

Route names:

- counsellor.availability.index
- counsellor.availability.rules.store
- counsellor.availability.rules.update
- counsellor.availability.rules.destroy
- counsellor.availability.breaks.store
- counsellor.availability.breaks.update
- counsellor.availability.breaks.destroy
- counsellor.availability.blocked-slots.store
- counsellor.availability.blocked-slots.update
- counsellor.availability.blocked-slots.destroy
- counsellor.availability.leave-days.store
- counsellor.availability.leave-days.update
- counsellor.availability.leave-days.destroy

### Admin Routes

Route names:

- admin.availability.index

## Navigation

Updated:

- resources/js/Config/navigation.js

Added:

- Counsellor sidebar item: My Availability
- Admin sidebar item: Availability

## Tests

Added factories:

- database/factories/CounsellorAvailabilityRuleFactory.php
- database/factories/CounsellorAvailabilityBreakFactory.php
- database/factories/CounsellorBlockedSlotFactory.php
- database/factories/CounsellorLeaveDayFactory.php

Added tests:

- tests/Feature/Counsellor/AvailabilityManagementTest.php
- tests/Feature/Admin/AvailabilityOversightTest.php

Test coverage includes:

- Counsellor can view availability page
- Counsellor can create recurring availability
- Overlapping recurring availability is rejected
- Non-overlapping recurring availability is allowed
- Counsellor can create breaks
- Breaks outside availability range are rejected
- Overlapping breaks are rejected
- Blocked slot overlap protection
- Leave day overlap protection
- Counsellor cannot delete another counsellor's availability rule
- Admin can view availability oversight
- Admin can filter availability oversight
- Restricted admin cannot view availability oversight

## Verification Commands

```bash
./vendor/bin/pint --test \
    app/Http/Controllers/Admin/AvailabilityController.php \
    app/Http/Controllers/Counsellor/AvailabilityController.php \
    app/Http/Controllers/Counsellor/AvailabilityBreakController.php \
    app/Http/Controllers/Counsellor/BlockedSlotController.php \
    app/Http/Controllers/Counsellor/LeaveDayController.php \
    app/Http/Requests/Availability \
    app/Models/CounsellorAvailabilityRule.php \
    app/Models/CounsellorAvailabilityBreak.php \
    app/Models/CounsellorBlockedSlot.php \
    app/Models/CounsellorLeaveDay.php \
    app/Models/CounsellorProfile.php \
    database/factories/CounsellorAvailabilityRuleFactory.php \
    database/factories/CounsellorAvailabilityBreakFactory.php \
    database/factories/CounsellorBlockedSlotFactory.php \
    database/factories/CounsellorLeaveDayFactory.php \
    tests/Feature/Counsellor/AvailabilityManagementTest.php \
    tests/Feature/Admin/AvailabilityOversightTest.php \
    routes/web.php

npm run build

php artisan test \
    tests/Feature/Counsellor/AvailabilityManagementTest.php \
    tests/Feature/Admin/AvailabilityOversightTest.php
