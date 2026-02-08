# Microservices Implementation Tracker

**Last updated:** 2025-01-16 (Phase 5.1 completed)  
**Current phase:** — All phases complete  
**Next suggested prompt:** (none; optional: add more rate limiters or Redis cache for non-balance data)

---

## Phase status overview

| Phase | Name | Status | Notes |
|-------|------|--------|--------|
| 0.1 | Tracker + skeleton | Done | Tracker created; services/ and packages/ dirs + READMEs created |
| 0.2 | Gateway prep | Done | config/services.php and .env.example updated with gateway + Redis vars |
| 0.3 | Internal API contract | Done | docs/INTERNAL_API_CONTRACT.md |
| 1.1 | Mining service scaffold | Done | services/mining/ Laravel app, internal routes + stub controller |
| 1.2 | Mining logic in service | Done | Models + InternalMiningController + UpdateMiningBalances in services/mining |
| 1.3 | Gateway proxy for mining | Done | MiningProxyController + routes; timeout + one retry |
| 1.4 | Mining E2E + flag off by default | Done | docs/E2E_MINING.md; MINING_SERVICE_ENABLED=false in .env.example |
| 2.1 | Task service scaffold | Done | services/task/ Laravel app, internal routes stubs |
| 2.2 | Task logic in service | Done | InternalTaskController + models + tasks:daily-reset, tasks:distribute-rewards |
| 2.3 | Gateway proxy for task | Done | TaskProxyController + routes |
| 2.4 | Task E2E + flag off | Done | docs/E2E_TASK.md; TASK_SERVICE_ENABLED=false in .env.example |
| 3.1 | Gamification service scaffold | Done | services/gamification/ Laravel app, internal routes stubs |
| 3.2 | Gamification logic in service | Done | MysteryBox + Booster + AdBooster in InternalGamificationController + models |
| 3.3 | Gateway proxy for gamification | Done | GamificationProxyController + routes; timeout + one retry |
| 3.4 | Gamification E2E + flag off | Done | docs/E2E_GAMIFICATION.md; GAMIFICATION_SERVICE_ENABLED=false in .env.example |
| 4.1 | KYC service scaffold | Done | services/kyc/ Laravel app, internal routes stubs |
| 4.2 | KYC logic in service | Done | InternalKycController + User, UserLevel, Setting, KycSubmission; INTERNAL_API_CONTRACT KYC |
| 4.3 | Gateway proxy for KYC | Done | KycProxyController + routes; timeout + one retry |
| 4.4 | KYC E2E + flag off | Done | docs/E2E_KYC.md; KYC_SERVICE_ENABLED=false in .env.example |
| 5.1 | Redis hardening | Done | .env.example Redis vars + comments; MiningController + mining service |

---

## Per-phase task list

### Phase 0.1 – Tracker + skeleton
- [x] Create this file `docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md`
- [x] Create directory `services/`
- [x] Create directory `packages/`
- [x] Create `services/README.md` (points to ARCHITECTURE_MICROSERVICES_PLAN.md and this tracker)
- [x] Create `packages/README.md` (placeholder for crutox-contracts)
- [x] Create placeholder READMEs in services/mining, services/task, services/gamification, services/kyc

### Phase 0.2 – Gateway prep
- [x] Add to `config/services.php`: `mining`, `task`, `gamification`, `kyc` (url, enabled, timeout), internal_api_secret
- [x] Add to `.env.example`: REDIS_DB, REDIS_CACHE_DB, REDIS_QUEUE, MINING_SERVICE_*, TASK_SERVICE_*, GAMIFICATION_SERVICE_*, KYC_SERVICE_*, INTERNAL_API_SECRET

### Phase 0.3 – Internal API contract
- [x] Document in tracker or `docs/INTERNAL_API_CONTRACT.md`: POST /api/start_mining, GET /api/mining_status, POST /api/start_coin, POST /api/claim_bonus, etc. (request/response shape from current MiningController)

### Phase 1.1 – Mining service scaffold
- [x] Create `services/mining/` as new Laravel app (same PHP/Laravel version as monolith)
- [x] Configure DB to same MySQL (.env.example: DB_DATABASE=my_gamez, INTERNAL_API_SECRET)
- [x] Add internal routes (stub controller returns 501; middleware VerifyInternalSecret)

### Phase 1.2 – Mining logic in service
- [x] Copy MiningController logic and required models into services/mining
- [x] Implement internal endpoints so request/response match monolith
- [x] Copy UpdateMiningBalances command to mining service

### Phase 1.3 – Gateway proxy for mining
- [x] In monolith routes: if mining.enabled, HTTP forward to mining.url with same body/query; else call MiningController
- [x] Timeout and one retry on forward (MiningProxyController)

### Phase 1.4 – Mining E2E
- [x] Test with mining.enabled=false (monolith) — see docs/E2E_MINING.md
- [x] Test with mining.enabled=true (service) — see docs/E2E_MINING.md
- [x] Set mining.enabled=false in .env.example and docs

