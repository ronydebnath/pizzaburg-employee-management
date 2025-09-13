# SRS (Scope: FR-001 → FR-029)

## A) Onboarding & Contract
✅ FR-001 Create pre-approved onboarding invite - **COMPLETED** (OnboardingInvite model, Filament resource)
🔄 FR-002 Magic link + OTP issuance - **IN PROGRESS** (Notification service created, OTP pending)
⏳ FR-003 OTP verification & login - **PENDING**
⏳ FR-004 KYC capture with selfie liveness (provider-agnostic) - **PENDING**
✅ FR-005 Joining date policy window - **COMPLETED** (OnboardingInvite expires_at field)
✅ FR-006 Position-to-contract template mapping - **COMPLETED** (Position.contract_template_key)
🔄 FR-007 Contract acceptance (click-wrap) - **IN PROGRESS** (Contract model created, UI pending)
✅ FR-008 Signature image upload - **COMPLETED** (EmploymentContract.signature_file_path)
✅ FR-009 Contract delivery & storage - **COMPLETED** (ContractGenerationService, PDF storage)

## B) Authentication & Identity
🔄 FR-010 Send OTP - **IN PROGRESS** (Notification service created, OTP logic pending)
⏳ FR-011 Verify OTP - **PENDING**
⏳ FR-012 Session management - **PENDING**
⏳ FR-013 Device binding (optional) - **PENDING**
⏳ FR-014 New device alert - **PENDING**
⏳ FR-015 Account recovery via phone re-verification - **PENDING**
⏳ FR-016 Rate limiting and throttling - **PENDING**
⏳ FR-017 Anti-automation on OTP - **PENDING**
⏳ FR-018 Logout and session revocation - **PENDING**
⏳ FR-019 JWT/OAuth2 issuance (Passport) - **PENDING**

## C) Employee Profile & Records
✅ FR-020 Profile view - **COMPLETED** (EmployeeProfile model, Filament resource)
✅ FR-021 Field editability by role - **COMPLETED** (User roles, Filament forms)
⏳ FR-022 Change request workflow - **PENDING**
⏳ FR-023 HR approval of changes - **PENDING**
⏳ FR-024 Audit log (immutable) - **PENDING**
⏳ FR-025 Profile data export (tenant) - **PENDING**
⏳ FR-026 Consent & policy links - **PENDING**
✅ FR-027 Document Center linkage - **COMPLETED** (EmployeeDocument model)
✅ FR-028 Branch reassignment rules - **COMPLETED** (Branch-based organization)
✅ FR-029 Position/grade change with effective dating - **COMPLETED** (EmployeeProfile effective_from/to)

## Non-Functional Requirements
🔄 Security: OAuth2/JWT, RBAC, encrypted PII columns, S3 signed URLs. - **IN PROGRESS** (RBAC implemented, OAuth2 pending)
⏳ Performance: p95 < 200ms for read endpoints under 200 RPS per tenant. - **PENDING**
✅ Observability: request IDs, structured logs, health checks, metrics. - **COMPLETED** (Comprehensive logging implemented)
⏳ Reliability: Idempotent OTP/send endpoints, retries on storage/queue. - **PENDING**

## Progress Summary
- **Completed**: 8/29 requirements (28%)
- **In Progress**: 4/29 requirements (14%)
- **Pending**: 17/29 requirements (58%)

### Key Achievements
✅ **Core Onboarding System**: Complete invitation workflow with secure tokens
✅ **Document Management**: File upload and verification system
✅ **Contract Generation**: PDF creation with signature embedding
✅ **Branch Organization**: Multi-location support with role-based access
✅ **Admin Interface**: Full Filament admin panel for HR management
✅ **Notification System**: Email and SMS invitation framework