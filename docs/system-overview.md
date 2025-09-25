# System Overview

This document captures the current behaviour of the Pizza Employee Management System as implemented in this repository.

## Technology Stack
- **Backend**: Laravel 12 (PHP 8.2) with Filament 3 for both admin and employee panels.
- **PDF Generation**: `barryvdh/laravel-dompdf` renders contract PDFs from HTML templates.
- **SMS Delivery**: `xenon/laravelbdsms` plus `App\Services\SmsService` for provider management.
- **Frontend Assets**: Vite with Tailwind CSS stubs; most UI is delivered through Filament components and Blade views.
- **Persistence**: MySQL (primary), Redis (queues/cache), and Mailpit for local email testing.
- **Containerisation**: Docker Compose environment for PHP-FPM, Nginx, database, Redis, Adminer, phpMyAdmin, and Mailpit services.

## Core Domain Model
- **Branches & Positions**: Define organisation hierarchy and link to contract templates. (`branches`, `positions`)
- **Onboarding Invites**: Drive candidate onboarding lifecycle; each invite owns KYC verifications, documents, and eventual contracts. (`onboarding_invites`)
- **KYC Verifications**: Store submitted identity data, profile photo paths, status, and HR review metadata. (`kyc_verifications`)
- **Employment Contracts**: Reference invites, track status, store generated and signed PDFs, signature image paths, and timestamps. (`employment_contracts`)
- **Employee Profiles & Users**: Created when KYC is approved; profiles keep employment metadata and branch/position links. (`employee_profiles`, `users`)
- **SMS Settings**: Persist provider credentials and defaults for outbound messaging. (`sms_settings`)

## Admin Panel (Filament “admin”)
- **Onboarding Management**: Create invites, send email links, or fill KYC on behalf of candidates. Actions update invite status, create pending KYC records, and use `EmailService` for notifications.
- **KYC Review**: HR can approve or reject submissions. Approval autogenerates user accounts, employee profiles, triggers welcome emails, and marks invites complete.
- **Contract Operations**: Generate PDFs from templates, send notifications, and allow download of signed documents.
- **Additional Resources**: CRUD for branches, positions, employee profiles, users, and SMS gateways, with helpful counters/filters.
- **Settings**: HR signature upload stored in `storage/app/private/hr-signatures/hr-signature.png` for embedding in signed contracts.

## Employee Portal (Filament “portal”)
- Separate Filament panel at `/portal` with dashboard, settings, profile update, and password change flow.
- `MustChangePasswordMiddleware` forces new users (from KYC approval) to set a password before accessing the portal.
- Widgets show profile details and contract status/download links using private storage routes.

## Onboarding Workflow Summary
1. **Invite Creation** – HR issues an invite in Filament; optional action sends email and seeds pending KYC session.
2. **Candidate KYC** – Candidate follows `/kyc/{token}`, submits profile data + photo; status becomes `pending_hr_review`.
3. **HR Approval** – Admin approves in Filament, triggering user & employee profile creation, welcome email, and invite completion.
4. **Contract Generation** – Contracts are generated from templates, emailed, and exposed at `/contract/{token}` for signature.
5. **Signature Capture** – Contract page records click-wrap acceptance, stores signature image, regenerates signed PDF, updates status, and sends notifications.

## Integrations & Notifications
- **Email**: `ContractNotificationMail` handles sent/signed/completed lifecycle; `WelcomeEmailWithPassword` delivers credentials to new employees.
- **SMS**: `SmsService` dynamically hydrates provider configs from `sms_settings` and exposes REST endpoints under `/api/sms/*`.
- **Logging**: Significant events (KYC submission, contract generation, acceptance) are logged with context for auditability.

## Infrastructure Notes
- Docker volumes persist MySQL (`.docker/db/data`) and Redis data (`.docker/redis/data`).
- All CLI commands should be executed inside containers using `docker compose exec <service> <command>` (for example, `docker compose exec php php artisan migrate`).
- Helper scripts (`scripts/backup-database.sh`, `restore-database.sh`, `verify-persistence.sh`) support backup/restore and persistence verification workflows.
- Environment defaults use database-backed queues and sessions; Mailpit handles outbound email in development.

## Known Gaps & Risks
- **Documentation Drift**: `docs/architecture.md` describes Passport-based multi-tenancy, which is not implemented in this codebase.
- **Routing Gap**: `OnboardingInvite::getInviteUrlAttribute` references a non-existent `onboarding.invite` route; emails must rely on `route('kyc.show', $invite->token)` instead.
- **KYC Liveness**: `KycService` simulates liveness detection; production deployments need integration with a real provider.
- **Legacy Portal Views**: `portal` Blade views and `PortalController` remain but are unused; consider removing or re-routing to avoid confusion.
- **Testing Coverage**: Only scaffolded Pest tests exist—behavioural coverage is effectively zero.
- **Storage Access**: Signed contracts are served via `Storage::url` on the private disk; ensure secure handling when deploying behind a CDN or reverse proxy.