### Phase 2.1 – Task service scaffold
- [x] Create `services/task/` as new Laravel app
- [x] Configure DB to same MySQL (.env.example: DB_DATABASE=my_gamez, INTERNAL_API_SECRET)
- [x] Add internal routes (stub controller returns 501; middleware VerifyInternalSecret)

### Phase 2.2 – Task logic in service
- [x] Copy TaskController logic and required models into services/task
- [x] Implement internal endpoints so request/response match monolith
- [x] Copy tasks:daily-reset and tasks:distribute-rewards commands to task service

### Phase 2.3 – Gateway proxy for task
- [x] In monolith routes: if task.enabled, HTTP forward to task.url; else call TaskController (TaskProxyController)

### Phase 2.4 – Task E2E
- [x] Test with task.enabled=false (monolith) — see docs/E2E_TASK.md
- [x] Test with task.enabled=true (service) — see docs/E2E_TASK.md
- [x] Set task.enabled=false in .env.example and docs

### Phase 3.1 – Gamification service scaffold
- [x] Create `services/gamification/` as new Laravel app
- [x] Configure DB to same MySQL (.env.example: DB_DATABASE=my_gamez, INTERNAL_API_SECRET)
- [x] Add internal routes (stub controller returns 501; middleware VerifyInternalSecret)

### Phase 3.2 – Gamification logic in service
- [x] Copy MysteryBoxController, BoosterController, AdBoosterController logic and models into services/gamification
- [x] Implement InternalGamificationController (mystery_box_watch_ad, click, open, details; booster_status, booster_claim; ad_booster_status, ad_booster_claim)

### Phase 3.3 – Gateway proxy for gamification
- [x] In monolith: GamificationProxyController; if gamification.enabled, forward to gamification.url; else call local controllers
- [x] Update routes: booster_status, booster_claim, mystery_box_*, ad_booster_* → GamificationProxyController; timeout + one retry

### Phase 3.4 – Gamification E2E + flag off
- [x] Test with gamification.enabled=false (monolith) — see docs/E2E_GAMIFICATION.md
- [x] Test with gamification.enabled=true (service) — see docs/E2E_GAMIFICATION.md
- [x] Set GAMIFICATION_SERVICE_ENABLED=false in .env.example and docs

### Phase 4.1 – KYC service scaffold
- [x] Create `services/kyc/` as new Laravel app (Laravel 12, from gamification template)
- [x] Configure DB to same MySQL (.env.example: DB_DATABASE=my_gamez, INTERNAL_API_SECRET)
- [x] Add internal routes (stub InternalKycController returns 501; middleware VerifyInternalSecret)
- [x] Routes: kyc_check_eligibility, kyc_submit, submit_kyc, kyc_get_status, get_kyc_progress, didit_create_request

### Phase 4.2 – KYC logic in service
- [x] Copy KycController logic and required models (User, UserLevel, Setting, KycSubmission, Didit) into services/kyc
- [x] Implement internal endpoints so request/response match monolith

### Phase 4.3 – Gateway proxy for KYC
- [x] In monolith: KycProxyController; if kyc.enabled, forward to kyc.url; else call KycController; timeout + one retry
- [x] Update routes: kyc_* and didit_create_request → KycProxyController

### Phase 4.4 – KYC E2E + flag off
- [x] Test with kyc.enabled=false (monolith); test with kyc.enabled=true (service)
- [x] Set KYC_SERVICE_ENABLED=false in .env.example and docs/E2E_KYC.md

### Phase 5.1 – Redis hardening
- [x] Ensure .env.example lists all Redis vars
- [x] Add comment in MiningController (and mining service): balance/token must never be read from cache
- [x] Optional: rate-limit middleware using Redis (API already uses throttle; when CACHE_DRIVER=redis it uses Redis)

---

## Redis rules (no wrong balance)

**Do NOT cache in Redis (for any API response used by Flutter):**
- `users.token`, `users.coin`, `users.is_mining`, `users.mining_end_time`, or any derived balance/mining state
- Mining Service and monolith must **always** read/write these from MySQL

**Allowed Redis usage:**
- **Rate limiting:** API throttle uses cache driver (when `CACHE_DRIVER=redis`, throttle uses Redis); key per user/IP (no balance data)
- **Session:** admin panel (optional; can stay file)
- **Queue:** Laravel queue driver redis for async jobs
- **Cache (non-balance only):** e.g. other_settings, get_currencies, ads config with short TTL (60–300s) and invalidation when admin updates; never cache per-user balance or token

**Forbidden cache keys (example):** Do not create keys such as `user:{id}:balance`, `user:{id}:token`, or any key storing mining state for API responses.

---

## Handoff: how to continue

When starting a new session:
1. Open `docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md`
2. Find the first phase with status "Not Started" or "In Progress"
3. Complete that phase's tasks, then set its status to "Done" and update "Last updated" and "Next suggested prompt"
4. Optionally commit the tracker and code

**Example next prompt:** "Continue from docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md and implement the next phase."
