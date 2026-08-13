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
