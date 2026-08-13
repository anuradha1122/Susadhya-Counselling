# Next Chat Handover - Susadhya Counselling Platform

## Current Status

Workbook module M07: Availability & Calendar is completed.

The project is following the Google Sheet workbook module order.

Completed recently:

- M04 Client Registration & Profile
- M05 Counsellor Management
- M06 Services & Categories
- M07 Availability & Calendar

Next module:

- M08 Search & Counsellor Discovery

Do not start appointment booking yet. Appointment booking belongs to workbook module M09.

## Latest Completed Module

### M07 Availability & Calendar

Implemented:

- Recurring counsellor availability rules
- Availability breaks
- Blocked slots
- Leave days
- Capacity per slot
- Timezone support
- Overlap validation
- Counsellor self-management page
- Admin read-only availability oversight page
- Feature tests

## Important Business Rules Added

### Availability Rules

- Counsellors can create weekly recurring availability.
- Active availability rules cannot overlap for the same counsellor and same day.
- Availability supports mode, slot duration, buffer, capacity, timezone, effective dates, and notes.

### Breaks

- Breaks belong to availability rules.
- Breaks must stay inside the selected availability rule time range.
- Active breaks cannot overlap inside the same availability rule.

### Blocked Slots

- Blocked slots belong to counsellor profiles.
- Blocked slots can be full day or partial day.
- Partial blocked slots require start and end time.
- Blocked slots cannot overlap for the same counsellor and date.

### Leave Days

- Leave days belong to counsellor profiles.
- Leave days can be full day or partial day.
- Partial leave requires start and end time.
- Leave days cannot overlap for the same counsellor and date.

## Key Files Added

### Backend

- app/Models/CounsellorAvailabilityRule.php
- app/Models/CounsellorAvailabilityBreak.php
- app/Models/CounsellorBlockedSlot.php
- app/Models/CounsellorLeaveDay.php
- app/Http/Controllers/Counsellor/AvailabilityController.php
- app/Http/Controllers/Counsellor/AvailabilityBreakController.php
- app/Http/Controllers/Counsellor/BlockedSlotController.php
- app/Http/Controllers/Counsellor/LeaveDayController.php
- app/Http/Controllers/Admin/AvailabilityController.php
- app/Http/Requests/Availability/*

### Frontend

- resources/js/Pages/Counsellor/Availability/Index.jsx
- resources/js/Pages/Admin/Availability/Index.jsx
- resources/js/Layouts/CounsellorLayout.jsx

### Tests

- tests/Feature/Counsellor/AvailabilityManagementTest.php
- tests/Feature/Admin/AvailabilityOversightTest.php

### Documentation

- docs/modules/07-availability-and-calendar.md

## Routes Added

### Counsellor

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

### Admin

- admin.availability.index

## Verification Commands

Run these before continuing to M08:

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
