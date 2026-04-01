# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GoFusion Backend is a Laravel 12 API application for an employee engagement and sustainability platform. The system features gamified challenges, quizzes, carbon footprint tracking, and a rewards system. It supports multi-language (English/French), role-based access control, and includes both mobile API endpoints and a backoffice admin interface.

## Development Commands

### Setup
```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed
```

### Development Server
```bash
# Run all services (server, queue, vite) - recommended
composer dev

# Or run individually
php artisan serve                    # API server on port 8000
php artisan queue:listen --tries=1   # Queue worker
npm run dev                          # Vite dev server
```

### Testing
```bash
# Run all tests (uses Pest)
composer test
# Or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with filter
php artisan test --filter test_name
```

### Code Quality
```bash
# Format code (Laravel Pint)
./vendor/bin/pint

# View logs in real-time
php artisan pail
```

### Database
```bash
# Create fresh database with seeds
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate
```

### Shortcuts (bash.sh)
The project includes shortcuts in `bash.sh`:
- `o:c` = `php artisan optimize:clear`
- `m:f` = `php artisan migrate:fresh`
- `s` = `php artisan serve`
- `a` = `php artisan`

## Architecture

### API Structure

**Mobile API (v1)**: `/api/v1/*`
- Located in `routes/api/v1.php`
- Authenticated via Laravel Sanctum
- Multi-language support via `set.lang` middleware
- Versioned for backwards compatibility

**Backoffice Admin**: `/admin/*`
- Routes in `routes/backoffice/admin.php`
- Web-based admin interface using Blade views
- Protected by `AdminAuthMiddleware`

**Company Admin**: `/company-admin/*`
- Routes in `routes/backoffice/company_admin.php`
- Separate interface for company administrators
- Protected by `IsCompanyAdmin` middleware

### Service Layer Pattern

Controllers delegate business logic to service classes in `app/Services/`. Services handle:
- Data manipulation and validation
- Database transactions
- Business rules and calculations
- File exports (Excel/CSV using PhpSpreadsheet)

Example: `QuizService`, `UserService`, `ChallengeService`

### Authorization

Uses Spatie Laravel Permission package for RBAC:
- **Admin**: Full system access
- **Company Admin**: Manage company users and campaigns
- **User**: Mobile app users

Permissions are checked via:
- `check.permission` middleware
- `role` middleware
- Permission config in `config/permission.php`

### Key Domain Concepts

**Go Sessions**: Core workflow system
- Each session contains multiple steps (`GoSessionStep`)
- Steps can be: Quiz, Image Validation, Event, Spin Wheel, Challenge, Survey
- Users progress through steps to earn points
- Tied to campaign seasons for time-based competitions

**Campaign Seasons**: Time-bound competitions
- Department-based leaderboards
- Reward tiers based on point ranges
- Managed in `CampaignsSeason` model

**Carbon Footprint Tracking**: Environmental impact calculations
- Stored in `UserCarbonFootprint`
- Calculated per user activity

**Posts System**: Social feed functionality
- Users create posts with media
- Comments, reactions, and mentions
- Content moderation via `PostReport`

### Localization

- Default language: French (`fr`)
- Supported: English (`en`), French (`fr`)
- Language files in `lang/{locale}/`
- `SetLangMiddleware` auto-detects user language from database or request param
- Use `trans('key')` for all user-facing strings

### Common Traits

**ApiResponse** (`app/Traits/ApiResponse.php`):
- Standardized JSON responses: `success()`, `error()`, `unauthorizedResponse()`
- All API endpoints should use these methods

**AppCommonFunction** (`app/Traits/AppCommonFunction.php`):
- Shared utility methods across services

**OTPTrait**: OTP generation and verification helpers

### Helper Functions

Global helpers in `app/Helpers/helper.php`:
- `generateOtp($length)`: Generate numeric OTP
- `generateToken()`: Generate MD5 token
- `paginationData()`: Format paginated responses

### Jobs and Events

**Jobs** (`app/Jobs/`):
- `SendFirebaseNotification`: Async push notifications via Firebase

**Events** (`app/Events/`):
- `UserScoreEvent`: Triggered when user earns points
- `UserProgressEvent`: Session progress updates
- `StoreCompleteUserSessionEvent`: Session completion

**Queue**: Uses database driver (`QUEUE_CONNECTION=database`)
- Jobs stored in `jobs` table
- Process with `php artisan queue:listen`

### File Imports

`ImportFileController` + `ImportFileDataStoreService` + `ImportFileDataValidationService`:
- Handles bulk data imports (CSV/Excel)
- Validation before storage
- Used for seeding data from spreadsheets

### Firebase Integration

- Configuration: `gofusion-firebase.json`
- Package: `kreait/firebase-php`
- Used for push notifications

## Database Conventions

- Migration naming: Chronological with descriptive names
- Models use singular names (e.g., `User`, `Quiz`)
- Relationships defined in models with proper naming
- Soft deletes used where applicable

## Testing with Pest

- Test files in `tests/Feature/` and `tests/Unit/`
- Configuration in `tests/Pest.php`
- Uses SQLite in-memory database for testing
- Extend `Tests\TestCase` class

## Environment Variables

Key configurations in `.env`:
- `APP_LOCALE`: Default locale (en/fr)
- `DB_*`: Database credentials
- `QUEUE_CONNECTION`: Job queue driver (database)
- `MAIL_*`: Email configuration (uses log driver in dev)
- Firebase credentials (if using push notifications)

## Constants

Application constants in `config/constants.php`:
- `ROLES`: System role names
- `STATUS`: Entity status values (active, pending, blocked, etc.)
- `LEVELS`: User progression levels (Starter 🌱 to Legend 🌟)
- `TRANSACTION_TYPE`: Credit/debit types

## Response Format

All API responses follow this structure:
```json
{
    "status": true/false,
    "message": "Human readable message",
    "result": {} or [],
    "code": 200
}
```

Use `ApiResponse` trait methods to maintain consistency.

## Important Notes

- Always wrap file paths with spaces in quotes when using bash commands
- Use services for complex business logic, not controllers
- Leverage API Resources (`app/Http/Resources/`) for response formatting
- Check permissions before modifying user data
- Translation keys must exist in both `lang/en/` and `lang/fr/`
- Queue workers must be running for notifications and async tasks
