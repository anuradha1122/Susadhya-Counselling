
# Module 05: Client Registration and Profile Management

## Status

Completed.

## Purpose

This module implements public client registration, authenticated client profile management, emergency contact management, counselling preference management, privacy and consent settings, and administrator-side client management.

This module follows the project workbook item:

- M04 Client Registration & Profile
- Phase: P2
- Priority: MVP
- Roles: Client, Admin
- Scope: Self-registration, demographics, contacts, emergency contact, preferences, privacy controls
- Acceptance: Client can maintain a validated profile and emergency contact

## Completed Features

### Public Client Registration

Clients can register from the public registration page.

Registration captures:

- First name
- Last name
- Preferred name
- Email
- Phone
- Preferred language
- Password
- Terms acceptance
- Privacy policy acceptance
- Optional communication consent

After registration:

- A user account is created.
- The user receives the `client` role.
- A client profile is created.
- Default counselling preferences are created.
- Consent history records are created.
- The user is logged in and redirected to the correct dashboard.

### Client Dashboard

Clients are redirected to their own dashboard after login.

The client dashboard links to:

- Client profile
- Emergency contacts
- Counselling preferences
- Privacy and consent settings

### Client Layout and Navigation

A dedicated `ClientLayout` was added.

Client sidebar navigation includes:

- Dashboard
- Client Profile
- Emergency Contacts
- Counselling Preferences
- Privacy & Consent
- Appointments, disabled for now
- Payments, disabled for now

### Client Profile Management

Clients can view and update their own profile.

Profile fields include:

- First name
- Last name
- Preferred name
- Date of birth
- Gender
- Pronouns
- Phone
- Alternate phone
- Address line 1
- Address line 2
- City
- District
- Province
- Postal code
- Preferred language
- Preferred contact method
- Occupation
- Marital status

Profile completion status is refreshed after profile updates.

### Emergency Contact Management

Clients can manage their own emergency contacts.

Supported actions:

- List contacts
- Create contact
- Edit contact
- Delete contact
- Set primary contact
- Control whether the contact may be used during emergencies

Safety rule:

- A client cannot view, edit, update, or delete another client's emergency contact.

### Counselling Preferences

Clients can view and update counselling preferences.

Preference fields include:

- Preferred counselling mode
- Preferred counsellor gender
- Preferred language
- General availability notes
- Accessibility requirements
- Additional preferences

### Privacy and Consent Settings

Clients can view and update privacy settings.

Supported settings:

- Communication consent
- Emergency contact permission
- Email update preference
- SMS update preference
- WhatsApp update preference
- Share profile with assigned counsellor preference

Consent history is recorded when relevant permissions are newly accepted.

### Admin Client Management

Administrators can manage client profiles from the admin area.

Admin features include:

- Client index
- Search clients
- Filter by status
- Filter by profile completion state
- View full client details
- Activate client
- Deactivate client
- Archive client
- Restore archived client

When an admin deactivates or archives a client profile, the linked user account is also deactivated.

When an admin activates or restores a client profile, the linked user account is reactivated.

## Database Tables Added

### client_profiles

Stores the main client profile.

Important fields:

- user_id
- first_name
- last_name
- preferred_name
- date_of_birth
- gender
- pronouns
- alternate_phone
- address_line_1
- address_line_2
- city
- district
- province
- postal_code
- preferred_language
- preferred_contact_method
- occupation
- marital_status
- profile_completed_at
- terms_accepted_at
- privacy_policy_accepted_at
- communication_consent
- communication_consent_at
- emergency_contact_permission
- emergency_contact_permission_at
- privacy_preferences
- status
- created_by
- updated_by
- archived_at
- archived_by

### client_emergency_contacts

Stores emergency contacts for client profiles.

Important fields:

- client_profile_id
- name
- relationship
- phone
- alternate_phone
- email
- may_contact_in_emergency
- is_primary
- created_by
- updated_by

### client_preferences

Stores counselling preferences for client profiles.

Important fields:

- client_profile_id
- preferred_counselling_mode
- preferred_counsellor_gender
- preferred_language
- general_availability_notes
- accessibility_requirements
- additional_preferences
- created_by
- updated_by

### client_consents

Stores consent history.

Important fields:

- client_profile_id
- consent_type
- version
- accepted_at
- ip_address
- user_agent
- metadata
- recorded_by

Consent types:

- terms
- privacy_policy
- communication
- emergency_contact

## Backend Files Added

### Controllers

- app/Http/Controllers/Admin/ClientController.php
- app/Http/Controllers/Client/DashboardController.php
- app/Http/Controllers/Client/ProfileController.php
- app/Http/Controllers/Client/EmergencyContactController.php
- app/Http/Controllers/Client/PreferenceController.php
- app/Http/Controllers/Client/PrivacySettingsController.php

### Requests

- app/Http/Requests/Auth/StoreClientRegistrationRequest.php
- app/Http/Requests/Client/UpdateClientProfileRequest.php
- app/Http/Requests/Client/StoreClientEmergencyContactRequest.php
- app/Http/Requests/Client/UpdateClientEmergencyContactRequest.php
- app/Http/Requests/Client/UpdateClientPreferenceRequest.php
- app/Http/Requests/Client/UpdateClientPrivacySettingsRequest.php

### Models

- app/Models/ClientProfile.php
- app/Models/ClientEmergencyContact.php
- app/Models/ClientPreference.php
- app/Models/ClientConsent.php

