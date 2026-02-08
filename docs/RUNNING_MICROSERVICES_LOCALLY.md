# Running the Microservices Backend Locally

This guide gets the Crutox backend running on your machine: **monolith only** (simplest) or **monolith + all four microservices**. All apps use the **same MySQL database** (`my_gamez`), so data (users, mining, tasks, KYC, etc.) is shared and loads correctly.

---

## Prerequisites

- **PHP 8.1+** (same as monolith)
- **Composer**
- **MySQL** (database `my_gamez` created and migrated from the monolith)
- **Redis** (optional; only if you use `CACHE_DRIVER=redis` or `QUEUE_CONNECTION=redis`)

---

## Option A: Run as monolith only (recommended first)

With all microservice flags **disabled**, the monolith handles every request. No extra processes needed. Data loads from the same MySQL DB you already use.

### 1. Monolith setup

From the **project root** (the Laravel app with `routes/api.php`, not inside `services/`):

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

Ensure your **monolith** `.env` has:

- `DB_DATABASE=my_gamez`
- `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` (match your local MySQL)
- Leave service flags **false** (default):

```env
MINING_SERVICE_ENABLED=false
TASK_SERVICE_ENABLED=false
GAMIFICATION_SERVICE_ENABLED=false
KYC_SERVICE_ENABLED=false
```

You can omit `MINING_SERVICE_URL`, etc., or set them for later; they are only used when the corresponding `*_ENABLED=true`.

### 3. Run the monolith

```bash
php artisan serve
# Or use port 8000:  php artisan serve --port=8000
```

- API base: **http://127.0.0.1:8000** (or 8000 if you used `--port=8000`)
- Flutter app should point to this URL (e.g. `http://127.0.0.1:8000` or your machine IP for a device).

**Data:** All endpoints (mining, tasks, gamification, KYC, auth, etc.) run in the monolith and read/write the same `my_gamez` database. No extra setup.

---

## Option B: Run monolith + microservices

Use this when you want to test the gateway forwarding to the mining, task, gamification, and KYC services. Each service is a separate Laravel app; all use the **same** MySQL database and the **same** `INTERNAL_API_SECRET`.

### 1. Monolith setup

Same as Option A:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

In **monolith** `.env`:

- `DB_DATABASE=my_gamez` (and correct DB_HOST, DB_USERNAME, DB_PASSWORD)
- Choose a **shared secret** and set it in the monolith and in **every** service:

```env
INTERNAL_API_SECRET=your-local-secret-string
```

- Service URLs (local):

```env
MINING_SERVICE_URL=http://127.0.0.1:8001
MINING_SERVICE_ENABLED=true
MINING_SERVICE_TIMEOUT=10

TASK_SERVICE_URL=http://127.0.0.1:8002
TASK_SERVICE_ENABLED=true
TASK_SERVICE_TIMEOUT=10

GAMIFICATION_SERVICE_URL=http://127.0.0.1:8003
GAMIFICATION_SERVICE_ENABLED=true
GAMIFICATION_SERVICE_TIMEOUT=10

KYC_SERVICE_URL=http://127.0.0.1:8004
KYC_SERVICE_ENABLED=true
KYC_SERVICE_TIMEOUT=10
```

- Run monolith on port **8000** (so Flutter and KYC `verification_url` stay consistent):

```bash
php artisan serve --port=8000
```

### 2. Mining service

```bash
cd services/mining
composer install
cp .env.example .env
php artisan key:generate
```

Edit **services/mining/.env**:

- Same DB as monolith: `DB_DATABASE=my_gamez`, `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`
- Same secret: `INTERNAL_API_SECRET=your-local-secret-string` (same value as monolith)

Run:

```bash
php artisan serve --port=8001
```

### 3. Task service

```bash
cd services/task
composer install
cp .env.example .env
php artisan key:generate
```

Edit **services/task/.env**:

- `DB_DATABASE=my_gamez` and same DB credentials
- `INTERNAL_API_SECRET=your-local-secret-string`

