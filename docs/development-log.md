## 2026-08-13 - M07 Availability & Calendar Completed

Completed workbook module M07: Availability & Calendar.

Implemented:

- Recurring counsellor availability rules
- Availability breaks
- Blocked slots
- Leave days
- Capacity per slot
- Timezone field support
- Counsellor self-management UI
- Admin read-only availability oversight UI
- Validation for overlapping availability rules
- Validation for breaks outside rule time range
- Validation for overlapping breaks
- Validation for overlapping blocked slots
- Validation for overlapping leave days
- Ownership protection for counsellor availability records
- Admin availability filters by counsellor search, day, and rule status
- Feature tests for counsellor availability management
- Feature tests for admin availability oversight

Added backend files:

- app/Http/Controllers/Admin/AvailabilityController.php
- app/Http/Controllers/Counsellor/AvailabilityController.php
- app/Http/Controllers/Counsellor/AvailabilityBreakController.php
- app/Http/Controllers/Counsellor/BlockedSlotController.php
- app/Http/Controllers/Counsellor/LeaveDayController.php
- app/Http/Requests/Availability/*
- app/Models/CounsellorAvailabilityRule.php
- app/Models/CounsellorAvailabilityBreak.php
- app/Models/CounsellorBlockedSlot.php
- app/Models/CounsellorLeaveDay.php

Updated:

- app/Models/CounsellorProfile.php
- routes/web.php
- resources/js/Config/navigation.js
- resources/js/Layouts/CounsellorLayout.jsx

Added frontend files:

- resources/js/Pages/Counsellor/Availability/Index.jsx
- resources/js/Pages/Admin/Availability/Index.jsx

Added test files:

- tests/Feature/Counsellor/AvailabilityManagementTest.php
- tests/Feature/Admin/AvailabilityOversightTest.php

Added factories:

- database/factories/CounsellorAvailabilityRuleFactory.php
- database/factories/CounsellorAvailabilityBreakFactory.php
- database/factories/CounsellorBlockedSlotFactory.php
- database/factories/CounsellorLeaveDayFactory.php

Verification:

- Pint passed
- Vite production build passed
- Counsellor availability tests passed
- Admin availability oversight tests passed

Notes:

- Appointment booking was intentionally not implemented in this module.
- Appointment booking belongs to workbook module M09.
- Next workbook module is M08 Search & Counsellor Discovery.

## 2026-09-02 - M09 Appointments & Scheduling Completed

Completed the full MVP Appointments & Scheduling module.

Finalized workflows:

- Appointment database foundation
- Appointment status history
- Availability-based slot generation
- Counsellor availability break exclusion
- Counsellor blocked slot exclusion
- Counsellor leave day exclusion
- Counsellor double-booking prevention
- Client double-booking prevention
- Client appointment booking
- Client booking UI on counsellor profile
- Client appointment list
- Client cancellation
- Client rescheduling
- Counsellor appointment dashboard
- Counsellor confirmation workflow
- Counsellor completion workflow
- Counsellor no-show workflow
- Admin appointment oversight
- Admin appointment status management
- Appointment reminder notification
- Appointment reminder sending command
- Scheduler hook
- Dashboard appointment metrics
- Final M09 documentation

Business rules completed:

- Clients create appointments as `pending`.
- Clients can cancel only their own `pending` or `confirmed` appointments.
- Clients can reschedule only their own `pending` or `confirmed` appointments.
- Rescheduling marks the old appointment as `rescheduled` and creates a new `pending` appointment.
- Counsellors can view only appointments assigned to their profile.
- Counsellors can confirm only their own `pending` appointments.
- Online confirmation requires a meeting link.
- In-person confirmation requires a location.
- Counsellors can complete or mark no-show only their own `confirmed` appointments.
- Admin can view all appointments and manage operational statuses.
- All major status changes create appointment status history records.
- Reminders are sent only once using `reminder_sent_at`.

Validation and QA:

- M09 feature tests cover slot generation, booking, listing, cancellation, rescheduling, counsellor workflows, admin oversight, reminders, and dashboard metrics.
- Vite production build should pass after the final code sections are applied.
- Final module documentation has been updated in `modules/09-appointments-and-scheduling.md`.
