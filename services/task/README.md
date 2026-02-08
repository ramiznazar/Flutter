# Task Service

Laravel microservice for daily tasks (task_start, task_claim_reward, task_track, get_daily_tasks). Called by the monolith API gateway when `TASK_SERVICE_ENABLED=true`.

- **Architecture:** [../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md](../../docs/ARCHITECTURE_MICROSERVICES_PLAN.md) (repo root)
- **Progress:** [../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md](../../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md)
- **Internal API contract:** [../../docs/INTERNAL_API_CONTRACT.md](../../docs/INTERNAL_API_CONTRACT.md) (Task endpoints documented in Phase 2)

## Setup

- Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*` (same MySQL as monolith, e.g. `DB_DATABASE=my_gamez`), and `INTERNAL_API_SECRET` (same as monolith).
- Internal routes are under `/internal/*` and require `X-Internal-Secret` or `Authorization: Bearer <INTERNAL_API_SECRET>`.

## Routes (stubs until Phase 2.2)

- `POST /internal/task_start`
- `POST /internal/task_claim_reward`
- `POST /internal/task_track`
- `POST /internal/get_daily_tasks`

Implemented in Phase 2.2. When `TASK_SERVICE_ENABLED=true`, run these in the task service (not monolith):

- `php artisan tasks:daily-reset` (e.g. daily at midnight)
- `php artisan tasks:distribute-rewards` (e.g. every 5 minutes)
