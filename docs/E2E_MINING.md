# E2E Testing: Mining (Phase 1.4)

Use this to verify mining works with the monolith only, then with the mining microservice. **Keep `MINING_SERVICE_ENABLED=false` in production until E2E with the service is validated.**

---

## 1. Test with mining in monolith (default)

**Setup:** In monolith `.env`:

- `MINING_SERVICE_ENABLED=false` (or omit; default is false)

**Steps:**

1. Start the monolith: `php artisan serve`
2. From Flutter app or Postman, call mining endpoints (e.g. `POST /api/start_mining`, `GET /api/mining_status`, `POST /api/start_coin`, etc.) with valid `email` and payload.
3. Confirm responses match the [API contract](INTERNAL_API_CONTRACT.md) (success, balance, mining_end_time, etc.).

**Expected:** All mining routes are handled by the monolith `MiningController`; no outbound calls to a mining service.

---

## 2. Test with mining microservice

**Setup:**

- Monolith `.env`: `MINING_SERVICE_ENABLED=true`, `MINING_SERVICE_URL=http://127.0.0.1:8001`, `INTERNAL_API_SECRET=<shared-secret>`
- Mining service: in `services/mining/`, copy `.env.example` to `.env`, set `APP_KEY`, same `DB_*` as monolith (e.g. `DB_DATABASE=my_gamez`), and same `INTERNAL_API_SECRET`
- Start mining service: `cd services/mining && php artisan serve --port=8001`
- (Optional) Run balance updater in mining service: `php artisan mining:update-balances` every 30s (cron)

**Steps:**

1. Call the **same** public endpoints from the Flutter app or Postman (e.g. `POST /api/start_mining`, `GET /api/mining_status`). Do not call the mining service directly; always go through the monolith.
2. Confirm responses are identical in shape and values to the monolith-only run (same balances, mining status, etc.).

**Expected:** Monolith forwards to the mining service; responses match. If the mining service is down or returns 5xx, the gateway retries once then returns an error.

---

## 3. Default and production

- In **.env.example** and docs, `MINING_SERVICE_ENABLED` is **false** so new installs and production stay on the monolith until you explicitly enable and test the service.
- After E2E with the service is validated, you can set `MINING_SERVICE_ENABLED=true` in the environment where the mining service is deployed.
