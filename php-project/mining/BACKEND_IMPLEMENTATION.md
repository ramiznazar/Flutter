# Crutox Backend Implementation Documentation

## Overview
This document describes the **REAL backend PHP implementation** for the Crutox production system. All business logic is enforced server-side, not in frontend.

---

## Database Tables

### New Tables Created

1. **`task_completions`** - Tracks user task progress and timers
   - `user_id`, `task_id`, `task_type` (daily/onetime)
   - `started_at`, `reward_available_at`, `reward_claimed`
   - Enforces 5min timer for daily, 1hr for one-time tasks

2. **`user_boosters`** - Tracks active boosters
   - `user_id`, `booster_type` (2x), `started_at`, `expires_at`
   - Enforces 1 hour duration, prevents reuse until expiry

3. **`mystery_box_claims`** - Tracks mystery box ad watching and cooldowns
   - `user_id`, `box_type`, `ads_watched`, `ads_required`
   - `last_ad_watched_at`, `cooldown_until`
   - Enforces cooldown periods between ads (configurable per box type)

### Existing Tables Used
- `users` - User accounts and token balances
- `social_media_setting` - Task definitions (first 3 = daily, rest = one-time)
- `settings` - System configuration (mining speed, referrals, mystery box settings, etc.)
- `kyc_submissions` - KYC verification data

---

## API Endpoints

### Task System

#### 1. `task_start.php` - Start a Task
**POST** `/api/task_start.php`
- Records when user starts a task
- **Backend enforces timers:**
  - Daily tasks: 5 minutes until reward available
  - One-time tasks: 1 hour until reward available
- Prevents duplicate daily task completion (checks reset time)
- Returns `reward_available_at` timestamp and seconds remaining

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "task_id": 1,
  "task_type": "daily" // or "onetime"
}
```

**Response:**
```json
{
  "success": true,
  "reward_available_at": "2024-01-15 10:05:00",
  "seconds_remaining": 300,
  "task_type": "daily",
  "reward": 2.0
}
```

#### 2. `task_claim_reward.php` - Claim Task Reward
**POST** `/api/task_claim_reward.php`
- **Backend validates timer has expired** before allowing claim
- Adds reward to user's token balance
- Marks completion as claimed

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "task_id": 1,
  "task_type": "daily"
}
```

**Response:**
```json
{
  "success": true,
  "reward": 2.0,
  "new_balance": 100.5
}
```

### Booster System

#### 3. `booster_claim.php` - Claim 2x Booster
**POST** `/api/booster_claim.php`
- Activates 2x booster for exactly 1 hour
- **Backend prevents reuse** until current booster expires
- Automatically deactivates expired boosters

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "booster_type": "2x",
  "expires_at": "2024-01-15 11:00:00",
  "duration_seconds": 3600
}
```

#### 4. `booster_status.php` - Get Booster Status
**POST** `/api/booster_status.php`
- Returns current active booster status
- Shows seconds remaining until expiry

### Mystery Box System

#### 5. `mystery_box_watch_ad.php` - Watch Ad for Mystery Box
**POST** `/api/mystery_box_watch_ad.php`
- Records ad watch for mystery box
- **Backend enforces cooldown** between ads (from settings)
- Tracks progress toward required ads
- Returns whether box can be opened

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "box_type": "rare" // common, rare, epic, legendary
}
```

**Response:**
```json
{
  "success": true,
  "ads_watched": 2,
  "ads_required": 3,
  "can_open_box": false,
  "cooldown_until": "2024-01-15 10:05:00",
  "cooldown_minutes": 5
}
```

