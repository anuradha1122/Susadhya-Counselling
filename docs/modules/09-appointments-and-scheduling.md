# M09 - Appointments & Scheduling

## Status

Completed.

## Workbook Reference

- Module: M09 Appointments & Scheduling
- Priority: P3 MVP
- Primary Roles: Client, Counsellor, Admin

## Purpose

The Appointments & Scheduling module manages the full counselling appointment lifecycle from client booking to final appointment outcome.

The module connects:

- Counsellor availability
- Client discovery
- Appointment booking
- Appointment cancellation
- Appointment rescheduling
- Counsellor confirmation
- Counsellor completion/no-show handling
- Admin oversight
- Reminder hooks
- Dashboard metrics
- Status history

## Scope Completed

Implemented:

- Appointment database foundation
- Appointment status history
- Slot generation from counsellor availability rules
- Break, leave, and blocked-slot exclusion
- Counsellor double-booking prevention
- Client double-booking prevention
- Client appointment booking
- Client booking UI on counsellor profile
- Client appointment list
- Client appointment cancellation
- Client appointment rescheduling
- Counsellor appointment dashboard
- Counsellor appointment confirmation
- Counsellor completion workflow
- Counsellor no-show workflow
- Admin appointment oversight
- Admin appointment status management
- Reminder notification hook
- Reminder sending Artisan command
- Scheduler integration
- Appointment dashboard metrics
- Final QA tests

## Core Models

### Appointment

File:

- `app/Models/Appointment.php`

Responsibilities:

- Stores appointment date/time
- Stores client profile
- Stores counsellor profile
- Stores optional counselling service
- Stores appointment mode
- Stores appointment status
- Stores meeting link or physical location
- Stores notes from client, counsellor, and admin
- Stores cancellation metadata
- Stores reminder metadata
- Stores reschedule linkage
- Provides status helper methods
- Provides reminder helper methods

Important fields:

- `uuid`
- `client_profile_id`
- `counsellor_profile_id`
- `counselling_service_id`
- `appointment_date`
- `start_time`
- `end_time`
- `timezone`
- `mode`
- `status`
- `meeting_link`
- `location`
- `client_notes`
- `counsellor_notes`
- `admin_notes`
- `rescheduled_from_appointment_id`
- `cancellation_reason`
- `cancelled_by`
- `cancelled_at`
- `reminder_scheduled_at`
- `reminder_sent_at`
- `created_by`
- `updated_by`

### AppointmentStatusHistory

File:

- `app/Models/AppointmentStatusHistory.php`

Responsibilities:

- Records all important appointment status changes
- Stores previous status
- Stores new status
- Stores reason
- Stores metadata
- Stores user who made the change

## Appointment Modes

| Mode | Meaning |
|---|---|
| online | Appointment happens online |
| in_person | Appointment happens physically |

## Appointment Statuses

| Status | Meaning |
|---|---|
| pending | Client requested appointment, waiting for confirmation |
| confirmed | Counsellor or admin confirmed appointment |
| rescheduled | Original appointment was replaced by a new appointment request |
| completed | Counselling session completed |
| cancelled | Appointment cancelled |
| no_show | Client did not attend confirmed appointment |

## Status Rules

### Client Rules

- Client can create appointments.
- New client appointments are created as `pending`.
- Client can cancel only their own appointments.
- Client can cancel only `pending` or `confirmed` appointments.
- Client can reschedule only their own appointments.
- Client can reschedule only `pending` or `confirmed` appointments.
- Rescheduling marks the old appointment as `rescheduled`.
- Rescheduling creates a new appointment as `pending`.
- Rescheduled appointment keeps a reference to the original appointment through `rescheduled_from_appointment_id`.

### Counsellor Rules

- Counsellor can view only appointments assigned to their counsellor profile.
- Counsellor can confirm only their own `pending` appointments.
- Online appointment confirmation requires a meeting link.
- In-person appointment confirmation requires a location.
- Counsellor can mark only their own `confirmed` appointments as `completed`.
- Counsellor can mark only their own `confirmed` appointments as `no_show`.
- Counsellor cannot close pending, cancelled, completed, no-show, or rescheduled appointments.

### Admin Rules

