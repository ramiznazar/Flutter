# Mining Service

Laravel microservice for mining, coin, bonus, social claims, and daily rewards. Called by the monolith API gateway when `MINING_SERVICE_ENABLED=true`.

- **Architecture:** [../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md](../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md) (repo root)
- **Progress:** [../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md](../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md)
- **Internal API contract:** [../../docs/INTERNAL_API_CONTRACT.md](../../docs/INTERNAL_API_CONTRACT.md)

## Setup

- Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*` (same MySQL as monolith, e.g. `DB_DATABASE=my_gamez`), and `INTERNAL_API_SECRET` (same as monolith).
- Internal routes are under `/internal/*` and require `X-Internal-Secret` or `Authorization: Bearer <INTERNAL_API_SECRET>`.

## Routes (stubs until Phase 1.2)

- `POST /internal/start_mining`  
- `GET /internal/mining_status`  
- `POST /internal/start_coin`  
- `POST /internal/claim_bonus`  
- `POST /internal/bonus_history`  
- `POST /internal/social_claim`  
- `POST /internal/social_list`  
- `POST /internal/get_daily_reward_status`  
- `POST /internal/add_daily_reward`  

All currently return `501 Not Implemented` until mining logic is moved in Phase 1.2.
