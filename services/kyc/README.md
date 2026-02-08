# KYC Service

Laravel microservice for KYC eligibility, submit, status, progress, and Didit integration. Called by the monolith API gateway when `KYC_SERVICE_ENABLED=true`.

- **Architecture:** [../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md](../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md) (repo root)
- **Progress:** [../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md](../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md)
- **Internal API contract:** [../../docs/INTERNAL_API_CONTRACT.md](../../docs/INTERNAL_API_CONTRACT.md) (KYC endpoints documented in Phase 4)

## Setup

- Run `composer install` in this directory.
- Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*` (same MySQL as monolith, e.g. `DB_DATABASE=my_gamez`), and `INTERNAL_API_SECRET` (same as monolith).
- Internal routes are under `/internal/*` and require `X-Internal-Secret` or `Authorization: Bearer <INTERNAL_API_SECRET>`.

## Routes (Phase 4.2 – full logic)

- `POST /internal/kyc_check_eligibility` — eligibility (mining sessions, referrals, can_submit)
- `POST /internal/kyc_submit`, `POST /internal/submit_kyc` — submit KYC (base64 images; pending admin review)
- `POST /internal/kyc_get_status` — latest submission status
- `POST /internal/get_kyc_progress` — same as check_eligibility
- `POST /internal/didit_create_request` — create verification session (returns verification_url; set `GATEWAY_PUBLIC_URL` when behind gateway so URL points to monolith)
