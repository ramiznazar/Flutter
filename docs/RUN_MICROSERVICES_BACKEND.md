# Run the microservices backend (complete guide)

This project has **two ways** to run the backend:

| Mode | What runs | When to use |
|------|-----------|-------------|
| **Monolith only** | Single Laravel app (project root). All APIs run in one process. | Simplest; one terminal, one URL. |
| **Micro architecture** | Monolith (gateway) + 4 services (mining, task, gamification, KYC). Same API URLs; gateway forwards to services. | When you want to run the split backend locally. |

Both use the **same MySQL database** (`my_gamez`). Data (users, mining, tasks, KYC, etc.) is shared and loads correctly.

---

## Prerequisites

- **PHP 8.1+**
- **Composer**
- **MySQL** with database `my_gamez` (create it if needed; run migrations from the **monolith** once)

---

## 1. Install packages (one-time)

From the project root (folder that contains `routes/api.php` and `services/`):

```powershell
# Monolith
composer install

# Mining service
cd services\mining
composer install
cd ..\..

# Task service
cd services\task
composer install
cd ..\..

# Gamification service
cd services\gamification
composer install
cd ..\..

# KYC service
cd services\kyc
composer install
cd ..\..
```

Or run each in a separate terminal:

```powershell
composer install
cd services\mining && composer install
cd ..\task && composer install
cd ..\gamification && composer install
cd ..\kyc && composer install
```

---

## 2. Environment files

### Monolith (project root `.env`)

Already set for micro architecture if you use the repo’s `.env`:

- `DB_DATABASE=my_gamez`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`
- `INTERNAL_API_SECRET=crutox-internal-local` (must match all services)
- `*_SERVICE_ENABLED=true` and `*_SERVICE_URL=http://127.0.0.1:8001` (and 8002, 8003, 8004)

To run **monolith only** (no services), set all to `false`:

```env
MINING_SERVICE_ENABLED=false
TASK_SERVICE_ENABLED=false
GAMIFICATION_SERVICE_ENABLED=false
KYC_SERVICE_ENABLED=false
```

### Services (`services/mining`, `services/task`, `services/gamification`, `services/kyc`)

Each service `.env` is set to:

- `DB_CONNECTION=mysql`, `DB_DATABASE=my_gamez`, same `DB_HOST` / `DB_USERNAME` / `DB_PASSWORD` as monolith
- `INTERNAL_API_SECRET=crutox-internal-local` (same as monolith)

KYC also has `GATEWAY_PUBLIC_URL=http://127.0.0.1:8000` so links in responses point to the gateway.

If your MySQL user/password differ, update `DB_USERNAME` and `DB_PASSWORD` in the monolith and in **every** service `.env`.

---

## 3. Run monolith only (one process)

1. Start MySQL.
2. From **project root**:

```powershell
php artisan serve --port=8000
```

3. API base: **http://127.0.0.1:8000**  
   Point your Flutter app to this URL. All requests are handled by the monolith.

---

## 4. Run micro architecture (monolith + 4 services)

1. Start **MySQL**.

2. Open **5 terminals**. In each, go to the correct folder and run one of these:

**Terminal 1 – Monolith (gateway)**  
From project root:

```powershell
php artisan serve --port=8000
```

**Terminal 2 – Mining service**

```powershell
cd services\mining
php artisan serve --port=8001
```

**Terminal 3 – Task service**

```powershell
cd services\task
php artisan serve --port=8002
```

**Terminal 4 – Gamification service**

```powershell
cd services\gamification
php artisan serve --port=8003
```

**Terminal 5 – KYC service**

```powershell
cd services\kyc
php artisan serve --port=8004
```

3. API base for the Flutter app: **http://127.0.0.1:8000**  
   All requests go to the monolith; it forwards mining/task/gamification/KYC to the right service.

### Ports

| App          | Port | Role                          |
|-------------|------|-------------------------------|
| Monolith    | 8000 | Gateway + auth, user, etc.    |
| Mining      | 8001 | Mining, bonus, daily reward   |
| Task        | 8002 | Daily tasks                   |
| Gamification| 8003 | Mystery box, booster         |
| KYC         | 8004 | KYC eligibility, submit      |

---

## 5. Generate app keys (if you copied `.env.example`)

If a service shows “No application encryption key”:

```powershell
cd services\mining
php artisan key:generate
```

Repeat for `services\task`, `services\gamification`, `services\kyc` if needed. Monolith: from project root, `php artisan key:generate`.

---

## 6. Troubleshooting

| Issue | Fix |
|-------|-----|
| **503 "Internal API not configured"** | Set `INTERNAL_API_SECRET` in that service `.env` to the **same** value as in the monolith `.env`. |
| **Connection refused** when calling API | Start the corresponding service on 8001–8004. Check `*_SERVICE_URL` in monolith `.env`. |
| **Database error** in a service | Use same `DB_DATABASE=my_gamez`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` as monolith in that service `.env`. |
| **Session/cache table not found** (task/gamification/kyc) | Those services use `SESSION_DRIVER=database` and `CACHE_STORE=database`. Run migrations **once** from the monolith so `my_gamez` has the tables; services will use them. |
| **Run monolith only again** | Set all `*_SERVICE_ENABLED=false` in monolith `.env`, restart the monolith. You can stop the four service terminals. |

---

## 7. Data and API

- **One database:** Monolith and all services use the same MySQL database (`my_gamez`). There are no separate DBs per service.
- **Same API:** Flutter always talks to the monolith (e.g. `http://127.0.0.1:8000`). Paths and request/response shapes do not change when you switch between “monolith only” and “micro architecture”.
- **Migrations:** Run only from the **monolith**: `php artisan migrate`. Do not run migrations from the service folders for shared tables.

You’re set: install packages, fix DB credentials if needed, then run either **monolith only** (Section 3) or **micro architecture** (Section 4).