- Admin can view all appointments.
- Admin can filter and search appointments.
- Admin can update operational appointment statuses.
- Admin confirmation follows the same meeting link/location rules.
- Admin cancellation stores cancellation reason, cancelled user, and cancelled timestamp.
- Admin notes are stored separately from client and counsellor notes.

## Slot Availability Rules

Appointment slot generation uses:

- Counsellor availability rules
- Availability breaks
- Counsellor blocked slots
- Counsellor leave days
- Existing active appointments

A slot is unavailable when:

- It is outside counsellor availability
- It overlaps an active break
- It overlaps a blocked slot
- It overlaps a leave day
- The counsellor already has a pending or confirmed appointment at that time
- The client already has a pending or confirmed appointment at that time

Cancelled, completed, no-show, and rescheduled appointments do not block future slots.

During rescheduling, the current appointment ID is ignored so the appointment does not block its own replacement slot.

## Reminder Rules

Reminder processing uses:

- `reminder_scheduled_at`
- `reminder_sent_at`
- appointment status
- active client user
- active counsellor user

A reminder is eligible only when:

- Appointment status is `confirmed`
- `reminder_scheduled_at` is not null
- `reminder_scheduled_at` is less than or equal to current time
- `reminder_sent_at` is null

Recipients:

- Active client user
- Active counsellor user

Inactive users are skipped.

After processing, `reminder_sent_at` is updated so the reminder is not sent again.

## Reminder Command

Command:

```bash
php artisan appointments:send-reminders
```

Dry run:

```bash
php artisan appointments:send-reminders --dry-run
```

Scheduler hook:

```php
Schedule::command('appointments:send-reminders')->everyFiveMinutes();
```

Server cron requirement:

```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

The server cron path must be configured per hosting environment and should not be committed into the repository.

## Dashboard Metrics

Dashboard metrics are provided through:

- `app/Services/Appointments/AppointmentDashboardMetricService.php`

Metrics are available for:

- Admin dashboard
- Client dashboard
- Counsellor dashboard

Metrics include:

- Total appointments
- Pending appointments
- Confirmed appointments
- Rescheduled appointments
- Completed appointments
- Cancelled appointments
- No-show appointments
- Today appointments
- Upcoming appointments
- Upcoming next 7 days
- Past appointments
- Due reminders

## Routes

### Client Routes

| Route Name | Method | URI | Purpose |
|---|---:|---|---|
| `client.appointments.index` | GET | `/client/appointments` | Client appointment list |
| `client.appointments.store` | POST | `/client/appointments` | Book appointment |
| `client.appointments.cancel` | PATCH | `/client/appointments/{appointment}/cancel` | Cancel appointment |
| `client.appointments.reschedule-slots` | GET | `/client/appointments/{appointment}/reschedule-slots` | Load reschedule slots |
| `client.appointments.reschedule` | PATCH | `/client/appointments/{appointment}/reschedule` | Reschedule appointment |
| `client.counsellors.appointment-slots.index` | GET | `/client/counsellors/{counsellor}/appointment-slots` | Load booking slots from counsellor profile |

### Counsellor Routes

| Route Name | Method | URI | Purpose |
|---|---:|---|---|
| `counsellor.appointments.index` | GET | `/counsellor/appointments` | Counsellor appointment dashboard |
| `counsellor.appointments.confirm` | PATCH | `/counsellor/appointments/{appointment}/confirm` | Confirm appointment |
| `counsellor.appointments.complete` | PATCH | `/counsellor/appointments/{appointment}/complete` | Mark appointment completed |
| `counsellor.appointments.no-show` | PATCH | `/counsellor/appointments/{appointment}/no-show` | Mark appointment no-show |

### Admin Routes

| Route Name | Method | URI | Purpose |
|---|---:|---|---|
| `admin.appointments.index` | GET | `/admin/appointments` | Admin appointment oversight |
| `admin.appointments.update-status` | PATCH | `/admin/appointments/{appointment}/status` | Admin status management |

## Implemented Files

### Models

- `app/Models/Appointment.php`
- `app/Models/AppointmentStatusHistory.php`

### Services

- `app/Services/Appointments/AppointmentSlotService.php`
- `app/Services/Appointments/AppointmentConflictService.php`
- `app/Services/Appointments/AppointmentDashboardMetricService.php`

### Client Controllers and Requests

- `app/Http/Controllers/Client/AppointmentController.php`
- `app/Http/Controllers/Client/AppointmentSlotController.php`
- `app/Http/Requests/Client/StoreAppointmentRequest.php`
- `app/Http/Requests/Client/CancelAppointmentRequest.php`
- `app/Http/Requests/Client/RescheduleAppointmentRequest.php`
- `app/Http/Requests/Client/RescheduleAppointmentSlotRequest.php`
- `app/Http/Requests/Client/AppointmentSlotRequest.php`

### Counsellor Controllers and Requests

- `app/Http/Controllers/Counsellor/AppointmentController.php`
- `app/Http/Requests/Counsellor/ConfirmAppointmentRequest.php`
- `app/Http/Requests/Counsellor/MarkAppointmentOutcomeRequest.php`

### Admin Controllers and Requests

- `app/Http/Controllers/Admin/AppointmentController.php`
- `app/Http/Requests/Admin/UpdateAppointmentStatusRequest.php`

### Notifications and Commands

- `app/Notifications/AppointmentReminderNotification.php`
- `app/Console/Commands/SendAppointmentReminders.php`

### React Pages and Components

- `resources/js/Pages/Client/Counsellors/Show.jsx`
- `resources/js/Pages/Client/Appointments/Index.jsx`
- `resources/js/Pages/Counsellor/Appointments/Index.jsx`
- `resources/js/Pages/Admin/Appointments/Index.jsx`
- `resources/js/Components/Appointments/AppointmentMetricGrid.jsx`

### Updated Existing Files

- `routes/web.php`
- `routes/console.php`
- `resources/js/Config/navigation.js`
- `resources/js/Pages/Admin/Dashboard.jsx`
- `resources/js/Pages/Client/Dashboard.jsx`
- `resources/js/Pages/Counsellor/Dashboard.jsx`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Client/DashboardController.php`
- `app/Http/Controllers/Counsellor/DashboardController.php`
- `app/Models/ClientProfile.php`
- `app/Models/CounsellorProfile.php`
- `app/Models/CounsellingService.php`

