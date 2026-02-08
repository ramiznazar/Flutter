# E2E Testing: Gamification (Phase 3.4)

Use this to verify gamification endpoints work with the monolith only, then with the gamification microservice. **Keep `GAMIFICATION_SERVICE_ENABLED=false` in production until E2E with the service is validated.**

---

## 1. Test with gamification in monolith (default)

**Setup:** In monolith `.env`:

- `GAMIFICATION_SERVICE_ENABLED=false` (or omit; default is false)

**Steps:**

1. Start the monolith: `php artisan serve`
2. From Flutter app or Postman, call gamification endpoints with valid `email` and payload:
   - **Mystery Box:** `POST /api/mystery_box_watch_ad`, `POST /api/mystery_box_click`, `POST /api/mystery_box_open`, `POST /api/mystery_box_details`
   - **Booster:** `POST /api/booster_status`, `POST /api/booster_claim`
   - **Ad Booster:** `POST /api/ad_booster_status`, `POST /api/ad_booster_claim`
3. Confirm responses match the existing API (success, message, data as applicable).

**Expected:** All gamification routes are handled by the monolith (`MysteryBoxController`, `BoosterController`, `AdBoosterController`); no outbound calls to the gamification service.

---

## 2. Test with gamification microservice

**Setup:**

- Monolith `.env`: `GAMIFICATION_SERVICE_ENABLED=true`, `GAMIFICATION_SERVICE_URL=http://127.0.0.1:8003`, `INTERNAL_API_SECRET=<shared-secret>`
- Gamification service: in `services/gamification/`, copy `.env.example` to `.env`, set `APP_KEY`, same `DB_*` as monolith (e.g. `DB_DATABASE=my_gamez`), and same `INTERNAL_API_SECRET`
- Start gamification service: `cd services/gamification && php artisan serve --port=8003`

**Steps:**

1. Call the **same** public endpoints from the Flutter app or Postman. Do not call the gamification service directly; always go through the monolith.
2. Confirm responses are identical in shape and values to the monolith-only run.

**Expected:** Monolith forwards to the gamification service; responses match. If the gamification service is down or returns 5xx, the gateway retries once then returns an error.

---

## 3. Default and production

- In **.env.example** and docs, `GAMIFICATION_SERVICE_ENABLED` is **false** so new installs and production stay on the monolith until you explicitly enable and test the service.
- After E2E with the service is validated, you can set `GAMIFICATION_SERVICE_ENABLED=true` where the gamification service is deployed.
