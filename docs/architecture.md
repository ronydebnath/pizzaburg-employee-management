# Architecture
- Multitenancy: column-scoped (tenant_id). Global scope + middleware.
- Auth: Passport; Access + Refresh tokens. Session revocation via token blacklisting.
- Authentication: Token-based magic links for simplified access.
- KYC: KycService abstraction; provider adapters (e.g., Sumsub/Onfido) later.
- Contracts: ContractService generates PDFs from templates; stores in S3; click-wrap evidence in AuditLog.
- Audit: AuditService writes append-only hash chain {id, actor_id, action, payload_hash, prev_hash}.

# Data Model (tables)
tenants(id, name, ...);
users(id, tenant_id, phone, email?, password?, role, status, last_login_at);
employee_profiles(id, tenant_id, user_id, first_name, last_name, dob, branch_id, position_id, grade, effective_from, effective_to, meta jsonb, pii_encrypted);
devices(id, tenant_id, user_id, device_fingerprint, last_seen_at, trusted boolean);
contracts(id, tenant_id, user_id, template_key, version, signed_at, storage_path, signature_image_path);
documents(id, tenant_id, user_id, kind, storage_path, meta jsonb);
audit_logs(id, tenant_id, actor_id, action, resource, resource_id, payload_hash, prev_hash, created_at);
branches(id, tenant_id, name, code);
positions(id, tenant_id, name, grade, contract_template_key);
profile_change_requests(id, tenant_id, user_id, field, old_value, new_value, status, approved_by, approved_at);

# Request Flow Examples
- FR-002/003: /onboarding/invite → create magic link → /kyc/{token} → direct access to KYC form.
- FR-007/008/009: /onboarding/contract/{id}/accept → capture click-wrap + signature image → store → email delivery.