Run:

```bash
php artisan serve --port=8002
```

### 4. Gamification service

```bash
cd services/gamification
composer install
cp .env.example .env
php artisan key:generate
```

Edit **services/gamification/.env**:

- `DB_DATABASE=my_gamez` and same DB credentials
- `INTERNAL_API_SECRET=your-local-secret-string`

Run:

```bash
php artisan serve --port=8003
```

### 5. KYC service

```bash
cd services/kyc
composer install
cp .env.example .env
php artisan key:generate
```

Edit **services/kyc/.env**:

- `DB_DATABASE=my_gamez` and same DB credentials
- `INTERNAL_API_SECRET=your-local-secret-string`
- So KYC responses (e.g. `didit_create_request`) point back to the monolith:
  - `GATEWAY_PUBLIC_URL=http://127.0.0.1:8000` (or `http://localhost:8000` if your Flutter app uses that)

Run:

```bash
php artisan serve --port=8004
```

### 6. Start order (Option B)

1. Start **MySQL** (and Redis if you use it).
2. Start **monolith**: from project root, `php artisan serve --port=8000`.
3. Start each service in its own terminal:
   - `cd services/mining && php artisan serve --port=8001`
   - `cd services/task && php artisan serve --port=8002`
   - `cd services/gamification && php artisan serve --port=8003`
   - `cd services/kyc && php artisan serve --port=8004`

API base for the Flutter app: **http://127.0.0.1:8000**. All requests go to the monolith; it forwards mining/task/gamification/KYC to the correct service when enabled.

---

## Will data load correctly?

**Yes.** All five apps (monolith + four services) use the **same MySQL database** (`my_gamez`). There are no separate “service databases” for this setup. So:

- Users, mining state, tasks, KYC submissions, settings, etc. are all in one place.
- Whether a request is handled by the monolith or by a microservice, it reads and writes the same tables.
- The Flutter app always talks to the monolith (e.g. `http://127.0.0.1:8000`); the monolith either handles the request itself or proxies to the right service. Response shape is the same.

---

## Port summary (local)

| App           | Port | Role                          |
|---------------|------|-------------------------------|
| Monolith      | 8000 | API gateway + auth, user, etc. |
| Mining        | 8001 | Mining / bonus / daily reward |
| Task          | 8002 | Daily tasks                  |
| Gamification  | 8003 | Mystery box, booster, ad booster |
| KYC           | 8004 | KYC eligibility, submit, status |

---

## Troubleshooting

- **"Internal API not configured" (503)**  
  The service expects `INTERNAL_API_SECRET` and the gateway sends it. Set the **same** non-empty value in monolith and in that service `.env`.

- **"Connection refused" when forwarding**  
  Start the corresponding service on the right port (8001–8004). Check `*_SERVICE_URL` in monolith (e.g. `http://127.0.0.1:8001`).

- **Database errors in a service**  
  Ensure that service’s `.env` has `DB_DATABASE=my_gamez` and the same `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` as the monolith. Run migrations from the **monolith** (services do not add new tables; they use the existing schema).

- **KYC `verification_url` wrong in app**  
  Set in **services/kyc/.env**: `GATEWAY_PUBLIC_URL=http://127.0.0.1:8000` (or the URL your Flutter app uses for the API).

- **Run monolith only again**  
  Set all `*_SERVICE_ENABLED=false` in monolith `.env` and restart the monolith. You can stop the four service processes.

---

## Later: deploying to your three cloud servers

When you move to the cloud, you will:

- Put the **monolith** on one server (or behind a load balancer) and set `APP_URL` and `*_SERVICE_URL` to the internal URLs of the other servers.
- Run **each microservice** on its own server (or group), using the same `DB_*` (or a shared MySQL) and the same `INTERNAL_API_SECRET`.
- Set `GATEWAY_PUBLIC_URL` in the KYC service to the public URL of the monolith gateway.

A separate deployment guide can document exact env vars and URLs for your three servers.
