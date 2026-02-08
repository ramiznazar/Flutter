# E2E Testing: Task (Phase 2.4)

Use this to verify task endpoints work with the monolith only, then with the task microservice. **Keep `TASK_SERVICE_ENABLED=false` in production until E2E with the service is validated.**

---

## 1. Test with task in monolith (default)

**Setup:** In monolith `.env`:

- `TASK_SERVICE_ENABLED=false` (or omit; default is false)

**Steps:**

1. Start the monolith: `php artisan serve`
2. From Flutter app or Postman, call task endpoints (e.g. `POST /api/task_start`, `POST /api/task_claim_reward`, `POST /api/task_track`, `POST /api/get_daily_tasks`) with valid `email` and payload.
3. Confirm responses match the existing API (success, reward_available_at, data array for get_daily_tasks, etc.).

**Expected:** All task routes are handled by the monolith `TaskController`; no outbound calls to the task service.

---

## 2. Test with task microservice

**Setup:**

- Monolith `.env`: `TASK_SERVICE_ENABLED=true`, `TASK_SERVICE_URL=http://127.0.0.1:8002`, `INTERNAL_API_SECRET=<shared-secret>`
- Task service: in `services/task/`, copy `.env.example` to `.env`, set `APP_KEY`, same `DB_*` as monolith (e.g. `DB_DATABASE=my_gamez`), and same `INTERNAL_API_SECRET`
- Start task service: `cd services/task && php artisan serve --port=8002`
- (Optional) Schedule in task service: `php artisan tasks:daily-reset` (e.g. daily), `php artisan tasks:distribute-rewards` (e.g. every 5 min)

**Steps:**

1. Call the **same** public endpoints from the Flutter app or Postman. Do not call the task service directly; always go through the monolith.
2. Confirm responses are identical in shape and values to the monolith-only run.

**Expected:** Monolith forwards to the task service; responses match. If the task service is down or returns 5xx, the gateway retries once then returns an error.

---

## 3. Default and production

- In **.env.example** and docs, `TASK_SERVICE_ENABLED` is **false** so new installs and production stay on the monolith until you explicitly enable and test the service.
- After E2E with the service is validated, you can set `TASK_SERVICE_ENABLED=true` where the task service is deployed.
