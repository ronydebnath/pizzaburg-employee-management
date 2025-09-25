# Document Memory

This log captures the key actions completed during the current documentation pass so future collaborators can pick up quickly.

## What Was Done
- Reviewed repository structure, domain models, Filament resources, and supporting services to understand current behaviour.
- Authored `docs/system-overview.md` summarising tech stack, workflows, integrations, infrastructure, and known gaps.
- Identified mismatches between legacy architecture documentation and the actual implementation (single-tenant, session auth).
- Documented Docker execution requirement and automatic document-memory updates in the relevant docs (`system-overview.md`, `starter-prompt.md`).
- Added National ID image capture to candidate and HR KYC flows (validation, storage, and UI), including Filament updates and service handling.
- Extended KYC verification admin form to manage the National ID image without requiring new migrations.
- Added National ID preview to the KYC verification view page so admins can inspect uploaded IDs directly.
- Adjusted KYC verification image thumbnails (profile & national ID) to use signed routes so previews render correctly.
- Fixed HR Fill KYC workflow by auto-generating an `employee_id` before creating employee profiles.
- Ensured Filament uploads for HR/admin KYC use the `private` disk so image links resolve via the secure download route.
- Updated employee portal navigation: removed Settings, exposed Update Profile, and added a My Contract page with download actions.
- Fixed password storage by relying on the model's `hashed` cast (UserResource, HR auto-provision, and change-password flow) so employee logins respect newly set credentials.
- Expanded the employee portal Update Profile page to show current details, accept edits (including ID uploads), and submit them as a new `profile_update` KYC verification for HR review.
- Added HR-side handling to approve those updates without forcing a new contract (or welcome email) while still respecting the existing onboarding flow for fresh hires.
- Patched the employee update form to back its state with an array (`$data`) so Livewire no longer complains about missing component properties.
- Removed the legacy profile/contract widgets from the employee portal dashboard so the layout now only shows the account widget (clean slate for future cards).

## Notes for Future Sessions
- Address the routing gap for `OnboardingInvite::getInviteUrlAttribute` before relying on invite-generated URLs.
- Replace simulated KYC liveness checks with the intended third-party provider integration when ready.
- Consider realigning or rewriting `docs/architecture.md` to reflect the implemented single-tenant approach.
- Expand automated test coverage—currently limited to framework scaffolding.
- Follow the documented instruction to run shell commands via `docker compose exec <service> ...` and to update this file after each iteration automatically.