## Test Coverage

Current M09 test files:

- `tests/Feature/Appointments/AppointmentSlotServiceTest.php`
- `tests/Feature/Appointments/AppointmentReminderCommandTest.php`
- `tests/Feature/Appointments/AppointmentDashboardMetricServiceTest.php`
- `tests/Feature/Appointments/AppointmentDashboardRouteTest.php`
- `tests/Feature/Client/AppointmentBookingTest.php`
- `tests/Feature/Client/AppointmentListTest.php`
- `tests/Feature/Client/AppointmentCancellationTest.php`
- `tests/Feature/Client/AppointmentReschedulingTest.php`
- `tests/Feature/Counsellor/AppointmentDashboardTest.php`
- `tests/Feature/Counsellor/AppointmentOutcomeTest.php`
- `tests/Feature/Admin/AppointmentOversightTest.php`

## QA Checklist

Completed QA areas:

- Client can book appointment from counsellor profile.
- Client cannot book unavailable slots.
- Client cannot double-book themselves.
- Counsellor cannot be double-booked.
- Client can view only own appointments.
- Client can cancel own pending/confirmed appointments.
- Client cannot cancel other clients' appointments.
- Client can reschedule own pending/confirmed appointments.
- Rescheduling creates a new pending appointment.
- Rescheduling keeps original appointment history.
- Counsellor can view only own appointments.
- Counsellor can confirm pending appointments.
- Online confirmation requires meeting link.
- In-person confirmation requires location.
- Counsellor can complete confirmed appointments.
- Counsellor can mark confirmed appointments as no-show.
- Admin can view all appointments.
- Admin can search and filter appointments.
- Admin can update appointment status.
- Reminder command sends due reminders.
- Reminder command skips ineligible appointments.
- Reminder command does not resend already sent reminders.
- Dashboard metrics are scoped correctly for admin, client, and counsellor.

## Final Module Result

M09 Appointments & Scheduling is completed for MVP scope.

Remaining future enhancements can be handled in later phases:

- Email template design polish
- SMS reminder support
- Calendar integration
- Payment integration
- Appointment reports
- Telehealth provider integration
- Advanced reschedule approval workflow
- Appointment feedback/rating workflow
