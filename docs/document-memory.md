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

## Notes for Future Sessions
- Address the routing gap for `OnboardingInvite::getInviteUrlAttribute` before relying on invite-generated URLs.
- Replace simulated KYC liveness checks with the intended third-party provider integration when ready.
- Consider realigning or rewriting `docs/architecture.md` to reflect the implemented single-tenant approach.
- Expand automated test coverage—currently limited to framework scaffolding.
- Follow the documented instruction to run shell commands via `docker compose exec <service> ...` and to update this file after each iteration automatically.
