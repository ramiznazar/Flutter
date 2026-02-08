# Gamification Service

Laravel microservice for Mystery Box, Booster, and Ad Booster. Called by the monolith API gateway when `GAMIFICATION_SERVICE_ENABLED=true`.

- **Architecture:** [../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md](../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md) (repo root)
- **Progress:** [../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md](../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md)
- **Internal API contract:** [../../docs/INTERNAL_API_CONTRACT.md](../../docs/INTERNAL_API_CONTRACT.md) (Gamification endpoints documented in Phase 3)

## Setup

- Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*` (same MySQL as monolith, e.g. `DB_DATABASE=my_gamez`), and `INTERNAL_API_SECRET` (same as monolith).
- Internal routes are under `/internal/*` and require `X-Internal-Secret` or `Authorization: Bearer <INTERNAL_API_SECRET>`.

## Routes (stubs until Phase 3.2)

- `POST /internal/mystery_box_watch_ad`
- `POST /internal/mystery_box_click`
- `POST /internal/mystery_box_open`
- `POST /internal/mystery_box_details`
- `POST /internal/booster_status`
- `POST /internal/booster_claim`
- `POST /internal/ad_booster_status`
- `POST /internal/ad_booster_claim`

All currently return `501 Not Implemented` until gamification logic is moved in Phase 3.2.
