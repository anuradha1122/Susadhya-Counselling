# Module 03 — Counsellor Management

## Status

Completed.

## Purpose

Module 03 provides administrative management of counsellor profiles and
their associated user accounts, specializations, languages and professional
qualifications.

## Authorization

The module uses the following permissions:

- `counsellors.view`
- `counsellors.create`
- `counsellors.update`
- `counsellors.archive`

The `CounsellorProfilePolicy` provides view, create, update, archive and
restore authorization. Archived profiles cannot be edited or archived again.

Restore access uses `counsellors.update`.

## Features

- Search counsellors by name, email, registration number, title, NIC or city
- Filter counsellors by status
- Filter counsellors by specialization
- Create counsellor profiles from eligible active users
- Assign the `counsellor` role without removing existing roles
- Record specializations
- Record languages and proficiency levels
- Record multiple professional qualifications
- Activate or deactivate the associated user account
- Archive counsellors without deleting their records
- Restore archived counsellors
- Display complete counsellor profile details
- Paginated administrative listing
- Permission-aware actions and navigation

## Status synchronization

- An active counsellor has an active user account.
- An inactive counsellor has an inactive user account.
- Archiving a counsellor deactivates the user account.
- Restoring a counsellor changes the profile to active and reactivates the
  user account.

## Database tables

- `counsellor_profiles`
- `counsellor_qualifications`
- `specializations`
- `languages`
- `counsellor_profile_specialization`
- `counsellor_profile_language`

Each user can have at most one counsellor profile.

Qualifications are deleted automatically when their parent counsellor profile
is deleted. Specialization and language pivot records are also deleted with
the parent profile.

## Routes

- `GET /admin/counsellors`
- `GET /admin/counsellors/create`
- `POST /admin/counsellors`
- `GET /admin/counsellors/{counsellor}`
- `GET /admin/counsellors/{counsellor}/edit`
- `PUT /admin/counsellors/{counsellor}`
- `DELETE /admin/counsellors/{counsellor}`
- `PATCH /admin/counsellors/{counsellor}/restore`

## Reference data

The `CounsellorReferenceSeeder` creates the initial counsellor
specializations and the Sinhala, Tamil and English languages.

The seeder is idempotent and can safely be executed more than once.

## Testing

Feature coverage includes:

- Guest redirects
- Authorization failures
- Index page rendering
- Search
- Status filtering
- Specialization filtering
- Eligible user selection
- Complete profile creation
- Role assignment
- Status and user-account synchronization
- Validation of unique fields
- Validation of inactive reference records
- Profile display
- Relationship and qualification updates
- Editing inactive counsellors
- Existing-role preservation
- Archive restrictions
- Restore behavior

## Completion criteria

Module 03 is complete when:

- All migrations run successfully on a fresh SQLite database.
- Counsellor feature tests pass.
- The complete application test suite passes.
- Pint passes.
- The production frontend build succeeds.
- Manual create, view, edit, archive and restore checks pass.