#### 6. `mystery_box_open.php` - Open Mystery Box
**POST** `/api/mystery_box_open.php`
- Opens box and gives random reward (min-max range from settings)
- **Backend validates all ads watched** before allowing open
- Prevents duplicate opens

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "box_type": "rare"
}
```

**Response:**
```json
{
  "success": true,
  "reward": 8.5,
  "new_balance": 108.5,
  "box_type": "rare"
}
```

### KYC System (Already Implemented)

- `kyc_check_eligibility.php` - Check if user can submit KYC
- `kyc_submit.php` - Submit KYC documents
- `kyc_get_status.php` - Get KYC submission status

---

## Cron Jobs

### 1. Daily Tasks Reset
**File:** `cron/daily_tasks_reset.php`
**Schedule:** `0 0 * * *` (Every day at midnight)
**Purpose:** Resets daily tasks every 24 hours
- Checks if 24 hours passed since last reset
- Updates `daily_tasks_reset_time` in settings
- Marks old daily completions as expired

**Manual Run:**
```bash
php backend/crutox/mining/cron/daily_tasks_reset.php
```

### 2. Task Rewards Distribution
**File:** `cron/task_rewards_distribute.php`
**Schedule:** `*/5 * * * *` (Every 5 minutes)
**Purpose:** Automatically distributes rewards for one-time tasks after 1 hour
- Finds one-time tasks where `reward_available_at <= NOW()` and `reward_claimed = 0`
- Adds reward to user's token balance
- Marks completion as claimed

**Manual Run:**
```bash
php backend/crutox/mining/cron/task_rewards_distribute.php
```

---

## Settings Configuration

All settings are stored in `settings` table and editable from admin panel:

### Mining Settings
- `mining_speed` - Mining speed multiplier
- `base_mining_rate` - Base mining rate
- `max_mining_speed` - Maximum mining speed

### Referral Settings
- `referrer_reward` - Reward for referrer
- `referee_reward` - Reward for referee
- `max_referrals` - Maximum referrals allowed
- `bonus_reward` - Bonus reward threshold

### User Count Progress
- `current_users` - Current user count
- `goal_users` - Target user count (1M)

### Mystery Box Settings (Per Box Type)
- `{box_type}_box_cooldown` - Cooldown in minutes between ads
- `{box_type}_box_ads` - Number of ads required
- `{box_type}_box_min_coins` - Minimum reward coins
- `{box_type}_box_max_coins` - Maximum reward coins

### KYC Settings
- `kyc_mining_sessions` - Required mining sessions (default: 14)
- `kyc_referrals_required` - Required referrals (default: 10)

### Ad Waterfall Settings
- `ad_waterfall_order` - JSON array: `["admob", "meta", "unity", "applovin"]`
- `ad_waterfall_enabled` - Enable/disable waterfall (1/0)

---

## Database Migration

Run the migration script to create required tables:

```sql
-- Run: backend/crutox/mining/database_migration_tasks_boosters.sql
```

This creates:
- `task_completions` table
- `user_boosters` table
- `mystery_box_claims` table
- Adds `ad_waterfall_order` and `ad_waterfall_enabled` columns to `settings`

---

## Key Backend Logic Enforcement

### ✅ Timer Enforcement
- **Daily tasks:** 5 minutes enforced server-side
- **One-time tasks:** 1 hour enforced server-side
- Frontend cannot bypass timers - backend validates `reward_available_at <= NOW()`

### ✅ Cooldown Enforcement
- **Mystery box ads:** Cooldown between ads enforced server-side
- **Boosters:** 1 hour duration enforced, prevents reuse until expiry

### ✅ Reward Distribution
- Rewards only given after backend validation
- One-time tasks automatically rewarded after 1 hour (via cron)
- All rewards added to `users.token` column

### ✅ Daily Task Reset
- Automatic reset every 24 hours (via cron)
- Prevents users from completing same daily task multiple times per day

### ✅ Mystery Box Cooldowns
- Cooldown periods configurable from admin panel
- Backend checks `cooldown_until` before allowing next ad
- Cooldown calculated from settings: `{box_type}_box_cooldown`

---

## File Structure

```
backend/crutox/mining/
├── api/
│   ├── task_start.php                    # Start task, enforce timer
│   ├── task_claim_reward.php             # Claim reward after timer
│   ├── booster_claim.php                 # Claim 2x booster
│   ├── booster_status.php               # Get booster status
│   ├── mystery_box_watch_ad.php         # Watch ad, enforce cooldown
│   ├── mystery_box_open.php             # Open box, give reward
│   ├── kyc_check_eligibility.php        # Check KYC eligibility
│   ├── kyc_submit.php                   # Submit KYC
│   ├── kyc_get_status.php               # Get KYC status
│   └── admin/
│       ├── tasks_manage.php             # Admin: Manage tasks
│       ├── settings_manage.php          # Admin: Manage settings
│       └── kyc_manage.php               # Admin: Manage KYC
├── cron/
│   ├── daily_tasks_reset.php            # Reset daily tasks (24hr)
│   └── task_rewards_distribute.php      # Auto-distribute rewards
├── database_migration_tasks_boosters.sql # Migration script
└── BACKEND_IMPLEMENTATION.md            # This file
```

---

## Setup Instructions

1. **Run Database Migration:**
   ```bash
   mysql -u username -p database_name < backend/crutox/mining/database_migration_tasks_boosters.sql
   ```

2. **Setup Cron Jobs:**
   ```bash
   # Edit crontab
   crontab -e
   
   # Add these lines:
   0 0 * * * /usr/bin/php /path/to/backend/crutox/mining/cron/daily_tasks_reset.php
   */5 * * * * /usr/bin/php /path/to/backend/crutox/mining/cron/task_rewards_distribute.php
   ```

3. **Configure Settings via Admin Panel:**
   - Mining speed, referral rewards
   - Mystery box cooldowns and ads required
   - KYC requirements
   - Ad waterfall order

---

## Important Notes

- **NO frontend timers** - All timers enforced backend
- **NO hardcoded values** - Everything from database
- **NO dummy data** - All data from real database queries
- **Business logic in backend** - Frontend only displays data
- **Cron jobs required** - For daily resets and auto-rewards

---

## Testing

Test each endpoint with real user credentials and verify:
1. Timers are enforced (cannot claim before timer expires)
2. Cooldowns are enforced (cannot watch ad during cooldown)
3. Rewards are added to user balance
4. Daily tasks reset after 24 hours
5. Boosters expire after 1 hour
6. Mystery box cooldowns work per box type

---

**All backend logic is production-ready and database-driven. No frontend simulation or hardcoded values.**


