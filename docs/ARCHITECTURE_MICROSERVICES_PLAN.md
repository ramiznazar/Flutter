# Crutox Backend: Microservices & Modular Architecture Plan

**Document Version:** 1.0  
**Target:** Laravel monolith → hybrid modular / microservices  
**Constraint:** Keep existing API contract (same request/response) for Flutter app; split internally only.

---

## Table of Contents

1. [Architecture Decision](#1-architecture-decision)
2. [Service Identification](#2-service-identification)
3. [Proposed System Architecture](#3-proposed-system-architecture)
4. [Database Strategy](#4-database-strategy)
5. [Inter-Service Communication](#5-inter-service-communication)
6. [Authentication & Authorization](#6-authentication--authorization)
7. [Performance & Scalability](#7-performance--scalability)
8. [Laravel-Specific Implementation](#8-laravel-specific-implementation)
9. [Deployment Strategy](#9-deployment-strategy)
10. [Migration Plan](#10-migration-plan)
11. [Common Mistakes to Avoid](#11-common-mistakes-to-avoid)
12. [Crutox-Specific: Module-to-Folder Mapping](#12-crutox-specific-module-to-folder-mapping)

---

## 1. Architecture Decision

### Recommendation: **Hybrid (Modular Monolith + Selective Microservices)**

**Do not** go full microservices for Crutox at this stage. Reasons:

- **API contract must stay identical** — Flutter app must keep calling the same `/api/*` endpoints. A full microservices rewrite would require an API Gateway and duplicate route definitions; the hybrid approach achieves the same scalability with less risk.
- **Team size and ops** — Full microservices demand more DevOps, monitoring, and debugging across services. A modular monolith with 2–4 extracted high-load services is easier to operate.
- **Database coupling** — The current schema has strong coupling (e.g. `users` referenced by mining, tasks, KYC, mystery box). Splitting everything into services with separate DBs would require a large data migration and eventual-consistency handling; the recommended path keeps one primary DB and isolates only the heaviest domains.

### When Microservices Are **Not** Recommended Here

- **Auth**: Tight coupling with every request; keep in monolith or a single “gateway” app that validates and forwards. Extracting auth as a separate service adds latency and complexity without clear win for this scale.
- **Settings/Config**: Read-heavy, cached; no need for a separate service.
- **Admin panel**: Low traffic, Blade-based; keep in monolith.
- **News, Shop, Giveaway, Spin**: Read-heavy content; better served by caching and read replicas than by new services.

### When Extraction **Is** Recommended

- **Mining**: `start_mining`, `mining_status` (polling), `start_coin`, `claim_bonus`, `add_daily_reward` — very high request rate and CPU/time logic; good candidate for a dedicated service.
- **Tasks**: `task_start`, `task_claim_reward`, `get_daily_tasks`, `task_track` + cron `tasks:daily-reset` and `tasks:distribute-rewards` — bursty and scheduled; can run on a separate process or service.
- **Mystery Box + Ad Booster**: `mystery_box_*`, `ad_booster_*` — ad-heavy, cooldown and reward logic; can be one “gamification” service.
- **KYC**: External provider (e.g. Didit), document uploads, status checks — clear boundary; good candidate for a small service or at least a dedicated module.

### Trade-offs Summary

| Approach | Pros | Cons |
|----------|------|------|
| **Full microservices** | Independent scaling, tech diversity | High ops cost, distributed debugging, API Gateway required, DB split painful |
| **Hybrid (recommended)** | Scale only hot paths, keep API unchanged, lower risk | Some shared DB, a few services to maintain |
| **Modular monolith only** | Simplest, single deploy | Mining/Tasks/Mystery Box still compete for CPU on one app |

### Risk Overview

- **Hybrid risk**: Cross-service calls and possible latency; mitigated by async where possible and clear ownership (e.g. Mining Service owns mining-related tables or accesses them via defined interfaces).
- **Shared DB risk**: One schema change can affect multiple services; mitigated by strict module ownership and migration ownership (see Database Strategy).

---

## 2. Service Identification

Based on the current Crutox codebase (`routes/api.php`, controllers, models, and scheduled tasks):

### 2.1 **Auth Service** (or keep in monolith)

**Endpoints:** `login`, `signup`, `create_account`, `otp_request`, `otp_request_new`, `verify_otp`, `verify_otp_and_set_password`, `change_password`, `reset_password`, `get_email`.

**Models:** `User` (auth-related fields: `email`, `password`, `otp`, `auth_token`, `account_status`).

**Extract?** **No.** Auth is on the critical path of almost every request. Keeping it in the main app (or in the same process as the API Gateway/BFF) avoids extra network hops and simplifies token validation. If you later introduce an API Gateway, the gateway can validate JWT/session and forward; the “auth service” can remain the same Laravel app that owns `users` for login/signup/OTP.

**Boundary:** Single module in monolith; clear interface (e.g. “validate token and return user id”) for other modules.

---

### 2.2 **User Service** (module in monolith)

**Endpoints:** `get_user_stats`, `update_profile`, `edit_profile`, `change_pic`, `get_team`, `getLevel`, `getBadges`, `check_levels`, `update_user_guide`, `update_user_ping`, `setup_username`, `setup_invite`, `delete_account_request`, `reactivate_account`.

**Models:** `User`, `UserLevel`, `UserGuide`, `Level`, `Badge`, `SocialMediaToken` (for team/invite).

**Extract?** **No.** Strong coupling to `users` and user-related tables; many endpoints are read-heavy and can be cached. Keep as a well-defined module inside the monolith.

**Boundary:** `UserController` + dedicated subdomain “user” in code (e.g. `App\Modules\User\*`). No separate service.

---

### 2.3 **Mining Service** — **EXTRACT**

**Endpoints:** `start_mining`, `mining_status`, `start_coin`, `claim_bonus`, `bonus_history`, `social_claim`, `social_list`, `add_daily_reward`, `get_daily_reward_status`.

**Models / Tables:** `User` (token, coin, is_mining, mining_end_time, mining_time, etc.), `UserLevel`, `Level`, `CoinSetting`, `Setting` (mining-related), `token_bonus_history`, `daily_reward_claims`, `SocialMediaSetting`, `SocialMediaToken`.

**Why extract:**  
- Highest request volume (polling `mining_status`, frequent `start_mining` / `start_coin` / `claim_bonus`).  
- Contains time-based and calculation logic; benefits from dedicated CPU and scaling.  
- Scheduled command `mining:update-balances` (every 30s) fits naturally in this service.

**Boundary:** Owns “mining” and “token/balance” semantics. Exposes internal API used by the gateway; gateway keeps public `/api/start_mining` etc. and forwards.

---

### 2.4 **Task Service** — **EXTRACT**

**Endpoints:** `task_start`, `task_claim_reward`, `task_track`, `get_daily_tasks`.

**Models / Tables:** `TaskCompletion`, `User`, `UserLevel`, task-related config in `settings` or similar.

**Cron:** `tasks:daily-reset`, `tasks:distribute-rewards`.

**Why extract:**  
- Bursty traffic (many users claiming at once).  
- Cron jobs are heavy; isolating them avoids blocking the main app.  
- Clear domain: “tasks” and “rewards”.

**Boundary:** Owns task definitions and completions. Reads user identity (and possibly level) from shared DB or via internal API; writes only to task-related tables.

---

### 2.5 **Booster Service** (Mining-related boosters + Ad Booster)

**Endpoints:** `booster_status`, `booster_claim`, `ad_booster_status`, `ad_booster_claim`.

**Models / Tables:** `UserBooster`, `AdBoosterClaim`, `User`, `Setting` (booster config).

**Extract?** **Optional.** Can stay in monolith and scale with the app, or be grouped with Mining (same process) since boosters affect mining rate. If you want a separate “gamification” service, combine with Mystery Box (below).

**Boundary:** If not extracted, keep as a module. If extracted, combine with Mystery Box into one **Gamification Service**.

---

### 2.6 **Mystery Box Service** — **EXTRACT** (or merge into Gamification)

**Endpoints:** `mystery_box_watch_ad`, `mystery_box_click`, `mystery_box_open`, `mystery_box_details`.

**Models / Tables:** `MysteryBoxClaim`, `User`, `Setting` (mystery box config).

**Why extract:**  
- Ad and click-heavy; cooldown and reward logic.  
- Isolating this avoids impacting auth/mining when mystery box logic is complex.

**Boundary:** Owns `mystery_box_claims` and mystery-box settings. Can be one service with Boosters (Gamification Service).

---

### 2.7 **KYC Service** — **EXTRACT** (small service)

**Endpoints:** `kyc_check_eligibility`, `kyc_submit`, `submit_kyc`, `kyc_get_status`, `get_kyc_progress`, `didit_create_request`.

**Models / Tables:** `KycSubmission`, `User` (for eligibility/status).

**Why extract:**  
- External provider (Didit), file uploads, and status checks.  
- Clear bounded context; can be a small Laravel app or a single module with strict interface.

**Boundary:** Owns KYC submissions and provider integration. Other services only need “is user KYC’d?” (read from shared DB or internal API).

---

### 2.8 **Content Service** (module in monolith)

**Endpoints:** `get_all_news`, `get_news`, `add_news`, `delete_news`, `like_news`, `set_news_view`, `get_all_shops`, `set_shop_view`, `giftcard_track`, `get_giveaway`, `spin`, `spin_claim`, `get_myspin_info`.

**Models:** `News`, `NewsLike`, `Shop`, `ShopView`, `Giveaway`, `Spin`, `SpinCailmed`, `SpinSetting`.

**Extract?** **No.** Read-heavy; better optimized with caching and read replicas. Keeping in monolith is simpler.

**Boundary:** Single “Content” or “Catalog” module in monolith.

---

### 2.9 **Settings / Config Service** (module in monolith)

**Endpoints:** `other_settings`, `get_currencies`, `getTotalUsers`, `time`, `ads`.

**Models:** `Setting`, `Currency`, `AdsSetting`.

**Extract?** **No.** Used at app startup and for global config; high cache hit rate. No benefit from a separate service.

**Boundary:** Module in monolith; heavy use of Redis/cache.

---

### 2.10 **Admin** (stay in monolith)

**Scope:** All `/api/admin/*` routes and Blade admin panel (`routes/web.php`).

**Extract?** **No.** Low traffic; keeps deployment and auth model simple.

**Boundary:** Admin module + admin middleware in the same app.

---

### Summary: What to Extract

| Service | Extract? | Reason |
|---------|----------|--------|
| Auth | No | Critical path; keep in main app |
| User | No | Module only; cache-friendly |
| **Mining** | **Yes** | High load, cron, calculations |
| **Task** | **Yes** | Bursty + cron |
| **Mystery Box (+ Booster)** | **Yes** | Gamification; ad/click heavy |
| **KYC** | **Yes** | External API, clear boundary |
| Content | No | Read-heavy; cache |
| Settings | No | Config; cache |
| Admin | No | Low traffic |

**Optimal count of separate runnable “services” (excluding monolith):**  
**3–4:** Mining, Task, Gamification (Mystery Box + Booster), KYC. Optionally merge Mining + Task into one “Core Game” service and keep Gamification + KYC separate (3 services total).

---

## 3. Proposed System Architecture

### 3.1 High-Level Diagram (Text)

```
                    [ Flutter App ]
                           |
                           | HTTPS (unchanged API paths)
                           v
              +----------------------------+
              |   API Gateway / BFF        |
              |   (Laravel monolith)      |
              |   - Routes /api/*          |
              |   - Auth validation        |
              |   - Rate limit             |
              |   - Forward to services    |
              +----------------------------+
                    |    |    |    |
         +----------+    |    |    +----------+
         |               |    |               |
         v               v    v               v
  +------------+  +-----------+  +-------------+  +----------+
  | Mining     |  | Task       |  | Gamification|  | KYC      |
  | Service    |  | Service   |  | (Mystery+   |  | Service  |
  | (Laravel)  |  | (Laravel) |  |  Booster)   |  | (Laravel)|
  +------------+  +-----------+  +-------------+  +----------+
         |               |    |               |
         +----------+    |    |    +----------+
                    |    |    |    |
                    v    v    v    v
              +----------------------------+
              |   MySQL (shared)           |
              |   + optional read replica  |
              +----------------------------+
                    |
              +----------------------------+
              |   Redis (cache, sessions,  |
              |   queues, rate limit)      |
              +----------------------------+
```

- **BFF / Gateway:** Same Laravel app that today serves all routes. It keeps the same `routes/api.php` contract; for extracted domains it forwards (sync HTTP or internal queue) to the corresponding service. No URL change for the Flutter app.
- **Internal APIs:** Services expose small HTTP (or gRPC) endpoints such as `POST /internal/mining/start`, `GET /internal/tasks/daily`, etc., not public.

### 3.2 API Gateway vs Direct Service Access

- **Public:** Flutter (and any other client) talks **only** to the gateway (monolith). All current paths remain: `/api/login`, `/api/start_mining`, `/api/task_claim_reward`, etc.
- **Internal:** Gateway → services over private network (or localhost if on same host). No client ever calls Mining/Task/KYC services directly.
- **Benefit:** Single place for auth, rate limiting, logging, and backward compatibility.

### 3.3 Sync vs Async

- **Sync (HTTP/gRPC):** Used for request/response that the Flutter app is waiting on (e.g. start_mining, task_claim_reward, mystery_box_open). Gateway calls the service and returns the response.
- **Async (queue):** Use for side effects that do not need to be in the same response: e.g. “record bonus history”, “update analytics”, “send notification”. Prefer Laravel queues (Redis or SQS) from the gateway or from the service.
- **Cron:** `mining:update-balances`, `tasks:daily-reset`, `tasks:distribute-rewards` run inside the service that owns that domain (Mining Service, Task Service).

### 3.4 Internal vs Public APIs

- **Public:** Everything under `/api/*` — unchanged for the client.
- **Internal:** e.g. `/internal/mining/*`, `/internal/tasks/*`, `/internal/gamification/*`, `/internal/kyc/*` — only gateway (or trusted backends) call these; protect by network and/or API key / service token.

---

## 4. Database Strategy

### 4.1 Recommendation: **Shared Database with Clear Ownership**

For Crutox, **do not** adopt database-per-service in the first phase. Reasons:

- Existing schema has many foreign keys and shared tables (`users`, `settings`). Splitting DBs would require event-driven duplication and eventual consistency.
- Migration is safer when all services still read/write the same MySQL; you only change “who” (which codebase) writes to which tables.

**Approach:** One MySQL database (or one per environment). Each extracted service has **owned** tables it is allowed to write to; others are read-only or accessed via the service that owns them.

### 4.2 Data Ownership (Example)

| Domain | Owned Tables (writer) | Can Read |
|--------|------------------------|----------|
| Auth / User (monolith) | `users`, `user_guide`, `user_levels` (part), `admins` | All |
| Mining Service | `users` (token, coin, is_mining, mining_end_time, etc. — or via RPC), `token_bonus_history`, `daily_reward_claims` | `users`, `coin_settings`, `settings`, `levels` |
| Task Service | `task_completions` | `users`, `user_levels`, `settings` |
| Gamification Service | `user_boosters`, `ad_booster_claims`, `mystery_box_claims` | `users`, `settings` |
| KYC Service | `kyc_submissions` | `users` |

**Transactional boundaries:** Prefer single-service transactions. Cross-service “transactions” (e.g. “task claim” updates user coins) are done either by the gateway calling Mining Service after Task Service, or by one service calling another’s internal API; avoid distributed transactions.

### 4.3 Read Replicas

- Add one MySQL read replica. Point read-heavy queries (news, shop, settings, user profile reads) to the replica in the monolith.
- Mining/Task services can use the same replica for reads if they need user/settings data; writes go to primary.

### 4.4 Transactions Across Services

- **Do not** use 2PC or distributed transactions. Use:
  - **Orchestration:** Gateway or one service calls another (e.g. Task Service “claims reward” then calls Mining Service “add coins”). If the second call fails, use idempotency and/or compensating action (e.g. job to reconcile).
  - **Events (optional):** Publish “TaskRewardClaimed” from Task Service; Mining Service consumes and credits coins. Prefer this once you have a queue (Redis/SQS) and want to decouple.

### 4.5 Eventual Consistency

- When Mining Service updates `users.token`, the monolith and other services see it after DB commit (or after cache invalidation). Accept small delay for non-critical reads.
- For “user balance” shown in Flutter after mining, the response comes from Mining Service, so it is consistent. For “balance” shown on profile, reading from replica or cache is eventually consistent.

---

## 5. Inter-Service Communication

### 5.1 REST vs gRPC vs Message Queues

- **Gateway → Services:** Prefer **HTTP REST** for simplicity and Laravel’s strength. Use **gRPC** only if you need very high throughput and are ready to maintain proto files and PHP gRPC clients.
- **Async:** **Laravel Queue** with **Redis** driver (or **SQS** in AWS). Use for background jobs (bonus history, notifications, reward distribution).

### 5.2 When to Use Redis, RabbitMQ, Kafka, SQS

- **Redis:** Cache, sessions, rate limiting, Laravel queues (default), pub/sub if you need simple events. **Use Redis** for queues in Crutox unless you need stronger guarantees.
- **RabbitMQ:** Use if you need complex routing, dead-letter, or multiple consumers with different semantics. Not required for initial split.
- **Kafka:** Use for high-throughput event streaming and long retention. Overkill for current scale.
- **SQS:** Use when deploying on AWS and you want managed queues and no Redis; Laravel supports SQS driver.

**Recommendation:** Redis for cache + queues; add SQS only if you move to AWS and want managed queues.

### 5.3 Retry, Timeout, Circuit Breaker

- **Timeout:** Every HTTP call from gateway to service must have a timeout (e.g. 5–10 s). Laravel HTTP client: `->timeout(10)`.
- **Retry:** Retry 1–2 times with backoff for 5xx and timeouts; do not retry 4xx (except 429 with Retry-After).
- **Circuit breaker:** If a service fails repeatedly (e.g. 5 failures in 1 min), open circuit and return a friendly error or cached fallback; periodically try again. Can be implemented in gateway middleware or a small library.

---

## 6. Authentication & Authorization

### 6.1 Central Auth in Monolith

- **Login/signup/OTP** stay in the monolith. Issue **JWT** (or keep current token in `users.auth_token`) after login.
- Gateway validates token on every request; extracts `user_id` (and optionally email) and passes to downstream (e.g. in header `X-User-Id` or in request body for internal calls).

### 6.2 JWT / OAuth2

- Prefer **opaque token** stored in DB (`users.auth_token`) for revocability, or **short-lived JWT** (e.g. 15 min) with refresh token in DB. Avoid long-lived JWT without revocation if you need “logout everywhere”.
- Admin: Keep session-based auth in the same app.

### 6.3 Service-to-Service

- Internal calls (gateway → Mining/Task/KYC) are on a private network. Use **API key** or **shared secret** in header (e.g. `X-Internal-Secret`) validated by each service. No user JWT needed for internal calls; gateway injects `X-User-Id` from the validated user token.

---

## 7. Performance & Scalability

### 7.1 High-Concurrency Endpoints

- **Mining:** Scale Mining Service horizontally (multiple instances behind a local load balancer). Use Redis for “mining session” or rate checks if needed.
- **Polling:** `mining_status` — keep responses small; consider ETag or conditional request to reduce payload when unchanged.
- **Task claim:** Idempotency key (e.g. task_id + user_id + date) to avoid double-claim under concurrency.

### 7.2 Caching (Redis)

- **Settings / config:** Cache `other_settings`, mining params, mystery box config (TTL 1–5 min).
- **User profile / level:** Cache per user_id (TTL 1–2 min); invalidate on update.
- **News, shop, giveaway list:** Cache with short TTL or cache-aside on read.

### 7.3 Rate Limiting

- Per-user rate limit on expensive endpoints (start_mining, task_claim_reward, mystery_box_open) — e.g. 60/min per user.
- Global rate limit on login/signup to prevent abuse. Implement in gateway (Laravel throttle middleware + Redis).

### 7.4 Read-Heavy Optimization

- MySQL read replica for selects in monolith and in services.
- Redis cache for settings, levels, and content.
- Indexes on `user_id`, `email`, `(user_id, task_id, date)` for task completions, etc.

---

## 8. Laravel-Specific Implementation

### 8.1 Structure of Each Microservice

- Each service is a **separate Laravel application** in its own folder (e.g. `services/mining`, `services/task`, `services/gamification`, `services/kyc`).
- Same Laravel version and PHP version as monolith for consistency.
- Only the routes, controllers, and models needed for that domain; no Blade, no admin.

### 8.2 Shared Packages vs Duplicated Code

- **Shared:** Put in a **private Composer package** (e.g. `crutox/shared-contracts`): DTOs, API request/response shapes, constants, and optionally validation rules. Each service and the monolith require this package.
- **Duplicated:** Avoid copying large chunks of business logic. Prefer “shared package” for interfaces and DTOs; each service implements its own logic.

### 8.3 Env and Secrets

- Each service has its own `.env` (and in production, use env vars or a secret manager). DB credentials can be the same (shared DB) or scoped per service if you later split DBs.
- Internal API keys and secrets: in env, never in code.

### 8.4 Queue Workers and Horizon

- Monolith: Run queue workers (or Horizon) for jobs that remain in the monolith (e.g. send email, log analytics).
- Mining/Task services: Run their own queue workers for domain-specific jobs (e.g. mining balance batch, task reward distribution). Use Horizon in each app if you need visibility and tuning.

---

## 9. Deployment Strategy

### 9.1 Docker

- **Dockerfile per app:** One for monolith, one per service. Multi-stage build; run as non-root.
- **Compose for local:** `docker-compose.yml` with monolith, mining, task, gamification, kyc, MySQL, Redis. Gateway points to service hostnames (e.g. `http://mining:8000`).

### 9.2 Single VPS vs Kubernetes vs Managed

- **Single VPS:** Run monolith + 3–4 PHP-FPM/Octane containers for services behind Nginx; one MySQL, one Redis. Easiest for small/medium scale.
- **Kubernetes:** Use when you need auto-scaling and multiple replicas per service; more operational overhead.
- **Managed:** Use managed MySQL (e.g. RDS), Redis (ElastiCache), and optionally ECS/Lambda for services if on AWS.

**Recommendation:** Start with **single VPS or 2–3 VPS** (one for app pool, one for DB if needed). Move to K8s or managed when traffic justifies it.

### 9.3 CI/CD Pipeline

- **Stages:** Lint → Unit/Feature tests → Build Docker image → Push to registry → Deploy (e.g. pull and restart containers, or K8s rollout).
- **Branch:** `main` deploys to production; or `develop` → staging, `main` → production.
- **Secrets:** From CI secret store; never in repo.

### 9.4 Rolling Deployments and Zero Downtime

- Multiple app instances behind a load balancer; deploy one instance at a time (rolling).
- Queue workers: use “graceful shutdown” (stop accepting new jobs, finish current job then exit); orchestrator restarts workers with new code.
- DB migrations: Run backward-compatible migrations first; deploy new code; then run cleanup migrations. Avoid long-running locks during peak.

---

## 10. Migration Plan

### Principle

- **One module at a time.** Keep the monolith working; add a new service and switch traffic gradually (feature flag or route switch).
- **API contract never changes** for the Flutter app; only the backend implementation (monolith vs service) changes.

### Step-by-Step (Order of Extraction)

**Phase 0: Prepare**

1. Introduce a **route layer** in the monolith: all `/api/*` handlers go through a single “dispatcher” or middleware that can later route to internal HTTP clients (e.g. “if config mining.service_enabled, proxy to Mining Service; else run local MiningController”). Initially, config is “disabled”; behavior remains current.
2. Add **internal API** to the monolith for Mining (e.g. duplicate routes under `Route::prefix('internal')->group(...)` that call the same `MiningController`). This will be the contract the gateway uses later.
3. Document **request/response** for each endpoint you plan to move (e.g. `POST /api/start_mining` body and response). This is the contract the new service must implement.

**Phase 1: Mining Service**

1. Create repo or folder `services/mining` (new Laravel app). Copy only Mining-related routes, `MiningController`, and dependent models (or replicate minimal models + DB connection to same DB).
2. Implement in Mining Service the same request/response as current `start_mining`, `mining_status`, `start_coin`, `claim_bonus`, etc. (internal API).
3. In monolith, add HTTP client call to Mining Service for each mining route (when feature flag or config is on). If call fails or flag is off, fall back to local controller (backward compatible).
4. Run `mining:update-balances` cron inside Mining Service only when traffic is fully switched.
5. Test under load; then switch traffic fully and remove old Mining code from monolith (or keep as fallback for one release).

**Phase 2: Task Service**

1. Same pattern: create `services/task`, implement `task_start`, `task_claim_reward`, `get_daily_tasks`, `task_track` with same contract.
2. Move `tasks:daily-reset` and `tasks:distribute-rewards` to Task Service.
3. Gateway forwards to Task Service; fallback to monolith during rollout.
4. After validation, remove task logic from monolith.

**Phase 3: Gamification Service (Mystery Box + Booster)**

1. Create `services/gamification` with mystery_box_* and ad_booster_* (and optionally booster_status/claim).
2. Implement same request/response; gateway forwards; fallback in monolith.
3. Switch traffic and remove from monolith.

**Phase 4: KYC Service**

1. Create `services/kyc` with KYC endpoints and Didit integration.
2. Gateway forwards; fallback in monolith.
3. Switch and clean up.

### How to Avoid Breaking Production

- **Feature flags:** e.g. `MINING_USE_SERVICE=true` in monolith. When false, use local controller; when true, call Mining Service. Roll out per env (staging first, then prod).
- **Shadow mode (optional):** Call both monolith and service; compare responses and log diffs; do not change client response. Fix discrepancies before switching.
- **Rollback:** Keep old code path for at least one release. If issues appear, set flag to false and redeploy.

---

## 11. Common Mistakes to Avoid

- **Over-fragmentation:** Do not extract Auth, User, Content, Settings, Admin into services. Only Mining, Task, Gamification, KYC.
- **Chatty services:** Avoid gateway calling multiple services in one user request (e.g. mining + task + user in sequence). Prefer one logical call per user action; if you need data from another domain, cache or have one service call another internally, not the gateway chaining 3 services.
- **Distributed monolith:** Do not share the same codebase and deploy it as “multiple services” with only different env. Each service must be a separate codebase (or at least a clearly separated module with its own deployable artifact).
- **Shared DB coupling:** Do not let every service write to `users`. Define ownership: monolith (or User module) owns `users`; Mining Service updates only mining-related columns or does it via an internal API that the monolith exposes.
- **No timeouts/retries:** Every internal HTTP call must have timeout and limited retries to avoid cascading failures.
- **Skipping idempotency:** For “claim” and “reward” endpoints, use idempotency keys so duplicate requests do not double-credit.

---

## 12. Crutox-Specific: Module-to-Folder Mapping

This section maps current Crutox modules to **folders and runnable units** so that implementation can be done “one by one” (e.g. by Cursor or a team) without changing the public API.

### 12.1 Repository / Folder Layout (Target)

```
crutox-backend/
├── app/                    # Monolith (gateway + auth + user + content + settings + admin)
│   ├── Http/Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── MiningController.php   # Eventually: proxy only
│   │   ├── TaskController.php     # Eventually: proxy only
│   │   ├── MysteryBoxController.php
│   │   ├── BoosterController.php
│   │   ├── AdBoosterController.php
│   │   ├── KycController.php
│   │   ├── NewsController.php
│   │   ├── ShopController.php
│   │   ├── ...
│   │   └── Admin/
│   └── ...
├── services/
│   ├── mining/             # Laravel app
│   │   ├── app/Http/Controllers/
│   │   ├── routes/api.php   # Internal routes only
│   │   └── ...
│   ├── task/
│   ├── gamification/       # Mystery Box + Booster
│   └── kyc/
├── packages/               # Optional shared package
│   └── crutox-contracts/
└── docs/
    └── ARCHITECTURE_MICROSERVICES_PLAN.md  # This file
```

### 12.2 Module 1: Mining Service

**Folder:** `services/mining/`

**Endpoints to implement (internal contract):**

- `POST /internal/start_mining` (body/response same as current `/api/start_mining`)
- `GET /internal/mining_status?email=...`
- `POST /internal/start_coin`
- `POST /internal/claim_bonus`
- `POST /internal/bonus_history`
- `POST /internal/social_claim`, `POST /internal/social_list`
- `POST /internal/add_daily_reward`, `POST /internal/get_daily_reward_status`

**Tables used (writer):** `users` (token, coin, is_mining, mining_end_time, mining_time, etc.), `token_bonus_history`, `daily_reward_claims`; (reader) `coin_settings`, `settings`, `user_levels`, `levels`, `social_media_*`.

**Cron:** `mining:update-balances` (schedule inside this app).

**Implementation order:** Create Laravel app → copy MiningController logic and models → expose internal routes → add gateway proxy in monolith → feature flag → migrate cron → full traffic → remove from monolith.

---

### 12.3 Module 2: Task Service

**Folder:** `services/task/`

**Endpoints:**

- `POST /internal/task_start`
- `POST /internal/task_claim_reward`
- `POST /internal/task_track`
- `POST /internal/get_daily_tasks`

**Tables:** `task_completions` (writer); (reader) `users`, `user_levels`, `settings` (task config).

**Cron:** `tasks:daily-reset`, `tasks:distribute-rewards`.

**Implementation order:** Same pattern as Mining — new app → copy logic → internal routes → gateway proxy → feature flag → cron in service → switch → cleanup monolith.

---

### 12.4 Module 3: Gamification Service (Mystery Box + Booster)

**Folder:** `services/gamification/`

**Endpoints:**

- `POST /internal/mystery_box_watch_ad`, `mystery_box_click`, `mystery_box_open`, `GET /internal/mystery_box_details`
- `POST /internal/booster_status`, `POST /internal/booster_claim`
- `POST /internal/ad_booster_status`, `POST /internal/ad_booster_claim`

**Tables:** `mystery_box_claims`, `user_boosters`, `ad_booster_claims` (writers); `users`, `settings` (readers).

**Implementation order:** New app → implement all above → gateway proxy → feature flag → switch → cleanup.

---

### 12.5 Module 4: KYC Service

**Folder:** `services/kyc/`

**Endpoints:**

- `POST /internal/kyc_check_eligibility`
- `POST /internal/kyc_submit`, `POST /internal/submit_kyc`
- `POST /internal/kyc_get_status`, `POST /internal/get_kyc_progress`
- `POST /internal/didit_create_request`

**Tables:** `kyc_submissions` (writer); `users`, `settings` (readers).

**Implementation order:** New app → KYC logic + Didit → internal routes → gateway proxy → feature flag → switch → cleanup.

---

### 12.6 What Stays in Monolith (No New Folder)

- **Auth:** All login, signup, OTP, password, reset — keep in `app/Http/Controllers/Api/AuthController.php`.
- **User:** All profile, stats, team, levels, badges, guide, invite — keep in `UserController` and related.
- **Content:** News, shop, giveaway, spin — keep in current controllers.
- **Settings:** other_settings, time, ads, currencies — keep in `SettingsController`.
- **Admin:** All `/api/admin/*` and Blade admin — keep in monolith.
- **Utility:** get_email, send_notification — keep in monolith.

---

### 12.7 Gateway Proxy Pattern (Monolith)

For each extracted domain, the monolith’s route file does:

- **If** `config('services.mining.enabled')` **and** request is for a mining path:  
  - Forward request to `config('services.mining.url')` (e.g. `http://mining:8000/internal/start_mining`) with same body and headers (plus `X-User-Id` if available).  
  - Return the response (status + body) to the client.
- **Else:**  
  - Call the existing controller (local implementation) and return response.

This keeps the public API path (e.g. `POST /api/start_mining`) unchanged; only the backend that handles it changes (local vs service).

---

## Document End

This plan is intended to be handed to a team or used with Cursor to implement the migration **module by module**, without changing the existing API contract and while minimizing risk to production. Start with Phase 0 (routing abstraction and internal API contract), then Phase 1 (Mining Service), then Task, Gamification, and KYC in order.
