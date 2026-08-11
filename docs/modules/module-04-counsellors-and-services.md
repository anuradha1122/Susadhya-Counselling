# Module 04 — Counsellors and Counselling Services

## Status

Completed.

## Purpose

Module 04 provides the administration functions required to manage counsellor records, service categories, and counselling services.

## Permissions

| Permission | Purpose |
|---|---|
| `counsellors.view` | View counsellor records |
| `counsellors.create` | Create counsellor records |
| `counsellors.update` | Update counsellor records |
| `counsellors.archive` | Archive counsellor records |
| `services.view` | View service categories and counselling services |
| `services.create` | Create service categories and counselling services |
| `services.update` | Update and restore service categories and counselling services |
| `services.archive` | Archive service categories and counselling services |

## Counsellor management

The counsellor administration feature includes:

- Counsellor listing and filtering
- Counsellor profile creation
- Counsellor profile display
- Counsellor profile editing
- Professional and contact information
- Counsellor status management
- Archive and restore actions
- Permission-based access control

## Service categories

Service categories support:

- Name and unique slug
- Description
- Display order
- Active, inactive, and archived states
- Category listing and filtering
- Category details
- Archive and restore actions
- Related counselling-service display
- Protection against selecting archived categories for new services

## Counselling services

Each counselling service contains:

- Service category
- Name and unique slug
- Short and full descriptions
- Session duration
- Service mode
- Target age group
- Optional custom minimum and maximum ages
- Price and currency
- Display order
- Active, inactive, and archived states

Supported service modes are:

- Online
- In person
- Online and in person

Supported target age groups are:

- Children
- Adolescents
- Adults
- Seniors
- All ages
- Custom age range

## Archive and restore behavior

Archiving retains the record and records:

- Archived timestamp
- User who performed the archive action

An archived service cannot be edited.

When a service is restored:

- It becomes active when its category is active.
- It becomes inactive when its category is inactive.
- Archive metadata is cleared.

## Validation rules

The module validates:

- Required category and service fields
- Unique slugs
- Supported durations
- Supported service modes
- Supported target age groups
- Non-negative prices
- Three-character currency codes
- Valid display order
- Custom minimum and maximum ages
- Maximum age greater than or equal to minimum age
- Active or inactive category selection for services

## Administration navigation

The administration navigation includes:

- Counsellors
- Service Categories
- Counselling Services

Navigation visibility is controlled by the relevant permissions.

## Testing

Feature tests cover:

- Authentication
- Permission enforcement
- Index and detail pages
- Create and update operations
- Validation
- Unique slugs
- Category restrictions
- Custom age ranges
- Search and filters
- Archive behavior
- Restore behavior
- Category-dependent restore status
