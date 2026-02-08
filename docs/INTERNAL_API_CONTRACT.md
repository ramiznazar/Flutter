# Internal API Contract (Gateway to Services)

This document describes the request/response shape for endpoints that the monolith may forward to internal services. The **public** API (paths and bodies) remains unchanged for the Flutter app; the gateway forwards the same body/query to the service and returns the same response.

**Rule:** Balance, token, and mining state must **never** be read from cache; always from MySQL. See [MICROSERVICES_IMPLEMENTATION_TRACKER.md](MICROSERVICES_IMPLEMENTATION_TRACKER.md#redis-rules-no-wrong-balance).

---

## Mining endpoints

Base path when called internally: same as public (gateway forwards as-is). Internal service may expose e.g. `POST /internal/start_mining` with the same body.

### POST /api/start_mining (internal: POST /internal/start_mining)

**Request (JSON):**
- `email` (required): string, email
- `coins` (required): numeric, min 0
- `reason` (optional): string, e.g. "get"
- `balance` (optional): numeric, frontend-calculated balance (for "get" sync)

**Response 200 (idle after completion or get):**
- `success`, `message` ("idle"), `server_time`, `mining_end_time` (empty), `total_team`, `coin`, `balance`, `token_per_sec`, `mining_speed`, `usdt`, `total_mining_time_in_sec`

**Response 200 (in_progress):**
- `success`, `message` ("in_progress"), `server_time`, `mining_end_time`, `total_team`, `coin`, `balance`, `starting_balance`, `token_per_sec`, `mining_speed`, `usdt`, `total_mining_time_in_sec`, `seconds_remaining`, `elapsed_seconds`

**Response 400:** `success: false`, `message` (validation or "Insufficient coins" / "Maximum mining time exceeded")
**Response 404:** `success: false`, `message`: "User not found or account not active"

---

### GET /api/mining_status (internal: GET /internal/mining_status)

**Query:** `email` (required)

**Response 200:** Same shape as start_mining "get" / idle or in_progress (server_time, balance, coin, mining_end_time, token_per_sec, etc.)

**Response 400/404:** Same as above.

---

### POST /api/start_coin (internal: POST /internal/start_coin)

**Request:** `email` (required), `reason` (required): "get" | "start"

**Response 200 (get):** `success`, `server_time`, `coin_end_time`, `total_coin_claim`, `progress` ("idle" | "in_progress")
**Response 200 (start):** `success`, `message`, `server_time`, `coin_end_time`, `total_coin_claim`, `progress`
**Response 400:** Limit exceeded or invalid reason
**Response 404:** User not found

---

### POST /api/claim_bonus (internal: POST /internal/claim_bonus)

**Request:** (any; gateway forwards as-is)

**Response 200:** `success: true`, `message: "Bonus claimed"`

---

### POST /api/bonus_history (internal: POST /internal/bonus_history)

**Response 200:** `success: true`, `data: []` (or array of history items)

---

### POST /api/social_claim (internal: POST /internal/social_claim)

**Request:** `email` (required), `ID` or `id` (social media ID)

**Response 200:** success, message, token amount etc.
**Response 400/404:** validation or not found

---

### POST /api/social_list (internal: POST /internal/social_list)

**Request:** `email` (required)

**Response 200:** `success`, `social_media_setting` (array with claimed flag)

---

### POST /api/get_daily_reward_status (internal: POST /internal/get_daily_reward_status)

**Request:** `email` (required)

**Response 200:** `success`, `claimed`, `available`, `message`, `claimed_count`, `max_per_period`, `can_claim`, `cooldown_minutes`, `cooldown_until`, `seconds_remaining`, `next_period_reset`, `next_available_at`, `period_reset_in_seconds`, `last_claimed_at`, `coins_claimed`

---

### POST /api/add_daily_reward (internal: POST /internal/add_daily_reward)

**Request:** `email` (required), `coins` (optional, numeric 0–10)

**Response 200:** `success`, `message`, `coins_added`, `new_balance`, `is_mining_active`, `claimed_count`, `max_per_period`, `can_claim_again_in_seconds`, `next_period_reset`
**Response 400:** All claimed or cooldown
**Response 404:** User not found

---

## Task endpoints (Phase 2)

To be documented when implementing Task Service: `task_start`, `task_claim_reward`, `task_track`, `get_daily_tasks`. Same pattern: request/response must match current TaskController.

## Gamification endpoints (Phase 3)

To be documented when implementing Gamification Service: mystery_box_*, booster_*, ad_booster_*.

## KYC endpoints (Phase 4)

Internal base path: `/internal`. All KYC endpoints are POST unless noted. Request/response match monolith `KycController`.

### POST /internal/kyc_check_eligibility

**Request (JSON):** `email` (required, email)

**Response 200:** `success: true`, `data`: `mining_sessions`, `mining_sessions_required`, `referrals`, `referrals_required`, `is_eligible`, `can_submit`, `kyc_status`, `mining_progress`, `referrals_progress`

**Response 400:** `success: false`, `message`: "Email is required"
**Response 404:** `success: false`, `message`: "User not found or account not active"

---

### POST /internal/kyc_submit (alias: POST /internal/submit_kyc)

**Request (JSON):** `email` (required), `full_name` (required), `dob` (required, Y-m-d), `front_image` (required, base64), `back_image` (required, base64)

**Response 200:** `success: true`, `message`, `data`: `kyc_id`, `status`, `didit_request_id`, `verification_status`

**Response 400:** Validation, "Not eligible to submit KYC or already submitted", "KYC submission already exists...", or invalid image format
**Response 404:** User not found or account not active

---

### POST /internal/kyc_get_status

**Request (JSON):** `email` (required)

**Response 200:** `success: true`, `data`: `status`, `full_name`, `submitted_at`, `admin_notes` — or if no submission: `status: null`, `message: "No KYC submission found"`

**Response 400/404:** Same as kyc_check_eligibility

---

### POST /internal/get_kyc_progress

Same contract as `kyc_check_eligibility` (returns eligibility/progress data).

---

### POST /internal/didit_create_request

**Request (JSON):** `email` (required), `full_name` (required), `dob` (required, Y-m-d)

**Response 200:** `success: true`, `message`, `data`: `request_id`, `verification_url` (gateway public URL + `/api/kyc_submit`), `verification_session_id`, `session_id`, `email`, `full_name`, `dob`, `can_proceed`, `verification_method`, `next_endpoint`, `required_fields`

**Response 400/404:** Same as kyc_submit (eligibility / already submitted). Set `GATEWAY_PUBLIC_URL` in KYC service .env so `verification_url` points to the monolith gateway.
