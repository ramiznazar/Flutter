# Admin Panel API Endpoints - Complete Summary

## Overview

All admin panel functionality that is used by the mobile app has corresponding API endpoints in `api/admin/`. These endpoints provide GET access to admin-managed content.

---

## API Endpoints in `api/admin/`

### 1. News Management
**File:** `api/admin/news_manage.php`

**GET** - Get all news articles
- **Endpoint:** `GET /api/admin/news_manage.php`
- **Response:** List of all news with redirect links
- **Used by:** App fetches news to display
- **Admin Panel:** `admin/news.php` manages this content

**Methods:**
- `GET` - List all news
- `POST` - Create news
- `PUT` - Update news
- `DELETE` - Delete news

---

### 2. Tasks Management
**File:** `api/admin/tasks_manage.php`

**GET** - Get tasks (daily and one-time)
- **Endpoint:** `GET /api/admin/tasks_manage.php?type=daily` or `?type=onetime` or `?type=all`
- **Response:** Tasks with redirect links, rewards, and reset time
- **Used by:** App fetches tasks to display
- **Admin Panel:** `admin/tasks.php` manages this content

**Methods:**
- `GET` - Get tasks (daily/onetime/all)
- `POST` - Create/Update daily tasks or create one-time task
- `PUT` - Update task
- `DELETE` - Delete one-time task

---

### 3. Shop Management
**File:** `api/admin/shop_manage.php`

**GET** - Get all shop items
- **Endpoint:** `GET /api/admin/shop_manage.php`
- **Response:** List of all shop items with redirect links
- **Used by:** App fetches shop items to display
- **Admin Panel:** `admin/shop.php` manages this content

**Methods:**
- `GET` - List all shop items
- `POST` - Create shop item
- `PUT` - Update shop item
- `DELETE` - Delete shop item

---

### 4. Giveaway Management
**File:** `api/admin/giveaway_manage.php`

**GET** - Get all giveaways
- **Endpoint:** `GET /api/admin/giveaway_manage.php`
- **Response:** List of all giveaways with redirect links
- **Used by:** App fetches giveaways to display
- **Admin Panel:** `admin/giveaway.php` manages this content

**Methods:**
- `GET` - List all giveaways
- `POST` - Create giveaway
- `PUT` - Update giveaway
- `DELETE` - Delete giveaway

---

### 5. Settings Management
**File:** `api/admin/settings_manage.php`

**GET** - Get all settings
- **Endpoint:** `GET /api/admin/settings_manage.php`
- **Response:** All system settings (mining speed, referral rewards, user count, mystery box, KYC, etc.)
- **Used by:** App fetches settings to configure behavior
- **Admin Panel:** Multiple pages manage these settings

**Methods:**
- `GET` - Get all settings
- `POST/PUT` - Update settings (mining, referral, user_count, mystery_box, kyc, ad_waterfall)

**Settings Included:**
- Mining speed settings
- Referral reward settings
- User count (current_users, goal_users)
- Mystery box settings (cooldown, ads, rewards)
- KYC settings
- Ad waterfall settings

---

### 6. Users Management
**File:** `api/admin/users_manage.php`

**GET** - Search users
- **Endpoint:** `GET /api/admin/users_manage.php?search=...&page=1&perPage=20`
- **Response:** List of users with balances
- **Used by:** Admin panel only (not app)

**POST** - Give coins or boosters
- **Endpoint:** `POST /api/admin/users_manage.php`
- **Used by:** Admin panel only (not app)

**Methods:**
- `GET` - Search users
- `POST` - Give coins or boosters to users

---

### 7. KYC Management
**File:** `api/admin/kyc_manage.php`

**GET** - Get KYC submissions
- **Endpoint:** `GET /api/admin/kyc_manage.php`
- **Response:** List of KYC submissions
- **Used by:** Admin panel only (not app)

**Methods:**
- `GET` - List KYC submissions
- `PUT` - Update KYC status

---

## App Usage Mapping

### Current App Endpoints vs Admin APIs

| App Uses | Admin API Available | Status |
|----------|-------------------|--------|
| `get_all_news.php` | `api/admin/news_manage.php` (GET) | ✅ Available |
| `social_list.php` | `api/admin/tasks_manage.php` (GET) | ✅ Available |
| `get_all_shops.php` | `api/admin/shop_manage.php` (GET) | ✅ Available |
| `get_giveaway.php` | `api/admin/giveaway_manage.php` (GET) | ✅ Available |
| `other_settings.php` | `api/admin/settings_manage.php` (GET) | ✅ Available |
| `getTotalUsers.php` | `api/admin/settings_manage.php` (GET) - includes user count | ✅ Available |

**All admin-managed content has corresponding GET endpoints in `api/admin/`**

---

## API Response Formats

### News API Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "News Title",
      "content": "News content",
      "redirect_link": "https://...",
      "image": "https://...",
      "status": "active",
      "created_at": "2024-01-01"
    }
  ]
}
```

### Tasks API Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Task Name",
      "reward": 2,
      "redirect_link": "https://...",
      "icon": "https://..."
    }
  ],
  "reset_time": "2024-01-15 10:00:00"
}
```

### Shop API Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "item_name": "Item Name",
      "redirect_link": "https://...",
      "item_image": "https://...",
      "status": "active"
    }
  ]
}
```

### Giveaway API Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Giveaway Title",
      "description": "Description",
      "redirect_link": "https://...",
      "icon": "https://..."
    }
  ]
}
```

### Settings API Response
```json
{
  "success": true,
  "data": {
    "mining_speed": 10,
    "base_mining_rate": 5,
    "max_mining_speed": 50,
    "referrer_reward": 50,
    "referee_reward": 25,
    "max_referrals": 100,
    "bonus_reward": 500,
    "current_users": 99000,
    "goal_users": 1000000,
    "common_box_cooldown": 0,
    "rare_box_cooldown": 5,
    "epic_box_cooldown": 10,
    "legendary_box_cooldown": 30,
    "common_box_ads": 1,
    "rare_box_ads": 3,
    "epic_box_ads": 6,
    "legendary_box_ads": 10,
    "common_box_min_coins": 1.00,
    "common_box_max_coins": 5.00,
    "rare_box_min_coins": 5.00,
    "rare_box_max_coins": 15.00,
    "epic_box_min_coins": 15.00,
    "epic_box_max_coins": 50.00,
    "legendary_box_min_coins": 50.00,
    "legendary_box_max_coins": 200.00,
    "kyc_mining_sessions": 14,
    "kyc_referrals_required": 10,
    "daily_tasks_reset_time": "2024-01-15 10:00:00"
  }
}
```

---

## Summary

✅ **All admin panel functionality has corresponding API endpoints in `api/admin/`**

- ✅ News - `api/admin/news_manage.php`
- ✅ Tasks - `api/admin/tasks_manage.php`
- ✅ Shop - `api/admin/shop_manage.php`
- ✅ Giveaway - `api/admin/giveaway_manage.php`
- ✅ Settings - `api/admin/settings_manage.php`
- ✅ Users - `api/admin/users_manage.php`
- ✅ KYC - `api/admin/kyc_manage.php`

**All endpoints support GET requests for app usage.**
**All endpoints support POST/PUT/DELETE for admin panel management.**

---

**Last Updated:** Current Date
**Status:** ✅ Complete

