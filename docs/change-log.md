# Change Log

## 2026-09-02 - M09 Appointments & Scheduling Completed

Completed the M09 Appointments & Scheduling MVP module.

Added:

- Appointment database structure
- Appointment status history
- Appointment slot generation
- Appointment conflict detection
- Client appointment booking
- Client appointment list
- Client appointment cancellation
- Client appointment rescheduling
- Counsellor appointment dashboard
- Counsellor appointment confirmation
- Counsellor appointment completion
- Counsellor no-show handling
- Admin appointment oversight
- Admin appointment status management
- Appointment reminder notification
- Appointment reminder sending command
- Scheduler hook for reminders
- Appointment dashboard metrics
- Final appointment QA documentation

Updated:

- Client dashboard
- Counsellor dashboard
- Admin dashboard
- Client navigation
- Counsellor navigation
- Admin navigation
- Appointment module documentation
- Development log
- Next chat handover

MVP rules completed:

- Appointments cannot double-book counsellors.
- Appointments cannot double-book clients.
- Clients can cancel and reschedule only their own eligible appointments.
- Counsellors can manage only their own assigned appointments.
- Admin can oversee all appointments.
- All major appointment status changes are recorded in status history.
- Reminder sending is idempotent through `reminder_sent_at`.
