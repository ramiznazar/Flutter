# E2E Testing: KYC (Phase 4.4)

Use this to verify KYC endpoints work with the monolith only, then with the KYC microservice. **Keep `KYC_SERVICE_ENABLED=false` in production until E2E with the service is validated.**

---

## 1. Test with KYC in monolith (default)

**Setup:** In monolith `.env`:

- `KYC_SERVICE_ENABLED=false` (or omit; default is false)

**Steps:**

1. Start the monolith: `php artisan serve`
2. From Flutter app or Postman, call KYC endpoints with valid `email` and payload:
   - **Eligibility:** `POST /api/kyc_check_eligibility` (body: `{"email":"user@example.com"}`)
   - **Progress:** `POST /api/get_kyc_progress` (same as eligibility)
   - **Status:** `POST /api/kyc_get_status` (body: `{"email":"user@example.com"}`)
   - **Didit create request:** `POST /api/didit_create_request` (body: `email`, `full_name`, `dob`)
   - **Submit:** `POST /api/kyc_submit` or `POST /api/submit_kyc` (body: `email`, `full_name`, `dob`, `front_image`, `back_image` base64)
3. Confirm responses match the existing API (success, message, data as applicable). For GET on `/api/kyc_submit`, expect 405.

**Expected:** All KYC routes are handled by the monolith (`KycController`); no outbound calls to the KYC service.

---

## 2. Test with KYC microservice

**Setup:**

- Monolith `.env`: `KYC_SERVICE_ENABLED=true`, `KYC_SERVICE_URL=http://127.0.0.1:8004`, `INTERNAL_API_SECRET=<shared-secret>`
- KYC service: in `services/kyc/`, copy `.env.example` to `.env`, set `APP_KEY`, same `DB_*` as monolith (e.g. `DB_DATABASE=my_gamez`), and same `INTERNAL_API_SECRET`. Optionally set `GATEWAY_PUBLIC_URL` to the monolith base URL (e.g. `http://127.0.0.1:8000`) so `didit_create_request` returns the correct `verification_url`.
- Start KYC service: `cd services/kyc && php artisan serve --port=8004`

**Steps:**

1. Call the **same** public endpoints from the Flutter app or Postman. Do not call the KYC service directly; always go through the monolith.
2. Confirm responses are identical in shape and values to the monolith-only run.

**Expected:** Monolith forwards to the KYC service; responses match. If the KYC service is down or returns 5xx, the gateway retries once then returns an error.

---

## 3. Default and production

- In **.env.example** and docs, `KYC_SERVICE_ENABLED` is **false** so new installs and production stay on the monolith until you explicitly enable and test the service.
- After E2E with the service is validated, you can set `KYC_SERVICE_ENABLED=true` where the KYC service is deployed.