### Policy

- app/Policies/ClientProfilePolicy.php

### Factories

- database/factories/ClientProfileFactory.php
- database/factories/ClientEmergencyContactFactory.php
- database/factories/ClientPreferenceFactory.php
- database/factories/ClientConsentFactory.php

## Frontend Files Added

### Layout

- resources/js/Layouts/ClientLayout.jsx

### Auth

- resources/js/Pages/Auth/Register.jsx

### Client Pages

- resources/js/Pages/Client/Dashboard.jsx
- resources/js/Pages/Client/Profile/Show.jsx
- resources/js/Pages/Client/Profile/Edit.jsx
- resources/js/Pages/Client/EmergencyContacts/Index.jsx
- resources/js/Pages/Client/EmergencyContacts/Create.jsx
- resources/js/Pages/Client/EmergencyContacts/Edit.jsx
- resources/js/Pages/Client/EmergencyContacts/Partials/EmergencyContactForm.jsx
- resources/js/Pages/Client/Preferences/Show.jsx
- resources/js/Pages/Client/Preferences/Edit.jsx
- resources/js/Pages/Client/Privacy/Show.jsx
- resources/js/Pages/Client/Privacy/Edit.jsx

### Admin Pages

- resources/js/Pages/Admin/Clients/Index.jsx
- resources/js/Pages/Admin/Clients/Show.jsx

## Files Updated

### Backend

- app/Http/Controllers/Auth/RegisteredUserController.php
- app/Http/Controllers/DashboardRedirectController.php
- app/Models/User.php
- app/Providers/AppServiceProvider.php
- database/seeders/RolePermissionSeeder.php
- routes/auth.php
- routes/web.php

### Frontend

- resources/js/Config/navigation.js
- resources/js/Pages/Auth/Register.jsx

## Routes Added

### Public Auth

- GET /register
- POST /register

### Client

- GET /client/dashboard
- GET /client/profile
- GET /client/profile/edit
- PATCH /client/profile
- GET /client/emergency-contacts
- GET /client/emergency-contacts/create
- POST /client/emergency-contacts
- GET /client/emergency-contacts/{contact}/edit
- PATCH /client/emergency-contacts/{contact}
- DELETE /client/emergency-contacts/{contact}
- GET /client/preferences
- GET /client/preferences/edit
- PATCH /client/preferences
- GET /client/privacy
- GET /client/privacy/edit
- PATCH /client/privacy

### Admin

- GET /admin/clients
- GET /admin/clients/{client}
- PATCH /admin/clients/{client}/status
- DELETE /admin/clients/{client}
- PATCH /admin/clients/{client}/restore

## Permissions Added

- dashboard.client.view
- clients.view
- clients.create
- clients.update
- clients.archive

## Roles Updated

### client

Receives:

- dashboard.client.view

### admin

Receives:

- clients.view
- clients.create
- clients.update
- clients.archive

### super_admin

Has full access through the global gate bypass.

## Validation Rules

### Registration

- First name is required.
- Last name is required.
- Email is required and unique.
- Phone is required and validated.
- Preferred language is required.
- Password must be confirmed.
- Terms acceptance is required.
- Privacy policy acceptance is required.
- Communication consent is optional.

### Client Profile

- First name is required.
- Last name is required.
- Date of birth must be before today.
- Gender must be one of the accepted values.
- Preferred language is required.
- Preferred contact method is required.
- Phone fields must use a valid format.

### Emergency Contact

- Name is required.
- Relationship is required.
- Phone is required.
- Email is optional but must be valid if provided.
- Primary contact flag is supported.
- Emergency contact permission flag is supported.

### Preferences

- Preferred counselling mode is required.
- Preferred counsellor gender is required.
- Preferred language is required.
- Notes fields are optional.

### Privacy

- Communication consent is supported.
- Emergency contact permission is supported.
- Email, SMS, and WhatsApp update preferences are supported.
- Profile sharing preference is supported.

## Tests Added

### Client Tests

File:

- tests/Feature/Client/ClientRegistrationAndProfileTest.php

Covered:

- Client registration creates user, profile, preference, and consent records.
- Preferred language is required during registration.
- Client can view and update own profile.
- Future date of birth is rejected.
- Client can create, update, and delete emergency contacts.
- Client cannot edit another client's emergency contact.
- Client can update counselling preferences.
- Client can update privacy settings and consent history is recorded.

### Admin Tests

File:

- tests/Feature/Admin/ClientManagementTest.php

Covered:

- Admin can view client index and detail pages.
- Admin can filter clients by search, status, and completion.
- Admin without clients.view cannot access client management.
- Admin can deactivate and reactivate clients.
- Admin can archive and restore clients.
- Invalid admin status updates are rejected.

## Verification Commands

The following checks passed:

```bash
./vendor/bin/pint --test \
    database/factories/ClientProfileFactory.php \
    database/factories/ClientEmergencyContactFactory.php \
    database/factories/ClientPreferenceFactory.php \
    database/factories/ClientConsentFactory.php \
    tests/Feature/Client/ClientRegistrationAndProfileTest.php \
    tests/Feature/Admin/ClientManagementTest.php \
    app/Http/Controllers/Admin/ClientController.php \
    routes/web.php

npm run build

php artisan test \
    tests/Feature/Client/ClientRegistrationAndProfileTest.php \
    tests/Feature/Admin/ClientManagementTest.php
