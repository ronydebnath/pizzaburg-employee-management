# SRS (Scope: FR-001 → FR-029)

## A) Onboarding & Contract
FR-001 Create pre-approved onboarding invite
FR-002 Magic link + OTP issuance
FR-003 OTP verification & login
FR-004 KYC capture with selfie liveness (provider-agnostic)
FR-005 Joining date policy window
FR-006 Position-to-contract template mapping
FR-007 Contract acceptance (click-wrap)
FR-008 Signature image upload
FR-009 Contract delivery & storage

## B) Authentication & Identity
FR-010 Send OTP
FR-011 Verify OTP
FR-012 Session management
FR-013 Device binding (optional)
FR-014 New device alert
FR-015 Account recovery via phone re-verification
FR-016 Rate limiting and throttling
FR-017 Anti-automation on OTP
FR-018 Logout and session revocation
FR-019 JWT/OAuth2 issuance (Passport)

## C) Employee Profile & Records
FR-020 Profile view
FR-021 Field editability by role
FR-022 Change request workflow
FR-023 HR approval of changes
FR-024 Audit log (immutable)
FR-025 Profile data export (tenant)
FR-026 Consent & policy links
FR-027 Document Center linkage
FR-028 Branch reassignment rules
FR-029 Position/grade change with effective dating

## Non-Functional Requirements
- Security: OAuth2/JWT, RBAC, encrypted PII columns, S3 signed URLs.
- Performance: p95 < 200ms for read endpoints under 200 RPS per tenant.
- Observability: request IDs, structured logs, health checks, metrics.
- Reliability: Idempotent OTP/send endpoints, retries on storage/queue.